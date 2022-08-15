<?php


namespace console\controllers;


use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\enum\mg_user_content\UserContentType;
use common\services\MgUserMobileSmsService;
use common\services\user\MgUserContentService;
use yii\console\Controller;
use Yii;

class UserController extends BaseController
{
    /**
     * 同步用户通讯录
     */
    public function actionUserContentMobile($processNum = 1)
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $execTime = mt_rand(180, 250);

        $this->printMessage("脚本开始执行，本次最大执行时间为{$execTime}秒");
        $service = new MgUserContentService();

        while (true) {
            if(time() - $now > $execTime)
            {
                $this->printMessage("运行满{$execTime}秒，关闭当前脚本");
                return;
            }
            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD]);
            if(empty($contentsStr)){
                $this->printMessage("队列为空,暂停1秒");
                sleep(1);
                continue;
            }
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            $startTime = time();
            foreach ($contents['data'] as $datum) {
                $datum['app_name'] = $contents['app_name'];
                $service->saveMgUserContentByFormToMNew(UserContentType::CONTACT(), $datum);
            }
            $costTime = time() - $startTime;
            $this->printMessage("落库完成，耗时{$costTime}秒");
        }
    }

    /**
     * 同步用户APP安装列表
     */
    public function actionUserContentApp($processNum = 1)
    {
        if(!$this->lock()){
            return;
        }
        Util::cliLimitChange(256);
        $now = time();
        $execTime = mt_rand(180, 250);

        $this->printMessage("脚本开始执行，本次最大执行时间为{$execTime}秒");
        $service = new MgUserContentService();

        while (true) {
            if(time() - $now > $execTime)
            {
                $this->printMessage("运行满{$execTime}秒，关闭当前脚本");
                return;
            }
            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD]);
            if(empty($contentsStr)){
                $this->printMessage("队列为空,暂停1秒");
                sleep(1);
                continue;
            }
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            $startTime = time();
            foreach ($contents['data'] as $datum) {
                if(empty($datum['addeds'])){
                    continue;
                }
                $datum['app_name'] = $contents['app_name'];
                $service->saveMgUserContentByFormToMNew(UserContentType::APP_LIST(), $datum);

            }
            $costTime = time() - $startTime;
            $this->printMessage("落库完成，耗时{$costTime}秒");
        }
    }

    /**
     * 同步用户短信记录
     */
    public function actionUserContentSms($processNum = 1)
    {
        if(!$this->lock()){
            return;
        }
        Util::cliLimitChange();
        $now = time();
        $execTime = mt_rand(180, 250);

        $this->printMessage("脚本开始执行，本次最大执行时间为{$execTime}秒");
//        $service = new MgUserContentService();

        while (true) {
            if(time() - $now > $execTime)
            {
                $this->printMessage("运行满{$execTime}秒，关闭当前脚本");
                exit;
            }
            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD]);
            if(empty($contentsStr)){
                $this->printMessage("队列为空,暂停1秒");
                sleep(1);
                continue;
            }
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            $startTime = time();
//            foreach ($contents['data'] as $datum) {
//                $service->saveMgUserContentByFormToMNew(UserContentType::SMS(), $datum);
//            }

            $data = [];
            $pan_code = '';
            foreach ($contents['data'] as $v){
                unset($v['_id']);
                unset($v['created_at']);
                unset($v['updated_at']);
                ksort($v);
                $v['_id'] = $v['pan_code'] . '_' . md5(implode(';', $v));
                $data[$v['_id']] = $v;
                $pan_code = $v['pan_code'];
            }

            if(empty($data)){
                $this->printMessage('短信为空,下一条');
                continue;
            }

            $class = MgUserMobileSmsService::getModelName($pan_code);
            $offset = 0;
            $limit = 1000;
            $query = $class::find()->select(['_id'])->where(['pan_code' => $pan_code])->limit($limit);
            $cloneQuery = clone $query;
            $info = $cloneQuery->offset($offset)->asArray()->all();
            while ($info){
                foreach ($info as $sms){
                    unset($data[$sms['_id']]);
                }

                $offset += $limit;
                $cloneQuery = clone $query;
                $info = $cloneQuery->offset($offset)->asArray()->all();
            }

            foreach ($data as $value) {
                $model = new $class;
                $map   = array_keys($model->getAttributes());

                foreach ($map as $item) {
                    if (isset($value[$item])) {
                        $model->$item = $value[$item];
                    }
                }

                try {
                    $model->save();
                } catch (\Exception $exception) {
                    if (strpos($exception->getMessage(), 'E11000') === false) {
                        throw $exception;
                    }
                }
            }

            $costTime = time() - $startTime;
            $this->printMessage("落库完成，耗时{$costTime}秒");
        }
    }

    /**
     * 同步用户通话记录
     */
    public function actionUserContentCallRecords($processNum = 1)
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $execTime = mt_rand(180, 250);

        $this->printMessage("脚本开始执行，本次最大执行时间为{$execTime}秒");
        $service = new MgUserContentService();

        while (true) {
            if(time() - $now > $execTime)
            {
                $this->printMessage("运行满{$execTime}秒，关闭当前脚本");
                return;
            }
            $contentsStr = RedisQueue::pop([RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD]);
            if(empty($contentsStr)){
                $this->printMessage("队列为空,暂停1秒");
                sleep(1);
                continue;
            }
            $contents = json_decode($contentsStr, true);
            $contents['data'] = json_decode($contents['data'], true);
            $startTime = time();
            foreach ($contents['data'] as $datum) {
                $datum['app_name'] = $contents['app_name'];
                $service->saveMgUserContentByFormToMNew(UserContentType::CALL_RECORDS(), $datum);
            }
            $costTime = time() - $startTime;
            $this->printMessage("落库完成，耗时{$costTime}秒");
        }
    }

}