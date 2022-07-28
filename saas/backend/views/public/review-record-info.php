<?php

use common\models\order\UserOrderLoanCheckLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\enum\verify\RejectCode;
use common\helpers\CommonHelper;
use yii\helpers\Html;

?>

<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10" >Historical audit information</th></tr>
    <tr>
        <?php if (empty($informationAll['userOrderLoanCheckLogRecord'])): ?>
            <td>No record</td>
        <?php else : ?>
            <td style=" padding: 2px;margin-bottom: 1px">
                <table style="margin-bottom: 0px" class="table">
                    <tr>
                        <th>Order Id</th>
                        <th>Operator</th>
                        <th>Audit Type</th>
                        <th>Audit time</th>
                        <th>Audit content</th>
                        <th>Operation type</th>
                        <th>Audit code</th>
                        <th>Before status of auditing</th>
                        <th>After status of auditing</th>
                    </tr>
                    <?php foreach ($informationAll['userOrderLoanCheckLogRecord'] as $log): ?>
                        <tr>
                            <td><?= Html::encode(CommonHelper::idEncryption($log['order_id'], 'order'));?></td>
                            <td><?= Html::encode($log['operator']);?></td>
                            <td><?= Html::encode(isset($log['type']) ? UserOrderLoanCheckLog::$type_list[$log['type']] : "--");?></td>
                            <td><?= Html::encode(date("Y-m-d H:i:s",$log['created_at']));?></td>
                            <td><?= Html::encode($log['audit_remark']);?></td>
                            <td><?= Html::encode(empty($log['operation_type']) ? "--" : UserOrderLoanCheckLog::$operation_type_list[$log['operation_type']] );?></td>
                            <td>
                                <?= Html::encode($log['head_code'].'|'.$log['back_code']);?></td>
                            </td>
                            <?php if(empty($log['repayment_type'])) : ?>
                                <td><?= UserLoanOrder::$order_status_map[$log['before_status']];?></td>
                                <td><?= UserLoanOrder::$order_status_map[$log['after_status']];?></td>
                            <?php else : ?>
                                <td><?= UserLoanOrderRepayment::$repayment_status_map[$log['before_status']];?></td>
                                <td><?= UserLoanOrderRepayment::$repayment_status_map[$log['after_status']];?></td>
                            <?php endif; ?>
                            <?php  ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        <?php endif; ?>
    </tr>
</table>
