<?php


namespace common\services\user;


use Carbon\Carbon;
use common\helpers\RedisQueue;
use common\models\enum\mg_user_content\UserContentType;
use common\models\user\MgUserCallReports;
use common\models\user\MgUserInstalledApps;
use common\models\user\MgUserMobileContacts;
use common\services\BaseService;
use common\services\MgUserMobileSmsService;
use frontend\models\risk\UserContentForm;
use Yii;

class MgUserContentService extends BaseService
{
    /**
     * 数据存贮至Redis
     * @param UserContentType $contentType
     * @param UserContentForm $contentForm
     * @return bool
     */
    public function saveMgUserContentByFormToRNew(UserContentType $contentType, UserContentForm $contentForm): bool
    {
        switch ($contentType->getValue()) {
            case UserContentType::APP_LIST()->getValue():
                $key = RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD;
                break;
            case UserContentType::CONTACT()->getValue():
                $key = RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD;
                break;
            case UserContentType::SMS()->getValue():
                $key = RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD;
                break;
            case UserContentType::CALL_RECORDS()->getValue():
                $key = RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD;
                break;
            default:
                return false;
        }

        return RedisQueue::push([$key, json_encode($contentForm->toArray())]);
    }

    /**
     * 数据入库
     * @param UserContentType $contentType
     * @param array $data
     * @return bool
     */
    public function saveMgUserContentByFormToMNew(UserContentType $contentType, array $data): bool
    {
        $model = null;
        unset($data['_id']);
        unset($data['created_at']);
        unset($data['updated_at']);

        switch ($contentType->getValue()) {
            case UserContentType::APP_LIST()->getValue():
                $data['_id'] = $data['app_name'] . '_' . trim($data['user_phone']) . '_' . md5(json_encode($data['addeds']));
                $model = MgUserInstalledApps::find()->where(['_id' => $data['_id']])->exists();
                if(!$model){
                    $model = new MgUserInstalledApps();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::CONTACT()->getValue():
                $data['_id'] = $data['app_name'] . '_' . trim($data['user_phone']) . '_' . trim($data['mobile']);
                $model = MgUserMobileContacts::find()->where(['_id' => $data['_id']])->exists();
                if (!$model) {
                    $model = new MgUserMobileContacts();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::SMS()->getValue():
                ksort($data);
                $data['_id'] = $data['pan_code'] . '_' . md5(implode(';', $data));
                $class = MgUserMobileSmsService::getModelName($data['pan_code']);
                $model = $class::find()->where(['_id' => $data['_id']])->exists();
                if (!$model) {
                    $model = new $class();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::CALL_RECORDS()->getValue():
                $data['_id'] = $data['app_name'] . '_' . trim($data['user_phone']) . '_' . md5(implode(';', $data));
                $model = MgUserCallReports::find()->where(['_id' => $data['_id']])->exists();
                if (!$model) {
                    $model = new MgUserCallReports();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            default:
                return false;
        }

        foreach ($map as $item) {
            if (in_array($item, ['created_at', 'updated_at'])) {
                continue;
            }

            if(isset($data[$item])) {
                $model->$item = $data[$item] ?? '';
            }
        }

        try {
            return $model->save();
        } catch (\Exception $exception){
            if(strpos($exception->getMessage(), 'E11000 duplicate key error collection:') !== false){
                return true;
            }

            throw $exception;
        }
    }
}