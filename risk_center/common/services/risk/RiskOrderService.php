<?php
namespace common\services\risk;

use Carbon\Carbon;
use common\exceptions\UserExceptionExt;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\InfoCollectionSuggestion;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoPictureMetadata;
use common\models\InfoRepayment;
use common\models\InfoUser;
use common\models\LoanCollectionRecord;
use common\models\LoginLog;
use common\models\ModelScore;
use common\models\order\EsUserLoanOrder;
use common\models\RemindLog;
use common\models\RemindOrder;
use common\models\risk\RiskBlackListAadhaar;
use common\models\risk\RiskBlackListDeviceid;
use common\models\risk\RiskBlackListPan;
use common\models\risk\RiskBlackListPhone;
use common\models\risk\RiskBlackListSzlm;
use common\models\RiskOrder;
use common\services\BaseService;
use frontend\models\risk\ApplyForm;
use frontend\models\risk\CollectionSuggestionForm;
use frontend\models\risk\LoanCollectionRecordForm;
use frontend\models\risk\LoginLogForm;
use frontend\models\risk\ModelScoreForm;
use frontend\models\risk\OrderLoanSuccessForm;
use frontend\models\risk\OrderOverdueForm;
use frontend\models\risk\OrderRejectForm;
use frontend\models\risk\OrderRepaymentSuccessForm;
use frontend\models\risk\RemindLogForm;
use frontend\models\risk\RemindOrderForm;
use frontend\models\risk\RiskBlackForm;
use yii\db\Exception;
use yii;

class RiskOrderService extends BaseService {

    /**
     * @param ApplyForm $applyForm
     * @return bool
     * @throws yii\base\InvalidConfigException
     */
    public function apply(ApplyForm $applyForm)
    {

        $check = RiskOrder::find()->where([
            'app_name' => $applyForm->app_name,
            'order_id' => $applyForm->order_id,
            'user_id' => $applyForm->user_id,
            'type' => RiskOrder::TYPE_AUTO_CHECK
            ])->exists();
        if($check)
        {
//            $this->setError("app_name:{$applyForm->app_name}, order_id:{$applyForm->order_id},已存在，请勿重复推单");
            $this->setResult([]);
            return true;
        }

        $transaction = RiskOrder::getDb()->beginTransaction();
        try{

            $riskOrder = new RiskOrder();
            $riskOrder->app_name = $applyForm->app_name;
            $riskOrder->order_id = $applyForm->order_id;
            $riskOrder->user_id = $applyForm->user_id;
            $riskOrder->status = RiskOrder::STATUS_WAIT_CHECK;
            $riskOrder->type = RiskOrder::TYPE_AUTO_CHECK;
            if(!$riskOrder->save())
            {
                throw new Exception("risk order save failed");
            }

            $infoOrder = new InfoOrder();
            $infoOrder->load($applyForm->order_info_model->toArray(), '');
            $infoOrder->app_name = $applyForm->app_name;
            $infoOrder->order_id = $applyForm->order_id;
            $infoOrder->user_id = $applyForm->user_id;
            $infoOrder->status = InfoOrder::STATUS_DEFAULT;
            $infoOrder->loan_time = 0;
//            $infoOrder->principal = 0;
//            $infoOrder->loan_amount = 0;
            if(!$infoOrder->save()){
                throw new Exception("info order save failed");
            }

            $infoUser = new InfoUser();
            $infoUser->load($applyForm->user_basic_info_model->toArray(), '');
            $infoUser->app_name = $applyForm->app_name;
            $infoUser->order_id = $applyForm->order_id;
            $infoUser->user_id = $applyForm->user_id;
            if(!$infoUser->save())
            {
                throw new Exception("info order save failed");
            }

            $infoDevice = new InfoDevice();
            $infoDevice->load($applyForm->client_info_model->toArray(),'');
            $infoDevice->app_name = $applyForm->app_name;
            $infoDevice->phone = $applyForm->user_basic_info_model->phone;
            $infoDevice->pan_code = $applyForm->user_basic_info_model->pan_code;
            $infoDevice->user_id = $applyForm->user_id;
            $infoDevice->order_id = $applyForm->order_id;
            $infoDevice->event_time = $applyForm->order_info_model->order_time;
            if(!$infoDevice->save())
            {
                var_dump($infoDevice->getErrorSummary(true));
                throw new Exception("info device save failed");
            }

            $infoPictureMetadata = new InfoPictureMetadata();
            $infoPictureMetadata->load($applyForm->picture_metadata_model->toArray(), '');
            $infoPictureMetadata->app_name = $applyForm->app_name;
            $infoPictureMetadata->user_id = $applyForm->user_id;
            $infoPictureMetadata->order_id = $applyForm->order_id;
            if(!$infoPictureMetadata->save()){
                throw new Exception("info picture metadata save failed");
            }

            if(YII_ENV_PROD){
                $this->saveOrderGps($applyForm);
            }

            $transaction->commit();

            if($infoOrder->is_first == InfoOrder::ENUM_IS_FIRST_Y){
                RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK, $riskOrder->id]);
            }else{
                RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK_OLD, $riskOrder->id]);
            }

        }catch (\Exception $exception)
        {
            $transaction->rollBack();
            $this->setError($exception->getMessage());
            return false;
        }

        $this->setResult([]);
        return true;

    }

    public function saveOrderGps(ApplyForm $applyForm){
        $clientInfo = $applyForm->client_info_model->toArray();

        if (empty($clientInfo['latitude']) || empty($clientInfo['longitude'])) {
            return false;
        }

        $orderInfo = $applyForm->order_info_model->toArray();

        $esOrder = new EsUserLoanOrder();
        $esOrder->app_name = $applyForm->app_name;
        $esOrder->user_id = $applyForm->user_id;
        $esOrder->order_id = $applyForm->order_id;
        $esOrder->order_time = Carbon::createFromTimestamp($orderInfo['order_time'])->toIso8601ZuluString();
        $esOrder->location = [
            'lat' => $clientInfo['latitude'],    //纬度
            'lon' => $clientInfo['longitude'],    //经度
        ];
        $primaryKey = $esOrder->app_name . '_' . $esOrder->user_id . '_' . $esOrder->order_id;
        $esOrder->setPrimaryKey($primaryKey);
        return $esOrder->save();
    }



    public function loginEventUpload(LoginLogForm $loginEventForm)
    {
        $infoDevice = new LoginLog();
        $infoDevice->load($loginEventForm->toArray(), '');
        if($infoDevice->save()){
            $this->setResult(['data' => 'success']);
            return true;
        }else{
            return false;
        }
    }


    public function orderReject(OrderRejectForm $orderRejectForm)
    {
        /** @var InfoOrder $infoOrder */
        $infoOrder = InfoOrder::find()->where([
            'app_name' => $orderRejectForm->app_name,
            'user_id'  => $orderRejectForm->user_id,
            'order_id' => $orderRejectForm->order_id
        ])->one();
        if(is_null($infoOrder))
        {
            $this->setError("数据不存在");
            return false;
        }

        if($orderRejectForm->data_version <= $infoOrder->data_version)
        {
            $this->setError("数据版本号低于当前记录:");
            return false;
        }

        if(!in_array($orderRejectForm->status, InfoOrder::$statusRejectSet)){
            $this->setError("status错误");
            return false;
        }

        $infoOrder->status = $orderRejectForm->status;
        $infoOrder->data_version = $orderRejectForm->data_version;
        $infoOrder->reject_reason = $orderRejectForm->reject_reason;
        if($infoOrder->save())
        {
            $this->setResult([]);
            return true;
        }else{
            return false;
        }

    }


    public function orderLoanSuccess(OrderLoanSuccessForm $orderLoanSuccessForm)
    {
        /** @var InfoOrder $infoOrder */
        $infoOrder = InfoOrder::find()->where([
            'app_name' => $orderLoanSuccessForm->app_name,
            'user_id'  => $orderLoanSuccessForm->user_id,
            'order_id' => $orderLoanSuccessForm->order_id
        ])->one();
        if(is_null($infoOrder))
        {
            $this->setError("数据不存在");
            return false;
        }

        if($orderLoanSuccessForm->data_version <= $infoOrder->data_version)
        {
            $this->setError("数据版本号低于当前记录:");
            return false;
        }

        $transaction = RiskOrder::getDb()->beginTransaction();

        try{
            $infoOrder->principal = $orderLoanSuccessForm->principal;
            $infoOrder->loan_time = $orderLoanSuccessForm->loan_time;
            $infoOrder->data_version = $orderLoanSuccessForm->data_version;
            if(!in_array($infoOrder->status, [InfoOrder::STATUS_CLOSED_REPAYMENT]))
            {
                $infoOrder->status = InfoOrder::STATUS_PENDING_REPAYMENT;
            }
            if(!$infoOrder->save()){
                throw new UserExceptionExt('infoOrder保存失败');
            }

            $infoRepayment = InfoRepayment::findOne([
                'app_name' => $orderLoanSuccessForm->app_name,
                'user_id'  => $orderLoanSuccessForm->user_id,
                'order_id' => $orderLoanSuccessForm->order_id
            ]);
            if(is_null($infoRepayment))
            {
                $infoRepayment = new InfoRepayment();
                $infoRepayment->load($orderLoanSuccessForm->toArray(), '');
                $infoRepayment->true_total_money = 0;
                $infoRepayment->is_overdue = InfoRepayment::OVERDUE_NO;
                $infoRepayment->overdue_day = 0;
                $infoRepayment->overdue_fee = 0;
                $infoRepayment->status = InfoRepayment::STATUS_PENDING;
                $infoRepayment->closing_time = 0;
                if(!$infoRepayment->save())
                {
                    throw new UserExceptionExt('infoRepayment保存失败');
                }
            }

            $transaction->commit();
        }catch (UserExceptionExt $exception)
        {
            $transaction->rollBack();
            $this->setError($exception->getMessage());
            return false;
        }catch (\Exception $exception)
        {
            $transaction->rollBack();
            return false;
        }

        $this->setResult([]);
        return true;
    }

    public function orderRepaymentSuccess(OrderRepaymentSuccessForm $orderRepaymentSuccessForm)
    {
        /** @var InfoRepayment $infoRepayment */
        $infoRepayment = InfoRepayment::findOne([
            'app_name' => $orderRepaymentSuccessForm->app_name,
            'user_id'  => $orderRepaymentSuccessForm->user_id,
            'order_id' => $orderRepaymentSuccessForm->order_id
        ]);
        if(is_null($infoRepayment))
        {
            $this->setError("数据不存在");
            return false;
        }

        /** @var InfoOrder $infoOrder */
        $infoOrder = InfoOrder::find()->where([
            'app_name' => $orderRepaymentSuccessForm->app_name,
            'user_id'  => $orderRepaymentSuccessForm->user_id,
            'order_id' => $orderRepaymentSuccessForm->order_id
        ])->one();
        if(is_null($infoOrder))
        {
            $this->setError("数据不存在");
            return false;
        }

        if($orderRepaymentSuccessForm->data_version <= $infoOrder->data_version)
        {
            $this->setError("数据版本号低于当前记录:");
            return false;
        }

        $transaction = InfoRepayment::getDb()->beginTransaction();

        try{
            $infoRepayment->total_money = $orderRepaymentSuccessForm->total_money;
            $infoRepayment->true_total_money = $orderRepaymentSuccessForm->true_total_money;
            $infoRepayment->is_overdue = $orderRepaymentSuccessForm->is_overdue;
            $infoRepayment->overdue_day = $orderRepaymentSuccessForm->overdue_day;
            $infoRepayment->overdue_fee = $orderRepaymentSuccessForm->overdue_fee;
            $infoRepayment->status = InfoRepayment::STATUS_CLOSED;
            $infoRepayment->closing_time = $orderRepaymentSuccessForm->closing_time;

            if(!$infoRepayment->save()){
                throw new UserExceptionExt('infoRepayment保存失败');
            }

            $infoOrder->status = InfoOrder::STATUS_CLOSED_REPAYMENT;
            $infoOrder->data_version = $orderRepaymentSuccessForm->data_version;
            if(!$infoOrder->save()){
                throw new UserExceptionExt('infoOrder保存失败');
            }

            $model = new RiskOrder();
            $model->app_name = $orderRepaymentSuccessForm->app_name;
            $model->user_id = $orderRepaymentSuccessForm->user_id;
            $model->order_id = $orderRepaymentSuccessForm->order_id;
            $model->status = RiskOrder::STATUS_WAIT_CHECK;
            $model->type = RiskOrder::TYPE_USER_CREDIT;
            if(!$model->save()){
                throw new UserExceptionExt('riskOrder保存失败');
            }
            $transaction->commit();

            //推送额度计算队列
            RedisQueue::push([RedisQueue::CREDIT_USER_CREDIT_CALC, $model->id]);
        }catch (UserExceptionExt $exception)
        {
            $transaction->rollBack();
            $this->setError($exception->getMessage());
            return false;
        }catch (\Exception $exception)
        {
            $transaction->rollBack();
            return false;
        }

        $this->setResult([]);
        return true;
    }

    public function orderOverdue(OrderOverdueForm $orderOverdueForm)
    {
        /** @var InfoRepayment $infoRepayment */
        $infoRepayment = InfoRepayment::findOne([
            'app_name' => $orderOverdueForm->app_name,
            'user_id'  => $orderOverdueForm->user_id,
            'order_id' => $orderOverdueForm->order_id
        ]);
        if(is_null($infoRepayment))
        {
            $this->setError("数据不存在");
            return false;
        }

        /** @var InfoOrder $infoOrder */
        $infoOrder = InfoOrder::find()->where([
            'app_name' => $orderOverdueForm->app_name,
            'user_id'  => $orderOverdueForm->user_id,
            'order_id' => $orderOverdueForm->order_id
        ])->one();
        if(is_null($infoOrder))
        {
            $this->setError("数据不存在");
            return false;
        }

        if($orderOverdueForm->data_version <= $infoOrder->data_version)
        {
            $this->setError("数据版本号低于当前记录:");
            return false;
        }

        $transaction = InfoOrder::getDb()->beginTransaction();

        try{

            $infoOrder->data_version = $orderOverdueForm->data_version;
            if(!$infoOrder->save())
            {
                throw new UserExceptionExt('infoOrder保存失败');
            }


            $infoRepayment->is_overdue = InfoRepayment::OVERDUE_YES;
            $infoRepayment->total_money = $orderOverdueForm->total_money;
            $infoRepayment->overdue_day = $orderOverdueForm->overdue_day;
            $infoRepayment->overdue_fee = $orderOverdueForm->overdue_fee;

            if(!$infoRepayment->save()){
                throw new UserExceptionExt('infoRepayment保存失败');
            }

            $transaction->commit();
        }catch (UserExceptionExt $exception)
        {
            $transaction->rollBack();
            $this->setError($exception->getMessage());
            return false;
        }catch (\Exception $exception)
        {
            $transaction->rollBack();
            return false;
        }

        $this->setResult([]);
        return true;
    }

    /**
     * @param RiskBlackForm $riskBlackForm
     * @return bool
     */
    public function addRiskBlack(RiskBlackForm $riskBlackForm)
    {

        try{
            if(!empty($riskBlackForm->device_ids)){
                foreach ($riskBlackForm->device_ids as $value){
                    if(empty($value)){
                        continue;
                    }
                    $check = RiskBlackListDeviceid::findOne(['value' => $value]);
                    if(is_null($check)){
                        $list = new RiskBlackListDeviceid();
                        $list->value = $value;
                        if(!$list->save()){
                            throw new UserExceptionExt('保存黑名单失败');
                        }
                    }
                }
            }

            if(!empty($riskBlackForm->szlm_ids)){
                foreach ($riskBlackForm->szlm_ids as $value){
                    if(empty($value)){
                        continue;
                    }
                    $check = RiskBlackListSzlm::findOne(['value' => $value]);
                    if(is_null($check)){
                        $list = new RiskBlackListSzlm();
                        $list->value = $value;
                        if(!$list->save()){
                            throw new UserExceptionExt('保存黑名单失败');
                        }
                    }
                }
            }

            $check = RiskBlackListPhone::findOne(['value' => $riskBlackForm->phone]);
            if(is_null($check)){
                $list = new RiskBlackListPhone();
                $list->value = $riskBlackForm->phone;
                if(!$list->save()){
                    throw new UserExceptionExt('保存黑名单失败');
                }
            }

            if(!empty($riskBlackForm->aadhaar_md5)){
                $check = RiskBlackListAadhaar::findOne(['value' => $riskBlackForm->aadhaar_md5]);
                if(is_null($check)){
                    $list = new RiskBlackListAadhaar();
                    $list->value = $riskBlackForm->aadhaar_md5;
                    if(!$list->save()){
                        throw new UserExceptionExt('保存黑名单失败');
                    }
                }
            }

            if(!empty($riskBlackForm->pan_code)){
                $check = RiskBlackListPan::findOne(['value' => $riskBlackForm->pan_code]);
                if(is_null($check)){
                    $list = new RiskBlackListPan();
                    $list->value = $riskBlackForm->pan_code;
                    if(!$list->save()){
                        throw new UserExceptionExt('保存黑名单失败');
                    }
                }
            }
        }catch (UserExceptionExt $exception)
        {
            $this->setError($exception->getMessage());
            return false;
        }catch (\Exception $exception)
        {
            return false;
        }

        $this->setResult([]);
        return true;
    }

    public function collectionSuggestion(CollectionSuggestionForm $form)
    {
        $infoCollection = InfoCollectionSuggestion::findOne([
            'app_name' => $form->app_name,
            'user_id'  => $form->user_id,
            'order_id' => $form->order_id
        ]);
        if(!empty($infoCollection))
        {
            $this->setResult([]);
            return true;
        }

        try{
            $model = new InfoCollectionSuggestion();
            $model->load($form->toArray(), '');

            if(!$model->save()){
                throw new UserExceptionExt('infoCollectionSuggestion保存失败');
            }

        }catch (UserExceptionExt $exception)
        {
            $this->setError($exception->getMessage());
            return false;
        }catch (\Exception $exception)
        {
            return false;
        }

        $this->setResult([]);
        return true;
    }

    public function loanCollectionRecord(LoanCollectionRecordForm $form)
    {
        $model = new LoanCollectionRecord();
        $model->load($form->toArray(), '');
        if($model->save()){
            $this->setResult(['data' => 'success']);
            return true;
        }else{
            $this->setError($model->getErrorSummary(false)[0] ?? '');
            return false;
        }
    }

    public function remindOrder(RemindOrderForm $form)
    {
        $model = RemindOrder::find()->where(['app_name' => $form->app_name, 'request_id' => $form->request_id])->one();
        if(empty($model)){
            $model             = new RemindOrder();
            $model->request_id = $form->request_id;
            $model->app_name   = $form->app_name;
            $model->order_id   = $form->order_id;
            $model->user_id    = $form->user_id;
        }
        $model->status             = $form->status;
        $model->remind_return      = $form->remind_return;
        $model->payment_after_days = $form->payment_after_days;
        $model->remind_count       = $form->remind_count;
        $model->created_at         = $form->created_at;
        $model->updated_at         = $form->updated_at;
        if($model->save()){
            $this->setResult(['data' => 'success']);
            return true;
        }else{
            $this->setError($model->getErrorSummary(false)[0] ?? '');
            return false;
        }
    }

    public function remindLog(RemindLogForm $form)
    {
        $model = RemindLog::find()->where(['app_name' => $form->app_name, 'request_id' => $form->request_id])->one();
        if(!empty($model)){
            $this->setResult(['data' => 'success']);
            return true;
        }
        $model             = new RemindLog();
        $model->load($form->toArray(), '');
        if($model->save()){
            $this->setResult(['data' => 'success']);
            return true;
        }else{
            $this->setError($model->getErrorSummary(false)[0] ?? '');
            return false;
        }
    }

    public function getModelScore(ModelScoreForm $form)
    {
        /** @var ModelScore $model */
        $model = ModelScore::find()->where(['pan_code' => $form->pan_code])->one();
        $data = [
            'score' => $model->score ?? 0
        ];
        $this->setResult($data);
        return true;
    }

}