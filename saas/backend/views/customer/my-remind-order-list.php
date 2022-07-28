<?php
use yii\helpers\Url;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrderRepayment;
use backend\models\remind\RemindOrder;
use backend\components\widgets\LinkPager;
use yii\helpers\Html;
use common\models\order\UserLoanOrder;
use common\models\user\LoanPerson;
use common\helpers\CommonHelper;

$this->shownav('customer', 'menu_my_remind_order_list');
$this->showsubmenu(Yii::T('common', 'My remind list'), array(

));
?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin([
    'id' => 'search_form',
    'method' => "get",
    'action' => Url::to(['customer/my-remind-order-list']),
    'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],
]); ?>
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Remind status') ?>：<?= Html::dropDownList('remind_status', Html::encode(\yii::$app->request->get('remind_status', '')), RemindOrder::$status_map,['prompt' => Yii::T('common', 'all')]); ?>&nbsp;
<?php echo Yii::T('common', 'Repayment status') ?>：<?= Html::dropDownList('repayment_status', Html::encode(\yii::$app->request->get('repayment_status', '')), UserLoanOrderRepayment::$repayment_status_map,['prompt' => Yii::T('common', 'all')]); ?>&nbsp;
<?php echo Yii::T('common', 'Is the first order') ?>：<?= Html::dropDownList('is_first', Html::encode(\yii::$app->request->get('is_first', '')), UserLoanOrder::$first_loan_map,['prompt' => Yii::T('common', 'all')]); ?>&nbsp;
<?php echo Yii::T('common', 'User type') ?>：<?= Html::dropDownList('customer_type', Html::encode(\yii::$app->request->get('customer_type', '')), LoanPerson::$customer_type_list,['prompt' => Yii::T('common', 'all')]); ?>&nbsp;
<?php echo Yii::T('common', 'reach') ?>/<?php echo Yii::T('common', 'no reach') ?>：<?= Html::dropDownList('remind_reach', Html::encode(\yii::$app->request->get('remind_reach', '')), \common\helpers\CommonHelper::getListT(RemindOrder::$remind_reach_map),['prompt' => Yii::T('common', Yii::T('common', 'all'))]); ?>&nbsp;
<?php echo Yii::T('common', 'Remind Return') ?>：<?= Html::dropDownList('remind_return', Html::encode(\yii::$app->request->get('remind_return', '')), \common\helpers\CommonHelper::getListT(RemindOrder::$remind_return_map_all),['prompt' => Yii::T('common', Yii::T('common', 'all'))]); ?>&nbsp;
<br/>
<?php echo Yii::T('common', 'Should Repay Date') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('begintime_plan', '')); ?>" name="begintime_plan" class="txt" onfocus="WdatePicker({lang:'en',startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('endtime_plan', '')); ?>" name="endtime_plan" class="txt" onfocus="WdatePicker({lang:'en',startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<input type="submit" name="search_submit" value="search" class="btn" />
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'repay Order NO') ?></th>
        <th><?php echo Yii::T('common', 'orderId') ?></th>
        <th><?php echo Yii::T('common', 'Loan Product') ?></th>
        <th><?php echo Yii::T('common', 'From APP') ?></th>
        <th><?php echo Yii::T('common', 'userId') ?></th>
        <th><?php echo Yii::T('common', 'name') ?></th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'Is the first order') ?></th>
        <th><?php echo Yii::T('common', 'Is an old user') ?></th>
        <th><?php echo Yii::T('common', 'Repay Count') ?></th>
        <th><?php echo Yii::T('common', 'Principal') ?></th>
        <th><?php echo Yii::T('common', 'Interest') ?></th>
        <th><?php echo Yii::T('common', 'Cost Fee') ?></th>
        <th><?php echo Yii::T('common', 'Completed Money') ?></th>
        <th><?php echo Yii::T('common', 'Should Repay Date') ?></th>
        <th><?php echo Yii::T('common', 'Dispatch time') ?></th>
        <th><?php echo Yii::T('common', 'Completed Date') ?></th>
        <th><?php echo Yii::T('common', 'Remind Count') ?></th>
        <th><?php echo Yii::T('common', 'Repayment status') ?></th>
        <th><?php echo Yii::T('common', 'Remind status') ?></th>
        <th><?php echo Yii::T('common', 'reach') ?>/<?php echo Yii::T('common', 'no reach') ?></th>
        <th><?php echo Yii::T('common', 'Remind Return') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td><?php echo $value['id']; ?></td>
            <td>
                <?= Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?>
            </td>
            <td><?php echo Html::encode($value['package_name']); ?></td>
            <td><?php echo Html::encode($value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name']); ?></td>
            <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
            <td><?php echo Html::encode($value['name']); ?></td>
            <td><div style="width: 100px"><span id="phone_mask_<?=Html::encode($value['id'])?>" class="phone_mask"><a href="javascript:;" onclick="callPhone(<?php echo Html::encode($value['phone']); ?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?></a><img onclick="showPhone($(this),<?=Html::encode($value['id'])?>)" src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=Html::encode($value['id'])?>" class="phone_show"><a href="javascript:;" onclick="callPhone(<?php echo Html::encode($value['phone']); ?>)"><?php echo Html::encode($value['phone']); ?></a></span></div></td>
            <td><?php echo Html::encode(UserLoanOrder::$first_loan_map[$value['is_first']] ?? '-'); ?></td>
            <td><?php echo Html::encode(LoanPerson::$customer_type_list[$value['customer_type']] ?? '-'); ?></td>
            <td><?php echo Html::encode($repayCount[$value['user_id']]['count'] ?? 0); ?></td>
            <td><?php echo Html::encode(sprintf("%0.2f",$value['principal']/100)); ?></td>
            <td><?php echo Html::encode(sprintf("%0.2f",$value['interests']/100)); ?></td>
            <td><?php echo Html::encode(sprintf("%0.2f",$value['cost_fee']/100)); ?></td>
            <td><?php echo Html::encode(sprintf("%0.2f",$value['true_total_money']/100)); ?></td>
            <td><?php echo Html::encode(date('Y-m-d',$value['plan_repayment_time'])); ?></td>
            <td><?php echo Html::encode($value['dispatch_time'] ? date('Y-m-d H:i',$value['dispatch_time']) : '-'); ?></td>
            <td><?php echo Html::encode($value['closing_time'] ? date('Y-m-d',$value['closing_time']) : '-'); ?></td>
            <td><?php echo Html::encode($value['remind_count']); ?></td>
            <td><?php echo Html::encode(isset(UserLoanOrderRepayment::$repayment_status_map[$value['status']])?UserLoanOrderRepayment::$repayment_status_map[$value['status']]:""); ?></td>
            <td style="color: <?php if($value['remind_status'] == RemindOrder::STATUS_REMINDED){echo 'green';}else{echo 'red';};?>"><?php echo Html::encode(RemindOrder::$status_map[$value['remind_status']]); ?></td>
            <td><?php echo  $value['remind_return'] > 0 ? 'Reach' : ($value['remind_return'] < 0 ? 'No Reach' : '-'); ?></td>
            <td><?php echo Html::encode((RemindOrder::$remind_return_map_all[$value['remind_return']] ?? '-') . ($value['payment_after_days'] > 0 ? '('.$value['payment_after_days'].')': '')) ; ?></td>
            <td>
                <a href="<?= Url::to(['customer/remind-detail', 'remind_id' => $value['remind_id']]);?>"><?php echo Yii::T('common', 'remind') ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?= LinkPager::widget(['pagination' => $pages]); ?>

<script>
    function callPhone(phone) {
        var data = {};
        data.phone = phone;
        var nx_phone = "<?= $nx_phone;?>";
        if (nx_phone == 0) {
            return false;
        }
        $.ajax({
            url: "<?= Url::toRoute(['customer/call-phone', 't' => time()]); ?>",
            type: 'get',
            dataType: 'json',
            data: data,
            async: false,
            success: function (data) {
                if (data.code == 0) {
                    window.location.href = "sip:" + phone + "," + data.orderid;
                } else {
                    alert(data.message);
                }
            },
            error: function () {
                alert('Please log in nx phone');
            }
        });
    }

    function showPhone(obj,id) {
        $('#phone_show_'+ id).show();
        $('#phone_mask_'+ id).hide();
        obj.hide();

    }
</script>
