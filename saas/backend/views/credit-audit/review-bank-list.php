<?php
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\product\ProductPeriodSetting;
use common\models\user\LoanPerson;
use common\helpers\CommonHelper;

$this->shownav('creditAudit', 'menu_credit_audit_review');
$this->showsubmenu('review bank list', array(
));
?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['credit-audit/review-list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('uid', '')); ?>" name="uid" class="txt" />&nbsp;
<?php echo Yii::T('common', 'username') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('name', '')); ?>" name="name" class="txt" />&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('id', '')); ?>" name="id" class="txt" />&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" />&nbsp;
<br />
<?php echo Yii::T('common', 'Is an old user') ?>：<?= Html::dropDownList('old_user', Html::encode(\yii::$app->request->get('old_user', '')), [0=>'all',1=>'yes',-1=>'no']); ?>&nbsp;
<?php echo Yii::T('common', 'application time') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('begintime', '')); ?>" name="begintime" class="txt" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('endtime', '')); ?>" name="endtime" class="txt" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'Application Amount') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('amount_min', '')); ?>" name="amount_min" class="txt" placeholder="<?php echo Yii::T('common', 'amount min') ?>" />&nbsp;
<?php echo Yii::T('common', 'to') ?>&nbsp;<input type="text" value="<?= Html::encode(\yii::$app->request->get('amount_max', '')); ?>" name="amount_max" class="txt" placeholder="<?php echo Yii::T('common', 'amount max') ?>" />
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'orderId') ?>/<?php echo Yii::T('common', 'userId') ?>/<?php echo Yii::T('common', 'username') ?></th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'Is an old user') ?></th>
        <th><?php echo Yii::T('common', 'Loan amount') ?>(Rs)</th>
        <th><?php echo Yii::T('common', 'Loan term') ?></th>
        <th><?php echo Yii::T('common', 'application time') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($data_list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode(CommonHelper::idEncryption($value['id'], 'order')); ?>/<?= Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?>/<?=Html::encode($value['name']); ?></td>
            <th><?= Html::encode($value['phone']); ?></th>
            <th><?= Html::encode(LoanPerson::$customer_type_list[$value['customer_type']] ?? '--'); ?></th>
            <th><?= Html::encode(sprintf("%0.2f",($value['amount'] + $value['interests'])/100)); ?></th>
            <th><?= Html::encode(($value['loan_term']*$value['periods']).ProductPeriodSetting::$loan_method_map[$value['loan_method']]); ?></th>
            <th><?= Html::encode(empty($value['order_time']) ? '--' : date('Y-m-d H:i:s',$value['order_time'])); ?></th>
            <th>
                <a href="<?= Url::to(['credit-audit/review-bank', 'id' => CommonHelper::idEncryption($value['id'], 'order')]);?>"><?php echo Yii::T('common', 'Audit') ?></a>
            </th>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($data_list)): ?>
    <div class="no-result"><?php echo Yii::T('common', '') ?><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>
