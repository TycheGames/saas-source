<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：14:41
 */

namespace backend\controllers;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use yii;
use yii\web\Response;


class DevelopmentToolsController extends BaseController
{

    /**
     * @name string 表结构缓存清理
     * @return array|string
     */
    public function actionClearSchemaCache()
    {
        if(yii::$app->request->isPost)
        {
            yii::$app->response->format = Response::FORMAT_JSON;
            if(CommonHelper::clearSchemaCache()){
                return [
                    'code' => 0,
                    'msg' => Yii::T('common', 'Cleaned up success')
                ];
            }else{
                return [
                    'code' => -1,
                    'msg' => Yii::T('common', 'Cleaned up fail')
                ];
            }
        }
        return $this->render('clear-schema-cache');
    }

    /**
     * @name 开发工具-重入风控队列
     */
    public function actionPushRedis()
    {
        $list = [
            RedisQueue::CREDIT_AUTO_CHECK => '机审队列-主决策树',
            RedisQueue::CREDIT_USER_CREDIT_CALC => '还款额度队列',
            RedisQueue::CREDIT_NOT_PUSH_LIST => '风控回调失败队列',
        ];
        if ($this->getRequest()->isPost) {
            $params = $this->getRequest()->post();

            if (empty($params['list_type'] || !isset($list[$params['list_type']]))) {
                return $this->redirectMessage('队列名错误', self::MSG_ERROR);
            }

            if (empty($params['ids'])) {
                return $this->redirectMessage('ids不能为空', self::MSG_ERROR);
            }

            $ids = explode(PHP_EOL, $params['ids']);

            foreach ($ids as $id) {
                $id = trim($id);
                if (empty($id)) {
                    continue;
                }
                if (!is_numeric($id)) {
                    return $this->redirectMessage(\sprintf('id：%s 类型错误', $id), self::MSG_ERROR);
                }

                RedisQueue::push([$params['list_type'], $id]);
            }

            return $this->redirectMessage('push成功', self::MSG_SUCCESS);

        }
        return $this->render('push-redis', [
            'list' => $list
        ]);
    }

    /**
     * @name  首页-管理中心首页
     * @return string
     */
    public function actionRedisList()
    {
        $redisList = [
            ['key' => RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD, 'name' => '上报app名字'],
            ['key' => RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD, 'name' => '上报通讯录'],
            ['key' => RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD, 'name' => '上报短信'],
            ['key' => RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD, 'name' => '上报通话记录'],
            ['key' => RedisQueue::CREDIT_AUTO_CHECK, 'name' => '主决策新客'],
            ['key' => RedisQueue::CREDIT_AUTO_CHECK_OLD, 'name' => '主决策老客'],
        ];
        foreach ($redisList as $key => $val) {
            $redisList[$key]['length'] = RedisQueue::getLength([$val['key']]);
        }
        return $this->render('redis-list', [
            'redisList' => $redisList,
        ]);
    }
}