<?php
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use \common\models\order\FinancialLoanRecord;
use yii\helpers\Html;
use common\helpers\CommonHelper;

$this->shownav('customer', 'menu_customer_disburse_order_search');
$this->showsubmenu('Disburse Order search');
?>

<style>
    input.txt {width:120px;}
    .header th{text-align: center;}
    body > .modal { display: none;}
</style>

<?php $form = ActiveForm::begin(['id' => 'searchform', 'method' => "get",'action'=>['customer/disburse-order'],'options' => ['style' => 'margin-bottom:5px;']]); ?>
Name：<input type="text" value="<?= Html::encode(\yii::$app->request->get('name', '')); ?>" name="name" class="txt" />&nbsp;
Phone：<input type="text" value="<?= Html::encode(\yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" />&nbsp;
    <input type="submit" name="search_submit"value="search" class="btn" />&nbsp;
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" style="text-align: center;">
        <tr class="header">
            <th>Third order ID</th>
            <th>UTR</th>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Retry Count</th>
            <th>Retry Time</th>
            <th style="width: 50px;">Name</th>
            <th style="width: 50px;">Phone</th>
            <th>Loan Term</th>
            <th>Principal Amount</th>
            <th>Disburse Amount</th>
            <th style="width: 50px;">Bank</th>
            <th>Bank Account</th>
            <th style="width: 50px;">Status</th>
            <th>Apply Time</th>
            <th>Created Time</th>
            <th>Updated TIme</th>
            <th>Success Time</th>
        </tr>
        <?php foreach ($withdraws as $value): ?>
        <tr class="hover">
            <td><?= Html::encode($value['trade_no'] ?? ''); ?></td>
            <td><?= Html::encode($value['utr'] ?? '-'); ?></td>
            <td><?= Html::encode(CommonHelper::idEncryption($value['business_id'], 'order')); ?></td>
            <td><?= Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
            <td><?= Html::encode($value['retry_num']); ?></td>
            <td><?= Html::encode(empty($value['retry_time']) ? '-' : date('Y-m-d H:i:s', $value['retry_time'])); ?></td>
            <td><?= Html::encode($value['name']); ?></td>
            <td><?= Html::encode($value['phone']); ?></td>
            <td>
            <?= Html::encode(empty($value['loan_term'])?'':$value['loan_term'].' days')?>
            </td>
            <td><?= Html::encode(isset($value['amount']) ? sprintf('%.2f', $value['amount'] / 100) : 'order not exist'); ?></td>
            <td>
                <?= Html::encode(sprintf('%.2f',  ($value['money']) / 100)); ?>
            </td>
            <td><?= Html::encode($value['bank_name']);?></td>
            <td><?= Html::encode($value['account']);?></td>
            <td><?= Html::encode(FinancialLoanRecord::$ump_pay_status[$value['status']]); ?></td>
            <td><?= Html::encode($value['order_time'] ? date('Y-m-d H:i', $value['order_time']) : '-'); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i', $value['created_at'])); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i', $value['updated_at'])); ?></td>
            <td><?= Html::encode($value['success_time'] ? date('Y-m-d H:i:s', $value['success_time']) : '-'); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</form>