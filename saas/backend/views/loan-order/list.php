<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrder;
use common\models\product\ProductPeriodSetting;
use common\models\user\LoanPerson;
use common\helpers\CommonHelper;

$this->shownav('loanOrder', 'menu_loan_order_list');
$this->showsubmenu(Yii::T('common', 'loanList'), array(
    array('list', Url::toRoute('loan-order/list'), 1),
));
?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['loan-order/list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('uid', '')); ?>" name="uid" class="txt" />&nbsp;
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('name', '')); ?>" name="name" class="txt" />&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('id', '')); ?>" name="id" class="txt" />&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" />&nbsp;
    <br/><br/>
<?php echo Yii::T('common', 'status') ?>：<?= Html::dropDownList('status', Html::encode(\yii::$app->request->get('status', '')), UserLoanOrder::$order_status_map,['prompt' => 'all']); ?>&nbsp;
<?php echo Yii::T('common', 'Payment status') ?>：<?= Html::dropDownList('loan_status', Html::encode(\yii::$app->request->get('loan_status', '')), UserLoanOrder::$order_loan_status_map,['prompt' => 'all']); ?>&nbsp;
<?php echo Yii::T('common', 'sourceId') ?>：<?= Html::dropDownList('source_id', Html::encode(\yii::$app->request->get('source_id')), $package_setting ,['prompt' => 'all']); ?>&nbsp;
<?php echo Yii::T('common', 'Order APP') ?>：<?= Html::dropDownList('order_app', Html::encode(\yii::$app->request->get('order_app')), $package_name_list ,['prompt' => 'all']); ?>&nbsp;
<?php if (!empty($isNotMerchantAdmin)): ?>
    <?php echo Yii::T('common', 'belongsToMerchants') ?>：<?= Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId() ,['prompt' => 'all']); ?>&nbsp;
<?php endif; ?>
    <br/><br/>
<?php echo Yii::T('common', 'Old users') ?>：<?= Html::dropDownList('old_user', Html::encode(\yii::$app->request->get('old_user', '')), [0=>'all',1=>'yes',-1=>'no']); ?>&nbsp;
<?php echo Yii::T('common', 'application time') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('begintime', '')); ?>" name="begintime" class="txt" onfocus="WdatePicker({startDate:'<?=date('Y-m-d 00:00:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('endtime', '')); ?>" name="endtime" class="txt" onfocus="WdatePicker({startDate:'<?=date('Y-m-d 00:00:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'Lending time') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('begintime2', '')); ?>" name="begintime2" class="txt" onfocus="WdatePicker({startDate:'<?=date('Y-m-d 00:00:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('endtime2', '')); ?>" name="endtime2" class="txt" onfocus="WdatePicker({startDate:'<?=date('Y-m-d 00:00:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'Application Amount') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('amount_min', '')); ?>" name="amount_min" class="txt" placeholder="amount min" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('amount_max', '')); ?>" name="amount_max" class="txt" placeholder="amount max" />
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />&nbsp;
<?php if($isShow):?>
    <input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportData');return true;" class="btn" />
<?php endif;?>
<?php $form = ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'orderId') ?>/<?php echo Yii::T('common', 'userId') ?>/<?php echo Yii::T('common', 'name') ?></th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'sourceId') ?></th>
        <th><?php echo Yii::T('common', 'Order APP') ?></th>
        <th><?php echo Yii::T('common', 'Is an old user') ?></th>
        <th><?php echo Yii::T('common', 'Loan amount') ?>(Rs)</th>
        <th><?php echo Yii::T('common', 'Loan term') ?></th>
        <th><?php echo Yii::T('common', 'application time') ?></th>
        <th><?php echo Yii::T('common', 'Lending time') ?></th>
        <th><?php echo Yii::T('common', 'Repayment time') ?></th>
        <!--        <th>子类型</th>-->
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'Payment status') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($data_list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode(CommonHelper::idEncryption($value['id'], 'order')); ?>/<?= Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?>/<?=Html::encode($value['name']); ?></td>
            <td><div style="width: 100px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['phone']); ?></span></div></td>
            <th><?= Html::encode($package_setting[$value['source_id']] ?? '--'); ?></th>
            <td><?php echo Html::encode($value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name']); ?></td>
            <th><?= Html::encode(LoanPerson::$customer_type_list[$value['customer_type']] ?? '--'); ?></th>
            <th><?= Html::encode($value['amount'] == 0 ? '--' : sprintf("%0.2f",($value['amount'] + $value['interests'])/100)); ?></th>
            <th><?= Html::encode(($value['loan_term']*$value['periods']).ProductPeriodSetting::$loan_method_map[$value['loan_method']]); ?></th>
            <th><?= Html::encode(empty($value['order_time']) ? '--' : date('Y-m-d H:i:s',$value['order_time'])); ?></th>
            <th><?= Html::encode(empty($value['loan_time']) ? '--' : date('Y-m-d H:i:s',$value['loan_time'])); ?></th>
            <th><?= Html::encode(empty($value['closing_time'])?'--':date('Y-m-d H:i:s',$value['closing_time'])); ?></th>
            <th><?= Html::encode(isset($status_data[$value['id']]) ? $status_data[$value['id']] : ""); ?></th>
            <th><?= Html::encode(UserLoanOrder::$order_loan_status_map[$value['loan_status']] ?? '-'); ?></th>
            <th>
                <a href="<?= Url::to(['loan-order/detail', 'id' => CommonHelper::idEncryption($value['id'], 'order')]);?>">detail</a>
                <?php if($value['status'] == UserLoanOrder::STATUS_CHECK
                    && $value['audit_status'] == UserLoanOrder::AUDIT_STATUS_GET_ORDER):?>
                    <a onclick="javascript:if(!confirmMsg('are you sure get this order!')) return false;" href="<?= Url::to(['loan-order/to-my-review', 'id' => CommonHelper::idEncryption($value['id'], 'order')]);?>">toMyReview</a>
                <?php endif;?>
            </th>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($data_list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>
<script>
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>
