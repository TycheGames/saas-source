<?php
namespace common\services;


use common\helpers\CommonHelper;
use common\models\enum\PackageName;
use common\models\order\UserLoanOrder;
use common\models\user\LoanPerson;
use common\services\order\OrderService;
use common\services\repayment\RepaymentService;
use frontend\models\guest\ApplyForm;
use frontend\models\guest\OrderDetailForm;
use light\hashids\Hashids;
use yii;

class GuestService extends BaseService
{


    /**
     * 解密订单号
     * @param $key
     * @return array
     */
    public function decryptionKey($key)
    {
        $system = substr($key, 0, 1);
        
        $orderID = substr($key, 1);
        $content = (new Hashids(['salt'=> yii::$app->params['guestPayment']['salt'], 'minHashLength'=> yii::$app->params['guestPayment']['minHashLength']]))->decode($orderID);
        $orderID = $content[0] ?? null;
        if(is_null($orderID))
        {
            return [];
        }

        return [
            'system' => $system,
            'orderID' => $orderID
        ];
    }


    /**
     * 加密订单号
     * @param int $orderID
     * @param string $system i是icredit大盘、r是rupeeplus大盘 I是icredit导流、R是rupeeplus导流
     * @return string
     */
    public function encryptionKey(int $orderID, string  $system)
    {
        $content = (new Hashids(['salt'=> yii::$app->params['guestPayment']['salt'], 'minHashLength'=> yii::$app->params['guestPayment']['minHashLength']]))->encode($orderID);
        return $system . $content;
    }

    /**
     * 还款请求
     * @param ApplyForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function payment(ApplyForm $form)
    {
        $info = $this->decryptionKey($form->key);
        if(empty($info))
        {
            $this->setError('key invalid 1');
            return false;
        }
        $orderID = $info['orderID'];

        $amount = CommonHelper::UnitToCents($form->amount);

        $repaymentService = new RepaymentService();

        //首字母是i、r则是大盘订单 首字母是I、R则是导流订单
        if($repaymentService->guestPayment($orderID, $amount))
        {
            $this->setResult($repaymentService->getResult());
            return true;
        }else{
            $this->setError($repaymentService->getError());
            return false;
        }
    }


    public function orderDetail(OrderDetailForm $form)
    {


        $info = $this->decryptionKey($form->key);
        if(empty($info))
        {
            $this->setError('key invalid 1');
            return false;
        }
        $system = $info['system'];
        $orderID = $info['orderID'];


        if(preg_match('/^[A-Z]$/', $system))
        {
            if(isset(yii::$app->params['link'][strtolower($system)]))
            {
                $packageName = yii::$app->params['link'][strtolower($system)];
            }else{
                $packageName = '';
            }

            /** @var UserLoanOrder|null $order */
            $order = UserLoanOrder::find()->where(['id' => $orderID, 'status' => UserLoanOrder::STATUS_LOAN_COMPLETE])->one();
            if(is_null($order))
            {
                $this->setResult([
                    'packageName' => $packageName,
                    'download' => true
                ]);
                return true;
            }

            $orderService = new OrderService($order);
            $loanPerson = LoanPerson::findOne($order->user_id);

            $phone = CommonHelper::strMask(strval($loanPerson->phone), 0, 5);
            $orderUUID = $order->order_uuid;
            $remainingPaymentAmount = CommonHelper::CentsToUnit($orderService->remainingPaymentAmount());

            $this->setResult([
                'phone' => $phone,
                'orderUUID' => $orderUUID,
                'remainingPaymentAmount' => $remainingPaymentAmount,
                'packageName' => $packageName,
                'overdueFeeAmount' => CommonHelper::CentsToUnit($orderService->loanAmount() * $order->overdue_rate / 100)
            ]);
            return true;
        }else{
            $this->setError('key invalid 3');
            return false;
        }


    }


    /**
     * 生成还款链接
     * @param UserLoanOrder $order
     * @return string
     */
    public function generatePaymentLink(UserLoanOrder $order)
    {
        if(UserLoanOrder::IS_EXPORT_NO == $order->is_export)
        {
            return '-';
        }
        $packageName = $order->clientInfoLog->app_market;

        $config = yii::$app->params['link'];

        $system = '';
        foreach ($config as $sys => $package)
        {
            if(0 === strpos($packageName, 'external_' . $package))
            {
                $system = strtoupper($sys);
                break;
            }
        }

        if(empty($system))
        {
            return '-';
        }


        if(YII_ENV_PROD)
        {
            $url = 'https://l.top1.link/';
        }elseif(YII_ENV_TEST)
        {
            $url = 'http://test-link.i-credit.in:8081/';
        }else{
            $url = 'http://api.mercy.local/';
        }
        return $url.$this->encryptionKey($order->id, $system);


    }

}