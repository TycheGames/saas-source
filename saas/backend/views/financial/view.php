
<?php
use yii\helpers\Html;
?>
<style>
    .person {
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
    }
    .table {
        max-width: 100%;
        width: 100%;
        border:1px solid #ddd;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
    .mark {
        font-weight: bold;
        /*background-color:indianred;*/
        color:red;
    }

    .hide {
        display: none;
    }
</style>


<table class="tb tb2 fixpadding" id="creditreport">
    <tr><th class="partition" colspan="10">打款信息</th></tr>
    <tr>
        <th width="110px;" class="person">收款人信息</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th class="td24">收款人：</th>
                    <td ><?= Html::encode($data['name']); ?></td>
                    <th class="td24">账号</th>
                    <td><?= Html::encode($data['account']); ?></td>
                </tr>
                <tr>
                    <th>ifsc</th>
                    <td><?= Html::encode($data['ifsc']); ?></td>
                    <th class="td24">银行</th>
                    <td><?= Html::encode($data['bank_name']); ?></td>
                </tr>
                <tr>
                    <th class="td24">用户ID</th>
                    <td><?= Html::encode($data['user_id']); ?></td>
                    <th class="td24">手机号</th>
                    <td><?= Html::encode($data['phone']); ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th width="110px;" class="person">订单信息</th>
        <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th class="td24">支付id：</th>
                    <td ><?= Html::encode($data['id']); ?></td>
                    <th class="td24">借款订单id：</th>
                    <td ><?= Html::encode($data['order_id']); ?></td>
                </tr>
                <tr>
                    <th class="td24">支付订单号：</th>
                    <td ><?= Html::encode($data['uuid']); ?></td>
                    <th class="td24">三方订单号：</th>
                    <td ><?= Html::encode($data['trade_no']); ?></td>
                </tr>
                <tr>
                    <th class="td24">借款金额：</th>
                    <td ><?= Html::encode($data['apply_amount']); ?></td>
                    <th class="td24">打款金额：</th>
                    <td ><?= Html::encode($data['loan_amount']); ?></td>
                </tr>
                <tr>
                    <th class="td24">打款状态：</th>
                    <td ><?= Html::encode($data['status']); ?></td>

                </tr>
                <tr>
                    <th>支付通道</th>
                    <td><?= Html::encode($data['service_type']);?></td>
                    <th class="td24">utr</th>
                    <td><?= Html::encode($data['utr']); ?></td>
                </tr>
                <tr>
                    <th>下单时间</th>
                    <td><?= Html::encode($data['order_time']); ?></td>
                    <th class="td24">放款时间</th>
                    <td><?= Html::encode($data['success_time']); ?></td>
                </tr>
                <tr>
                    <th>创建时间</th>
                    <td><?= Html::encode($data['created_at']); ?></td>
                    <th class="td24">更新时间</th>
                    <td><?= Html::encode($data['updated_at']); ?></td>
                </tr>
                <tr>
                    <th>首单</th>
                    <td><?= Html::encode($data['is_first']); ?></td>
                    <th>支付账号</th>
                    <td><?= Html::encode($data['payout_account']);?></td>
                </tr>
                <tr>
                    <th>重试次数</th>
                    <td><?= Html::encode($data['retry_num']); ?></td>
                    <th class="td24">重试时间</th>
                    <td><?= Html::encode($data['retry_time']); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>


