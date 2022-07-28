<?php

namespace common\services\message;

use common\helpers\RedisQueue;
use common\models\message\ExternalOrderMessageForm;
use common\services\BaseService;


class ExternalOrderMessageService extends BaseService
{
    //推入外部push队列
    public function pushToExternalPushQueue(ExternalOrderMessageForm $form)
    {
        RedisQueue::push([RedisQueue::LIST_EXTERNAL_ORDER_PUSH, json_encode($form->toArray(), JSON_UNESCAPED_UNICODE)]);
    }
    //内部push队列
    public function pushToInsidePushQueue(ExternalOrderMessageForm $form)
    {
        RedisQueue::push([RedisQueue::LIST_INSIDE_ORDER_PUSH, json_encode($form->toArray(), JSON_UNESCAPED_UNICODE)]);
    }

    //推入message队列
    public function pushToMessageQueue(ExternalOrderMessageForm $form)
    {
        RedisQueue::push([RedisQueue::LIST_EXTERNAL_ORDER_MESSAGE, json_encode($form->toArray(), JSON_UNESCAPED_UNICODE)]);
    }

    //获取外部订单产品名
    public function getProductName($is_export, $app_market)
    {
        if($app_market)
        {
            $productNameArray = explode('_',$app_market);
        }

        if(!$productNameArray)
        {
            return '';
        }
        return $is_export ? $productNameArray[1] : $productNameArray[0];
    }

}
