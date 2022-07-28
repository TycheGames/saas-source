<?php


namespace console\controllers;


use backend\models\remind\RemindOrder;
use callcenter\models\loan_collection\LoanCollectionOrder;
use Carbon\Carbon;
use common\helpers\EncryptData;
use common\helpers\RedisQueue;
use common\models\enum\mg_user_content\UserContentType;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\MgUserMobilePhotos;
use common\models\user\UserActiveTime;
use common\models\user\UserBankAccountLog;
use common\models\user\UserCreditReportOcrAad;
use common\services\FileStorageService;
use common\services\message\WeWorkService;
use common\services\order\PushOrderRiskService;
use common\services\user\MgUserContentService;
use common\services\user\UserBankInfoService;
use frontend\models\user\UserBankAccountForm;
use yii\console\Controller;
use Yii;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class UserController extends BaseController
{
    /**
     * 同步用户通讯录
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionUserContentMobile()
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $service = new MgUserContentService();
        $pushService = new PushOrderRiskService();

        $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD]);

        while (!empty($contentsStr)) {
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            foreach ($contents['data'] as $datum) {
                $datum['user_id'] = $contents['user_id'];
                $datum['merchant_id'] = $contents['merchant_id'];
                $phoneNumbers = isset($datum['mobile']) ? explode(':', $datum['mobile']) : [];
                $item = $datum;
                foreach ($phoneNumbers as $phoneNumber) {
                    $item['mobile'] = $phoneNumber;
                    $service->saveMgUserContentByFormToM(UserContentType::CONTACT(), $item);
                }
            }

            try{
                $result = $pushService->uploadContentsNew($contents['user_id'], UserContentType::CONTACT());
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception){
                $workService = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $contents['user_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message .= $exception->getTraceAsString();
                $workService->send($message);
            }

            if(time() - $now > 180){
                $this->printMessage('运行满3分钟，关闭当前脚本');
                exit;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD]);
        }
    }

    /**
     * 同步用户APP安装列表
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionUserContentApp()
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $service = new MgUserContentService();
        $pushService = new PushOrderRiskService();

        $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD]);

        while (!empty($contentsStr)) {
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            $item = array_merge($contents['data'], $contents['params']);
            $item['user_id'] = $contents['user_id'];
            $item['merchant_id'] = $contents['merchant_id'];
            $service->saveMgUserContentByFormToM(UserContentType::APP_LIST(), $item);

            try{
                $result = $pushService->uploadContentsNew($contents['user_id'], UserContentType::APP_LIST());
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception){
                $workService = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $contents['user_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message .= $exception->getTraceAsString();
                $workService->send($message);
            }

            if(time() - $now > 180){
                $this->printMessage('运行满3分钟，关闭当前脚本');
                exit;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD]);
        }
    }

    /**
     * 同步用户短信记录
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionUserContentSms()
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $service = new MgUserContentService();
        $pushService = new PushOrderRiskService();

        $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD]);

        while (!empty($contentsStr)) {
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            foreach ($contents['data'] as $datum) {
                $item = $datum;
                $item['user_id'] = $contents['user_id'];
                $item['merchant_id'] = $contents['merchant_id'];
                $service->saveMgUserContentByFormToM(UserContentType::SMS(), $item);
            }

            try{
                $result = $pushService->uploadContentsNew($contents['user_id'], UserContentType::SMS());
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception){
                $workService = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $contents['user_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message .= $exception->getTraceAsString();
                $workService->send($message);
            }

            if(time() - $now > 180){
                $this->printMessage('运行满3分钟，关闭当前脚本');
                exit;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD]);
        }
    }

    /**
     * 同步用户通话记录
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionUserContentCallRecords()
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $service = new MgUserContentService();
        $pushService = new PushOrderRiskService();

        $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD]);

        while (!empty($contentsStr)) {
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            foreach ($contents['data'] as $datum) {
                $item = $datum;
                $item['user_id'] = $contents['user_id'];
                $item['merchant_id'] = $contents['merchant_id'];
                $service->saveMgUserContentByFormToM(UserContentType::CALL_RECORDS(), $item);
            }

            try{
                $result = $pushService->uploadContentsNew($contents['user_id'], UserContentType::CALL_RECORDS());
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception){
                $workService = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $contents['user_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message .= $exception->getTraceAsString();
                $workService->send($message);
            }

            if(time() - $now > 180){
                $this->printMessage('运行满3分钟，关闭当前脚本');
                exit;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD]);
        }
    }

    /**
     * 同步用户相册记录
     */
    public function actionUserPhotoRecords()
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $service = new MgUserContentService();

        while (true) {

            if(time() - $now > 300){
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_PHOTO_UPLOAD]);
            if(empty($contentsStr)){
                $this->printMessage('无记录，跳出循环');
                exit;
            }

            $contents = json_decode($contentsStr, true);
            $userPhoto = MgUserMobilePhotos::findOne(['user_id' => $contents['user_id'], 'date' => $contents['date']]);
            if(!empty($userPhoto)){
                $this->printMessage('当天已上传，跳过');
                continue;
            }
            foreach ($contents['content'] as $datum) {
                $item = $datum;
                $item['user_id'] = $contents['user_id'];
                $item['merchant_id'] = $contents['merchant_id'];
                $item['date'] = $contents['date'];
                $service->saveMgUserPhoto($item);
            }
        }
    }

    public function actionUserAadhaarEncrypt()
    {
        //表结构变动记录
        if(!$this->lock()){
            return;
        }

        $now = time();
        $tmpDir = '/tmp/';
        $fileSuffix = '.data';
        $fileService = new FileStorageService();

        $query = UserCreditReportOcrAad::find()
            ->where(['is_encode' => 0])
            ->orderBy('id ASC')
            ->limit(1000);

        $maxId = 0;
        $cloneQuery = clone $query;
        $records = $cloneQuery
            ->andWhere(['>', 'id', $maxId])
            ->andWhere(['<', 'created_at', time() - 600])
            ->all();

        while($records) {
            foreach ($records as $record) {
                /**
                 * @var UserCreditReportOcrAad $record
                 */
                if (!empty($record->img_front_path)) {
                    $tmpFrontPath = $fileService->downloadFile($record->img_front_path);
                    $tmpFrontSuffix = '.' . pathinfo($tmpFrontPath, PATHINFO_EXTENSION);
                    $encryptedFrontPath = $tmpDir . pathinfo($tmpFrontPath, PATHINFO_FILENAME) . $fileSuffix;
                    EncryptData::encryptFile($tmpFrontPath, $encryptedFrontPath, EncryptData::PUBLIC_KEY, true);
                    $encryptedFrontRemotePath = str_replace(['aadhaar', $tmpFrontSuffix], ['check_code', $fileSuffix], $record->img_front_path);
                    $record->check_data_z_path = $fileService->uploadFileByPath($encryptedFrontRemotePath, $encryptedFrontPath, true);
                }
                if (!empty($record->img_back_path)) {
                    $tmpBackPath = $fileService->downloadFile($record->img_back_path);
                    $tmpBackSuffix = '.' . pathinfo($tmpBackPath, PATHINFO_EXTENSION);
                    $encryptedBackPath = $tmpDir . pathinfo($tmpBackPath, PATHINFO_FILENAME) . $fileSuffix;
                    //加密后自动删除源文件
                    EncryptData::encryptFile($tmpBackPath, $encryptedBackPath, EncryptData::PUBLIC_KEY, true);
                    $encryptedBackRemotePath = str_replace(['aadhaar', $tmpBackSuffix], ['check_code', $fileSuffix], $record->img_back_path);
                    //上传后自动删除临时文件
                    $record->check_data_f_path = $fileService->uploadFileByPath($encryptedBackRemotePath, $encryptedBackPath, true);
                }
                $record->is_encode = UserCreditReportOcrAad::STATUS_ENCODE;

                $record->save();
            }

            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $maxId = $record->id;
            $cloneQuery = clone $query;
            $records = $cloneQuery
                ->andWhere(['>', 'id', $maxId])
                ->andWhere(['<', 'created_at', time() - 600])
                ->all();
        }
    }

    /**
     * 删除ocr-aad表中的的数据,正面照
     */
    public function actionUserAadhaarFrontDelete()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();

        $query = UserCreditReportOcrAad::find()
            ->where(['is_encode' => UserCreditReportOcrAad::STATUS_ENCODE])
            ->limit(1000);

        $fileService = new FileStorageService();

        $maxId = 0;
        $cloneQuery = clone $query;
        $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($records) {
            foreach ($records as $record) {
                /**
                 * @var UserCreditReportOcrAad $record
                 */
                if (!empty($record->img_front_path)) {
                    if ($fileService->deleteFile($record->img_front_path)) {
                        $record->img_front_path = null;
                        $record->is_encode = UserCreditReportOcrAad::STATUS_DELETE_FRONT;
                    }
                } else {
                    $record->is_encode = UserCreditReportOcrAad::STATUS_DELETE_FRONT;
                }

                if(!$record->save()) {
                    $this->printMessage('error record id:' . $record->id);
                }

                $maxId = $record->id;
            }

            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $cloneQuery = clone $query;
            $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }
    }

    /**
     * 删除ocr-aad表中的的数据,背面照
     */
    public function actionUserAadhaarBackDelete()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();

        $query = UserCreditReportOcrAad::find()
            ->where(['is_encode' => UserCreditReportOcrAad::STATUS_DELETE_FRONT])
            ->limit(1000);

        $fileService = new FileStorageService();

        $maxId = 0;
        $cloneQuery = clone $query;
        $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($records) {
            foreach ($records as $record) {
                /**
                 * @var UserCreditReportOcrAad $record
                 */
                if (!empty($record->img_back_path)) {
                    if ($fileService->deleteFile($record->img_back_path)) {
                        $record->img_back_path = null;
                        $record->is_encode = UserCreditReportOcrAad::STATUS_DELETE_BACK;
                    }
                } else {
                    $record->is_encode = UserCreditReportOcrAad::STATUS_DELETE_BACK;
                }

                if(!$record->save()) {
                    $this->printMessage('error record id:' . $record->id);
                }

                $maxId = $record->id;
            }

            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $cloneQuery = clone $query;
            $records = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }
    }


    public function actionFixUserActivePay()
    {
        $maxID = 9999999;
        $query = FinancialPaymentOrder::find()->select(['id','user_id', 'created_at'])
            ->where(['>=' ,'created_at',  strtotime('2020-05-26')])->orderBy(['id' => SORT_DESC]);

        $cloneQuery = clone $query;
        $lists = $cloneQuery->andWhere(['<', 'id', $maxID])->limit(2000)->asArray()->all();

        while ($lists)
        {
            foreach ($lists as $data)
            {
                $maxID = $data['id'];
                $this->printMessage("id:{$maxID} 开始执行");
                if($data['created_at'] <= strtotime('2020-05-26'))
                {
                    $this->printMessage("脚本结束");
                    exit;
                }
                $model = UserActiveTime::find()->where(['user_id' => $data['user_id']])->one();
                if(is_null($model))
                {
                    $model = new UserActiveTime();
                }
                if($model->last_pay_time >= $data['created_at'])
                {
                    $this->printMessage("id:{$maxID} last_pay_time > created_at");
                    continue;
                }
                $model->user_id = $data['user_id'];
                $model->last_pay_time = $data['created_at'];
                $model->save();
            }

            $cloneQuery = clone $query;
            $lists = $cloneQuery->andWhere(['<', 'id', $maxID])->limit(2000)->asArray()->all();
        }

    }


    public function actionFixUserActiveAccess()
    {
        $collectionLists = LoanCollectionOrder::find()->select(['user_id', 'user_last_access_time'])
            ->where(['>', 'user_last_access_time', time() - 86400 * 3])->asArray()->all();
        foreach ($collectionLists as $collection)
        {
            $model = UserActiveTime::find()->where(['user_id' => $collection['user_id']])->one();
            if(is_null($model))
            {
                $model = new UserActiveTime();
            }
            if($model->last_active_time >= $collection['user_last_access_time'])
            {
                $this->printMessage("last_active_time > created_at");
                continue;
            }
            $model->user_id = $collection['user_id'];
            $model->last_pay_time = $collection['user_last_access_time'];
            $model->save();
        }


        $remindLists = RemindOrder::find()->select(['repayment_id', 'user_last_access_time'])
            ->where(['>', 'user_last_access_time', time() - 86400 * 3])->asArray()->all();
        foreach ($remindLists as $remind)
        {
            $repaymentOrder = UserLoanOrderRepayment::findOne($remind['repayment_id']);
            $model = UserActiveTime::find()->where(['user_id' => $repaymentOrder->user_id])->one();
            if(is_null($model))
            {
                $model = new UserActiveTime();
            }
            if($model->last_active_time >= $remind['user_last_access_time'])
            {
                $this->printMessage("last_active_time > created_at");
                continue;
            }
            $model->user_id = $repaymentOrder->user_id;
            $model->last_pay_time = $remind['user_last_access_time'];
            $model->save();
        }
    }

    public function actionAsyncVerifyUserBankCard(): int
    {
        if (!$this->lock()) {
            $this->printMessage("AsyncVerifyUserBankCard 已经运行中,关闭脚本");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $startTime = time();
        $execTime = mt_rand(240, 300);

        $service = new UserBankInfoService();
        while (true) {
            if ((time() - $startTime) > $execTime) {
                $this->printMessage("运行满{$execTime}秒，关闭当前脚本");
                return ExitCode::OK;
            }

            $contentsStr = RedisQueue::pop([RedisQueue::LIST_VERIFY_USER_BANK]);
            if (empty($contentsStr)) {
                sleep(2);
                continue;
            }
            $contents = json_decode($contentsStr, true);
            $form = new UserBankAccountForm();
            $form->load($contents, '');

            $isSuccess = $service->verifyAndSaveBankInfoByType($form, $contents['log_id']);
            $userBankAccountLog = UserBankAccountLog::findOne($contents['log_id']);
            if ($isSuccess) {
                $serviceRes = $service->getResult();
                $userBankAccountLog->account_id = $serviceRes['id'];
            } else {
                $errorMsg = $service->getError();
                $userBankAccountLog->remark = is_string($errorMsg) ? $errorMsg : json_encode($errorMsg);
            }
            $userBankAccountLog->save();
        }
    }

    public function actionCollectorRanking($standardDay = 0)
    {
        $standardDay = intval($standardDay);
        $startTime = Carbon::today()->addDays($standardDay)->timestamp;
        $endTime = Carbon::tomorrow()->addDays($standardDay)->timestamp;
        $repayOrderData = LoanCollectionOrder::find()
            ->alias('o')
            ->select([
                'o.outside',
                'o.current_overdue_group',
                'o.current_collection_admin_user_id',
                'count(o.id) as num',
            ])
            ->leftJoin(['r' => UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName()], 'o.user_loan_order_repayment_id = r.id')
            ->where([
                'r.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
            ])
            ->andWhere(['between', 'r.closing_time', $startTime, $endTime])
            ->andWhere(['>', 'o.current_collection_admin_user_id', 0])
            ->groupBy(['o.outside', 'o.current_overdue_group', 'o.current_collection_admin_user_id'])
            ->asArray()
            ->all();
        $formatRepayOrderData = ArrayHelper::index($repayOrderData, null, ['outside', 'current_overdue_group']);
        foreach ($formatRepayOrderData as $outside => $outsideData) {
            foreach ($outsideData as $overdueGroup => $userData) {
                $scoreMember = [];
                $scoreScore = [];
                foreach ($userData as $datum) {
                    //元素循序 遵循 先 score,后 member 不可调整
                    array_push($scoreMember, $datum['num']);//score
                    array_push($scoreMember, $datum['current_collection_admin_user_id']);//member
                    //辅助排序，score 和 member 相同（同分数，同排名）
                    array_push($scoreScore, $datum['num']);//score
                    array_push($scoreScore, $datum['num']);//member
                }
                $listMemberKey = sprintf('%s:%s:%s', RedisQueue::Z_LIST_MERCHANT_AGEING_REPAY_ORDER, $outside, $overdueGroup);
                $listScoreKey = sprintf('%s:%s:%s', RedisQueue::Z_LIST_MERCHANT_AGEING_REPAY_ORDER_SCORE, $outside, $overdueGroup);

                RedisQueue::addZSet($listMemberKey, ...$scoreMember);
                RedisQueue::addZSet($listScoreKey, ...$scoreScore);
            }
        }
    }
}