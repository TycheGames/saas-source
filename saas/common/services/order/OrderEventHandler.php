<?php


namespace common\services\order;

use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\order\UserLoanOrder;
use yii\base\Event;

class OrderEventHandler
{
    /**
     * 事件处理
     * @param Event $event 事件
     */
    public static function remindByOderStatus(Event $event)
    {
        /**
         * @var UserLoanOrder $order
         */
        $order = $event->sender;
        switch ($event->name)
        {
            case UserLoanOrder::EVENT_AFTER_CHANGE_STATUS:
                if($order->status == UserLoanOrder::STATUS_WAIT_DRAW_MONEY &&
                    $order->loan_status == UserLoanOrder::LOAN_STATUS_DRAW_MONEY
                ) {
                    //延迟队列
                    $dataStr = json_encode([
                        'order_id'     => $order->id,
                        'delay_minute' => 20,
                    ]);
                    RedisDelayQueue::pushDelayQueue(RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY_AUTO, $dataStr, 1200);
                }
                break;
            default:
                break;
        }
    }
}