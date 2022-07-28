<?php
use common\helpers\CommonHelper;
use yii\helpers\Html;

?>

<table class="tb tb2 fixpadding" id="creditreport">
    <tr><th class="partition" colspan="10">Details of loan order</th></tr>
    <tr>
        <th width="110px;" class="person">Order info</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th class="td24">Order Id：</th>
                    <td ><?= Html::encode(CommonHelper::idEncryption($information['userLoanOrder']['id'], 'order')); ?></td>
                    <th class="td24">Borrowing balance(rupee)</th>
                    <td class="mark"><?=Html::encode($information['userLoanOrder']['amount'] == 0 ? '--' : sprintf("%.2f",($information['userLoanOrder']['amount'] + $information['userLoanOrder']['interests']) / 100)); ?></td>
                    <th class="td24">application time</th>
                    <td class="mark"><?= Html::encode(date("Y-m-d H:i:s",$information['userLoanOrder']['order_time'])); ?></td>
                </tr>
                <tr>

                    <th class="td24">Interests</th>
                    <td class="mark"><?= Html::encode($information['userLoanOrder']['interests'] == 0 ? '--' : sprintf("%.2f",$information['userLoanOrder']['interests'] / 100)); ?></td>
                    <th class="td24">Processing fees</th>
                    <td class="mark"><?= Html::encode($information['userLoanOrder']['cost_fee'] == 0 ? '--' : sprintf("%.2f",$information['userLoanOrder']['cost_fee'] / 100)); ?></td>
                    <th class="td24">Source From</th>
                    <td class="mark"><?= Html::encode($information['userLoanOrder']->clientInfoLog['package_name'] ?? '--'); ?></td>
                </tr>

            </table>
        </td>
    </tr>
</table>