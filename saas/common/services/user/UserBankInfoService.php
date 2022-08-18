<?php

namespace common\services\user;

use Carbon\Carbon;
use common\helpers\bank\IFSC;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\ClientInfoLog;
use common\models\enum\validation_rule\ValidationServiceProvider;
use common\models\enum\validation_rule\ValidationServiceType;
use common\models\order\UserLoanOrder;
use common\models\third_data\ValidationRule;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserBankAccountLog;
use common\models\user\UserRegisterInfo;
use common\services\BaseService;
use common\services\order\OrderService;
use frontend\models\user\UserBankAccountForm;
use frontend\models\user\UserBankAccountStatusForm;
use GuzzleHttp\Exception\RequestException;
use Yii;

/**
 * Class UserBankInfoService
 * @package common\services\user
 * @property UserBankAccount $userBankAccount
 * @property
 */
class UserBankInfoService extends BaseService implements IThirdDataService
{

    private $userBankAccount;
    private $service;

    const SUCCESS = 'success';
    const FAILED = 'failed';
    const RETRY = 'retry';

    const CODE_BING_SUCCESS = 0;
    const CODE_BING_ERROR = 1;
    const CODE_BING_PENDING = 2;

    const REJECT = 'reject';
    const ALLOW = 'allow';
    const SKIP = 'skip';


    /**
     * 检查数据是否过期，true:过期 false:未过期
     * @return bool
     */
    public function checkDataExpired(Carbon $updateTime): bool
    {
        return false;
    }

    /**
     * 异步认证银行卡-提交
     * @param UserBankAccountForm $form
     * @return bool
     */
    public function asyncSaveBankInfo(UserBankAccountForm $form): bool
    {
        $loanPerson = LoanPerson::findById($form->userId);
        $sourceId = $loanPerson->source_id;

        $bankAccountLog = new UserBankAccountLog();
        $bankAccountLog->user_id = $form->userId;
        $bankAccountLog->account = $form->account;
        $bankAccountLog->ifsc = $form->ifsc;
        $bankAccountLog->name = $form->name;
        $bankAccountLog->status = UserBankAccountLog::STATUS_SUCCESS;
        $bankAccountLog->save();

        $userBankAccount = new UserBankAccount();
        $userBankAccount->user_id = $form->userId;
        $userBankAccount->source_id = $sourceId;
        $userBankAccount->source_type = 'source_type';
        $userBankAccount->name = $form->name;
        $userBankAccount->account = $form->account;
        $userBankAccount->ifsc = 'ifsc';
        $userBankAccount->main_card = UserBankAccount::MAIN_IS;
        $userBankAccount->status = UserBankAccount::STATUS_UNVERIFIED;
        $userBankAccount->report_account_name = 'report_account_name';
        $userBankAccount->service_account_name = 'service_account_name';
        $userBankAccount->bank_name = 'bank_name';
        $userBankAccount->data = 'data';
        $userBankAccount->client_info = json_encode($form->clientInfo,JSON_UNESCAPED_UNICODE);
        $userBankAccount->merchant_id = $loanPerson->merchant_id;
        $userBankAccount->save();

        $this->setResult([
            'id' => CommonHelper::idEncryption($bankAccountLog->id),
        ]);
        return true;






        



        if (strpos($form->ifsc, ' ') !== false || strpos($form->account, ' ') !== false) {
            $this->setError('IFSC Code or Bank Account Number error');
            return false;
        }
        $ifscTag = substr($form->ifsc, 0, 3);
        if(in_array(strtolower($ifscTag), yii::$app->params['bankMaintenanceList']))
        {
            $this->setError('Due to the banking system, State Bank Of India is temporarily not supported, please choose to use a new bank card');
            return false;
        }

        $loanPerson = LoanPerson::findById($form->userId);

        //判检查当前用户体系下是否已绑定
        $bankAccountExists = UserBankAccount::find()
            ->where(['account' => $form->account])
            ->andWhere(['source_id' => $loanPerson->source_id])
            ->andWhere(['!=', 'status', UserBankAccount::STATUS_FAILED])
            ->exists();
        if ($bankAccountExists) {
            $this->setError('The account already exists');
            return false;
        }

        $bankAccountLog = new UserBankAccountLog();
        $bankAccountLog->user_id = $form->userId;
        $bankAccountLog->account = $form->account;
        $bankAccountLog->ifsc = $form->ifsc;
        $bankAccountLog->name = $form->name;
        $bankAccountLog->status = UserBankAccountLog::STATUS_UNVERIFIED;
        $bankAccountLog->save();
        $data = array_merge(['log_id' => $bankAccountLog->id], $form->toArray());
        RedisQueue::push([RedisQueue::LIST_VERIFY_USER_BANK, json_encode($data)]);

        $this->setResult([
            'id' => CommonHelper::idEncryption($bankAccountLog->id),
        ]);

        return true;
    }

    public function asyncGetBankStatus(UserBankAccountStatusForm $form): bool
    {
        /**
         *@var UserBankAccountLog $bankAccountLog
         */
        $bankAccountLog = UserBankAccountLog::find()
            ->where([
                'id' => CommonHelper::idDecryption($form->id),
                'user_id' => $form->userId,
            ])
            ->andWhere(['>', 'created_at', strtotime('-5 minutes')])
            ->one();

        if (empty($bankAccountLog)) {
            $this->setResult([
                'id'     => null,
                'status' => self::CODE_BING_ERROR,
            ]);
            return true;
        }

        $this->setResult([
            'id'     => $bankAccountLog->account_id,
            'status' => UserBankAccountLog::STATUS_SUCCESS == $bankAccountLog->status ?
                self::CODE_BING_SUCCESS : self::CODE_BING_PENDING,
        ]);

        return true;
    }

    public function verifyAndSaveBankInfoByType(UserBankAccountForm $from, string $logId, string $type = 'auto')
    {
        $loanPerson = LoanPerson::findById($from->userId);
        $sourceId = $loanPerson->source_id;
        if (strpos($from->ifsc, ' ') !== false || strpos($from->account, ' ') !== false) {
            $this->setError('IFSC Code or Bank Account Number error');
            return false;
        }
        $ifscTag = substr($from->ifsc, 0, 3);
        if(in_array(strtolower($ifscTag), yii::$app->params['bankMaintenanceList']))
        {
            $this->setError('Due to the banking system, State Bank Of India is temporarily not supported, please choose to use a new bank card');
            return false;
        }
        //屏蔽 YES Bank的银行卡
//        if (strpos(strtoupper($from->ifsc),'YESB') !== false) {
//            $this->setError('Due to business adjustment, the bank card is not supported.');
//            return false;
//        }

        //判断该银行账号在当前用户体系下是否已存在，认证状态不是失败（即认证中，认证成功）
        $bankAccountExists = UserBankAccount::find()
            ->where(['account' => $from->account])
            ->andWhere(['source_id' => $sourceId])
            ->andWhere(['!=', 'status', UserBankAccount::STATUS_FAILED])
            ->exists();
        if ($bankAccountExists) {
            $this->setError('The account already exists');
            return false;
        }

        /**
         * @var UserBankAccount $successBankAccount
         */
        //判断该银行卡在全平台是否存在认证成功的记录
        $successBankAccount = UserBankAccount::find()
            ->where(['account' => $from->account, 'merchant_id' => $loanPerson->merchant_id])
            ->andWhere(['in', 'status', [UserBankAccount::STATUS_SUCCESS, UserBankAccount::STATUS_UNVERIFIED]])
            ->limit(1)
            ->one();
        $bankData = [];
        $userBankAccountLog = UserBankAccountLog::findOne($logId);
        if ($successBankAccount) {
            $bankData['source_type'] = UserBankAccount::SOURCE_DATABASE;
            $bankData['report_account_name'] = $successBankAccount->report_account_name;
            $bankData['bank_name'] = $successBankAccount->bank_name;
            $bankData['data'] = $successBankAccount->data;
            $bankData['ifsc'] = $successBankAccount->ifsc;
            $userBankAccountLog->source_type = UserBankAccountLog::SOURCE_DATABASE;
            $userBankAccountLog->report_account_name = $bankData['report_account_name'];
            $userBankAccountLog->bank_name = $bankData['bank_name'];
            $userBankAccountLog->data = $bankData['data'];
            $userBankAccountLog->status = UserBankAccountLog::STATUS_SUCCESS;
            $userBankAccountLog->save();
        } else {
            $packageName = $from->clientInfo['packageName'] ?? '';
            $type = $packageName == 'rupeecash' ? 'aadhaarApi' : $type;
            switch ($type) {
                case 'yuanDing':
                    $response = $this->verifyBankInfoAuto(
                        $from, ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey());
                    break;
                case 'aadhaarApi':
                    $response = $this->verifyBankInfoAuto(
                        $from, ValidationServiceProvider::VERIFY_BANK_AADHAAR_API()->getKey());
                    break;
                case 'auto':
                default :
                    $response = $this->verifyBankInfoAuto($from);
            }
            $userBankAccountLog->source_type = $response['source_type'];
            $userBankAccountLog->report_account_name = $response['report_account_name'];
            $userBankAccountLog->bank_name = $response['bank_name'];
            $userBankAccountLog->data = $response['data'];
            $userBankAccountLog->status = $response['success'] ? UserBankAccountLog::STATUS_SUCCESS : UserBankAccountLog::STATUS_FAILED;
            $userBankAccountLog->save();
            if ($response['success']) {
                $bankData['source_type'] = $response['source_type'];
                $bankData['report_account_name'] = $response['report_account_name'];
                $bankData['service_account_name'] = $response['service_account_name'];
                $bankData['bank_name'] = $response['bank_name'];
                $bankData['data'] = $response['data'];
                $bankData['ifsc'] = $from->ifsc;
            } else {
                $this->setError($response['serviceError']);
                return false;
            }
        }
        $userBankAccount = UserBankAccount::find()
            ->where(['account' => $from->account])
            ->andWhere(['source_id' => $sourceId])
            ->andWhere(['user_id' => $from->userId])
            ->andWhere(['status' => UserBankAccount::STATUS_FAILED])
            ->limit(1)
            ->one();
        if (!$userBankAccount) {
            $userBankAccount = new UserBankAccount();
        }

        $userBankAccount->user_id = $from->userId;
        $userBankAccount->source_id = $sourceId;
        $userBankAccount->source_type = $bankData['source_type'];
        $userBankAccount->name = $from->name;
        $userBankAccount->account = $from->account;
        $userBankAccount->ifsc = $bankData['ifsc'] ?? $from->ifsc;
        $userBankAccount->main_card = UserBankAccount::MAIN_IS;
        $userBankAccount->status = UserBankAccount::STATUS_UNVERIFIED;
        $userBankAccount->report_account_name = $bankData['report_account_name'];
        $userBankAccount->service_account_name = $bankData['service_account_name'] ?? '';
        $userBankAccount->bank_name = $bankData['bank_name'];
        $userBankAccount->data = $bankData['data'];
        $userBankAccount->client_info = json_encode($from->clientInfo,JSON_UNESCAPED_UNICODE);
        $userBankAccount->merchant_id = $loanPerson->merchant_id;

        $logData = $userBankAccount->toArray();
        unset($logData['client_info']);
        Yii::info($logData, 'UserBankAccountRecord');
        $user = UserRegisterInfo::findOne(['user_id' => $from->userId]);
        if($userBankAccount->save()){
            if($userBankAccount->status == UserBankAccount::STATUS_SUCCESS){
                Yii::info(['user_id' => $from->userId,'appMarket' => $user['appMarket'],'type' => 'bank_verify','status' => 'success','msg' => 'success'],'auth_info');
            }else{
                Yii::info(['user_id' => $from->userId,'appMarket' => $user['appMarket'],'type' => 'bank_verify','status' => 'fail','msg' => 'fail'],'auth_info');
            }
            ClientInfoLog::addLog($from->userId, ClientInfoLog::EVENT_BIND_CARD, $userBankAccount->id, $from->clientInfo);
            $this->setResult(['id' => $userBankAccount->id]);
            return true;
        }else{
            Yii::info(['user_id' => $from->userId,'appMarket' => $user['appMarket'],'type' => 'bank_verify','status' => 'fail','msg' => 'save-fail'],'auth_info');
            $this->setError('system error');
            return false;
        }
    }

    private function verifyBankInfoAuto(UserBankAccountForm $form, string $provider = null)
    {
        if (is_null($provider)) {
            if ($form->userId % 2 == 0) {
                //aadhaar_api
                $serviceKey = RedisQueue::KEY_VALIDATION_BANK_SERVICE_0;
                $serviceName = ValidationServiceProvider::VERIFY_BANK_AADHAAR_API()->getKey();
                $switchServiceName = ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey();
            } else {
                //yuan_ding
                $serviceKey = RedisQueue::KEY_VALIDATION_BANK_SERVICE_1;
                $serviceName = ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey();
                $switchServiceName = ValidationServiceProvider::VERIFY_BANK_AADHAAR_API()->getKey();
            }
            $currentService = RedisQueue::get(['key' => $serviceKey]);
            if (is_null($currentService)) {
                $currentService = $serviceName;
                RedisQueue::set([
                    'expire' => 86400,
                    'key'    => $serviceKey,
                    'value'  => $serviceName,
                ]);
            } elseif ($currentService == 'disable') {
                $currentService = $switchServiceName;
            }
        } else {
            $currentService = $provider;
        }
        switch ($currentService) {
            case ValidationServiceProvider::VERIFY_BANK_AADHAAR_API()->getKey():
                $user = LoanPerson::findById($form->userId);
                $service = new AadhaarApiService([
                    'userSourceId' => $user->source_id
                ]);
                try {
                    $response = $service->getBankInfo($form->account, $form->ifsc);
                } catch (RequestException $requestException) {
                    $this->recordBankServiceError(ValidationServiceProvider::VERIFY_BANK_AADHAAR_API());
                    throw $requestException;
                } catch (\Exception $exception) {
                    $this->recordBankServiceError(ValidationServiceProvider::VERIFY_BANK_AADHAAR_API());
                    throw $exception;
                }
                $thirdSuccess = $response['success'];
                $sourceType = UserBankAccount::SOURCE_AADHAAR_API;
                $reportAccountName = $response['data']['full_name'] ?? '';
                $bankName = '';
                $reportData = $response ?? null;
                break;
            case ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey():
                $user = LoanPerson::findById($form->userId);
                $service  = new YuanDingBankVerificationService([
                    'userSourceId' => $user->source_id
                ]);
                try {
                    $thirdSuccess = $service->getBankInfo($form->name, $form->account, $form->ifsc);
                } catch (RequestException $exception) {
                    $this->recordBankServiceError(ValidationServiceProvider::VERIFY_BANK_YUAN_DING());
                    throw $exception;
                }
                $sourceType = UserBankAccount::SOURCE_YUAN_DING;
                $reportAccountName = $service->response['data']['benename'] ?? '';
                $bankName = $service->response['data']['bank'] ?? '';
                $reportData = $service->response ?? null;
                break;
            default:
                //默认使用数据源
                return $this->verifyBankInfoAuto($form, ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey());
                break;
        }
        $bankData['success'] = $thirdSuccess;
        $bankData['source_type'] = $sourceType;
        $bankData['report_account_name'] = $reportAccountName;
        $bankData['bank_name'] = empty($bankName) ? IFSC::getBankName($form->ifsc) : $bankName;
        $bankData['data'] = is_string($reportData) ? $reportData : json_encode($reportData,JSON_UNESCAPED_UNICODE);
        $bankData['serviceError'] = $service->getError();
        $bankData['service_account_name'] = $service->apiId;

        return $bankData;
    }

    private function recordBankServiceError(ValidationServiceProvider $provider)
    {
        $keySuffix = date('Hi');
        switch ($provider->getKey()) {
            case ValidationServiceProvider::VERIFY_BANK_AADHAAR_API()->getKey():
                $keyPrefix = RedisQueue::KEY_PREFIX_VALIDATION_BANK_AADHAAR_API;
                $serviceName = RedisQueue::KEY_VALIDATION_BANK_SERVICE_0;
                break;
            case ValidationServiceProvider::VERIFY_BANK_YUAN_DING()->getKey():
            default:
                $serviceName = RedisQueue::KEY_VALIDATION_BANK_SERVICE_1;
                $keyPrefix = RedisQueue::KEY_PREFIX_VALIDATION_BANK_YUAN_DING;
                break;
        }
        $keyName = $keyPrefix . '_' .  $keySuffix;
        RedisQueue::inc([$keyName, 1]);
        RedisQueue::expire([$keyName, 86400]);

        /**
         * @var ValidationRule $rule
         */
        $rule = ValidationRule::find()
            ->where(['service_current' => $provider->getValue()])
            ->andWhere(['validation_type' => ValidationServiceType::VERIFY_BANK()->getValue()])
            ->andWhere(['is_used' => ValidationRule::IS_USED])
            ->orderBy(['id' =>SORT_DESC])
            ->limit(1)
            ->one();
        $errorCount = 0;
        for ($i = 1; $i <= $rule->service_time; $i++) {
            $strTime = "-{$i} minute";
            $checkKeySuffix = date('Hi', strtotime($strTime));
            $checkKeyName = $keyPrefix . '_' . $checkKeySuffix;
            $errorCount += RedisQueue::get(['key' => $checkKeyName]);
        }


        if ($errorCount >= $rule->service_error) {
            //清空近期的数据
            for ($i = 1; $i <= $rule->service_time; $i++) {
                $strTime = "-{$i} minute";
                $checkKeySuffix = date('Hi', strtotime($strTime));
                $checkKeyName = $keyPrefix . '_' . $checkKeySuffix;
                RedisQueue::set([
                    'expire' => 86400,
                    'key'    => $checkKeyName,
                    'value'  => 0,
                ]);
            }
            RedisQueue::set([
                'expire' => 3600,
                'key'    => $serviceName,
                'value'  => 'disable',
            ]);
        }
    }

    /**
     * 选择主卡
     * @param $id
     * @param $userId
     * @return bool
     */
    public function changeMainCard($id, $userId)
    {
        /**
         * @var UserBankAccount $model
         */
        $model = UserBankAccount::find()->where([
            'id' => $id,
            'user_id' => $userId,
            'status' => UserBankAccount::STATUS_SUCCESS
        ])->limit(1)->one();
        if(is_null($model)){
            $this->setError('参数错误');
            return false;
        }
        if(UserBankAccount::MAIN_IS == $model->main_card)
        {
            return true;
        }
        UserBankAccount::updateAll(['main_card' => UserBankAccount::MAIN_NO ],['user_id' =>$userId]);
        $model->main_card = UserBankAccount::MAIN_IS;
        return $model->save();
    }

    /**
     * 获取用户所有已绑卡信息
     * @param $userId
     * @return array
     */
    public function getUserBankAccounts($userId)
    {
        $list = [];
        $models = UserBankAccount::find()
            ->select(['id','account','ifsc','status'])
            ->where(['user_id' => $userId, 'status' => [UserBankAccount::STATUS_SUCCESS, UserBankAccount::STATUS_UNVERIFIED]])->orderBy(['id' => SORT_DESC])->all();
        /**
         * @var UserBankAccount $model
         */
        foreach($models as $model)
        {
            //屏蔽 YES Bank的银行卡
//            if (strpos(strtoupper($model->ifsc),'YESB') !== false) {
//                continue;
//            }
            $list[] = [
                'id' => $model->id,
                'account'   => CommonHelper::strMask($model->account,4,4),
                'ifsc' => $model->ifsc,
                'status' => $model->status
            ];
        }

        return $list;
    }

}
