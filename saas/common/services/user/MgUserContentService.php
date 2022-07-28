<?php


namespace common\services\user;


use Carbon\Carbon;
use common\helpers\RedisQueue;
use common\models\enum\mg_user_content\UserContentType;
use common\models\user\MgUserCallReports;
use common\models\user\MgUserInstalledApps;
use common\models\user\MgUserMobileContacts;
use common\models\user\MgUserMobilePhotos;
use common\models\user\MgUserMobileSms;
use common\services\BaseService;
use frontend\models\user\UserContentForm;

class MgUserContentService extends BaseService
{
    /**
     * 数据存贮至Redis
     * @param UserContentType $contentType
     * @param UserContentForm $contentForm
     * @return bool
     */
    public function saveMgUserContentByFormToR(UserContentType $contentType, UserContentForm $contentForm): bool
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
    public function saveMgUserContentByFormToM(UserContentType $contentType, array $data): bool
    {
        $model = null;

        switch ($contentType->getValue()) {
            case UserContentType::APP_LIST()->getValue():
                $model = new MgUserInstalledApps();
                $data['_id'] = trim($data['user_id']) . '_' . md5(uniqid(mt_rand(),true));
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::CONTACT()->getValue():
                $data['_id'] = trim($data['user_id']) . '_' . trim($data['mobile']);
                if(isset($data['contactedLastTime'])){
                    $data['contactedLastTime'] = intval(intval($data['contactedLastTime']) / 1000);
                }
                if(isset($data['contactLastUpdatedTimestamp'])){
                    $data['contactLastUpdatedTimestamp'] = intval(intval($data['contactLastUpdatedTimestamp']) / 1000);
                }
                $model = MgUserMobileContacts::findOne($data['_id']);
                if (!$model) {
                    $model = new MgUserMobileContacts();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::SMS()->getValue():
                $mid = $data['_id'] ?? 0;
                $threadId = $data['threadId'] ?? 0;
                unset($data['_id']);
                unset($data['threadId']);
                unset($data['userId']);
                $data['messageDate'] = intval(intval($data['messageDate']) / 1000);
                $data['_id'] = trim($data['user_id']) . '_' . md5(implode(';', $data));
                $data['mid'] = $mid;
                $data['threadId'] = $threadId;
                $model = MgUserMobileSms::findOne($data['_id']);
                if (!$model) {
                    $model = new MgUserMobileSms();
                } else {
                    return true;
                }
                $map = array_keys($model->getAttributes());
                break;
            case UserContentType::CALL_RECORDS()->getValue():
                unset($data['userId']);
                $data['callDateTime'] = intval(intval($data['callDateTime']) / 1000);
                $data['callDuration'] = intval($data['callDuration']);
                $data['_id'] = trim($data['user_id']) . '_' . md5(implode(';', $data));
                $model = MgUserCallReports::findOne($data['_id']);
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
            if(in_array($exception->getMessage(), [
                'E11000 duplicate key error collection: user_installed_apps index: _id_',
                'E11000 duplicate key error collection: user_mobile_contacts index: _id_',
                'E11000 duplicate key error collection: user_mobile_sms index: _id_',
                'E11000 duplicate key error collection: user_call_reports index: _id_',
            ])){
                return true;
            }

            throw $exception;
        }
    }

    /**
     * 数据入库
     * @param array $data
     * @return bool
     */
    public function saveMgUserPhoto(array $data): bool
    {
        $model = new MgUserMobilePhotos();
        $data['_id'] = trim($data['user_id']) . '_' . md5(uniqid(mt_rand(),true));
        $data['AlbumFileCrawlTime'] = intval(intval($data['AlbumFileCrawlTime']) / 1000);
        $data['AlbumFileLastModifiedTime'] = intval(intval($data['AlbumFileLastModifiedTime']) / 1000);
        $map = array_keys($model->getAttributes());

        foreach ($map as $item) {
            if (in_array($item, ['created_at', 'updated_at'])) {
                continue;
            }

            if(isset($data[$item])){
                $model->$item = $data[$item] ?? '';
            }
        }

        return $model->save();
    }

    //获取通讯录数据
    public static function getContactData($user_id, $db = null) {
        $lock_key = RedisQueue::CUISHOU_USER_TXL_KEY.$user_id;
        $mobile_contact = RedisQueue::get(['key'=>$lock_key]);
        if ($mobile_contact) {
            return json_decode($mobile_contact, true);
        }

        $mobile_contact = MgUserMobileContacts::find()
            ->where(['user_id' => $user_id])
            ->asArray()
            ->all($db);
        if ($mobile_contact) {
            if ($user_id % 10 == 0) { // 先缓存1/10的量
                RedisQueue::set([
                    'expire' => 43200,
                    'key' => $lock_key,
                    'value' => json_encode($mobile_contact),
                ]);
            }
        }

        return $mobile_contact;
    }
}