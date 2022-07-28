<?php
namespace backend\controllers;

use backend\models\search\FinancialLoanListSearch;
use common\exceptions\UserExceptionExt;
use common\helpers\CommonHelper;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\pay\PayoutAccountInfo;
use common\models\user\LoanPerson;
use common\services\order\OrderService;
use yii\helpers\Url;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\web\Response;

class FinancialController extends BaseController {


    /**
     * @name 订单管理-打款管理-打款列表
     * @param string $view
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLoanList() {
        $query = FinancialLoanRecord::find()
            ->from(FinancialLoanRecord::tableName().' as l')
            ->where(['l.merchant_id' => $this->merchantIds])
            ->select([
                'l.*','l.id as rid','p.name','p.phone', 'u.order_time'
            ])
            ->leftJoin(LoanPerson::tableName().' as p','l.user_id=p.id')
            ->leftJoin(UserLoanOrder::tableName().' as u','l.business_id=u.id')
            ->orderBy(['l.id'=>SORT_DESC]);

        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $searchForm = new FinancialLoanListSearch();
            $searchArray = $searchForm->search($search);
            foreach ($searchArray as $item)
            {
                $query->andFilterWhere($item);
            }
        }
        $cloneQuery = clone $query;
        if(!empty(yii::$app->request->get('is_summary')) && yii::$app->request->get('is_summary') == 1){
            $count = $cloneQuery->cache(120)->count('l.id');
            $pages = new Pagination(['totalCount' => $count]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $pages->pageSize = \yii::$app->request->get('per-page', 50);
        $data = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        return $this->render('loan-list', [
            'withdraws' => $data,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }


    /**
     * @name 订单管理-打款管理-查看
     * @param integer $id
     * @return string
     * @name-cn 订单管理-打款管理-查看
     */
    public function actionView()
    {
        $id = CommonHelper::idDecryption(Yii::$app->request->get('id'));
        /** @var FinancialLoanRecord $loan */
        $loan = FinancialLoanRecord::find()->where(['id' =>intval($id) , 'merchant_id' => $this->merchantIds])->one();
        if(is_null($loan))
        {
            throw new UserExceptionExt("params error");
        }
        $loanPerson = LoanPerson::findOne($loan->user_id);
        $order = UserLoanOrder::findOne($loan->business_id);

        $data = [
            'name' => $loanPerson->name,
            'account' => $loan->account,
            'ifsc' => $loan->ifsc,
            'bank_name' => $loan->bank_name,
            'user_id' => CommonHelper::idEncryption($loan->user_id, 'user'),
            'order_id' => CommonHelper::idEncryption($loan->business_id, 'order'),
            'id' => CommonHelper::idEncryption($loan->id, 'financial'),
            'uuid' => $loan->order_id,
            'payout_account' => PayoutAccountInfo::getListMap()[$loan->payout_account_id] ?? '-',
            'phone' => $loanPerson->phone,
            'trade_no' => $loan->trade_no,
            'utr' => $loan->utr,
            'status' => FinancialLoanRecord::$ump_pay_status[$loan->status],
            'success_time' => $loan->success_time ? date('Y-m-d H:i:s', $loan->success_time) : '-',
            'order_time' => $order->order_time ? date('Y-m-d H:i:s', $order->order_time) : '-',
            'result' => $loan->result,
            'notify_result' => $loan->notify_result,
            'service_type' => FinancialLoanRecord::$service_type_map[$loan->service_type],
            'is_first' => UserLoanOrder::$first_loan_map[$order->is_first],
            'is_all_first' => UserLoanOrder::$first_loan_map[$order->is_all_first],
            'retry_num' => $loan->retry_num,
            'retry_time' => $loan->retry_time? date('Y-m-d H:i:s', $loan->retry_time) : '-',
            'apply_amount' => CommonHelper::CentsToUnit($order->amount) ,
            'loan_amount' =>  CommonHelper::CentsToUnit($loan->money),
            'created_at' => $loan->created_at ? date('Y-m-d H:i:s', $loan->created_at) : '-',
            'updated_at' => $loan->created_at ? date('Y-m-d H:i:s', $loan->created_at) : '-',


        ];
        return $this->render('view', [
            'data' => $data
        ]);
    }

    /**
     * @name string 放款驳回
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionLoanOrderReject()
    {
        $this->response->format = Response::FORMAT_JSON;
        $id = CommonHelper::idDecryption(Yii::$app->request->post('id'));
        /** @var FinancialLoanRecord $financialLoanOrder */
        $financialLoanOrder = FinancialLoanRecord::find()
            ->where([
                'id' => $id,
                'merchant_id' => $this->merchantIds,
                'status' => [FinancialLoanRecord::UMP_PAY_HANDLE_FAILED, FinancialLoanRecord::UMP_PAY_WAITING, FinancialLoanRecord::UMP_CMB_PAYING]
            ])->one();
        if(is_null($financialLoanOrder)){
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'Record does not exist or status is incorrect')
            ];
        }
        /** @var  UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $financialLoanOrder->business_id, 'status' => UserLoanOrder::STATUS_LOANING])->one();
        if(is_null($order))
        {
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'The loan order does not exist')
            ];
        }
        $financialLoanOrder->status = FinancialLoanRecord::UMP_PAY_FAILED;
        $service = new OrderService($order);
        if($service->orderLoanReject(0, Yii::T('common', 'Payment failed')) && $financialLoanOrder->save()){
            return [
                'code' => 0,
                'msg' => Yii::T('common', 'operation success')

            ];
        }else{
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'operation fail')
            ];
        }




    }

    /**
     * @name string 放款状态重置为申请中
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionLoanOrderReset()
    {
        $this->response->format = Response::FORMAT_JSON;
        $id = CommonHelper::idDecryption(Yii::$app->request->post('id'));
        /** @var FinancialLoanRecord $financialLoanOrder */
        $financialLoanOrder = FinancialLoanRecord::find()
            ->where([
                'id' => $id,
                'merchant_id' => $this->merchantIds,
                'status' => [FinancialLoanRecord::UMP_PAY_HANDLE_FAILED, FinancialLoanRecord::UMP_PAY_WAITING]
            ])->one();
        if(is_null($financialLoanOrder)){
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'Record does not exist or status is incorrect')
            ];
        }
        /** @var  UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $financialLoanOrder->business_id, 'status' => UserLoanOrder::STATUS_LOANING])->one();
        if(is_null($order))
        {
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'The loan order does not exist or is in the wrong state')

            ];
        }
        $financialLoanOrder->status = FinancialLoanRecord::UMP_PAYING;
        if( $financialLoanOrder->save()){
            return [
                'code' => 0,
                'msg' => Yii::T('common', 'operation success')

            ];
        }else{
            return [
                'code' => -1,
                'msg' => Yii::T('common', 'operation fail')
            ];
        }




    }

}
