<?php

use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\helpers\CommonHelper;
use common\models\order\UserRepaymentLog;
use yii\helpers\Html;

$this->shownav('loanOrder', 'menu_repayment_log_list');
$this->showsubmenu(Yii::T('common', 'Repayment log'), array(
));
?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('user_id', '')); ?>" name="user_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'type') ?>：<?= yii\helpers\Html::dropDownList('type', Html::encode(Yii::$app->request->get('type', '')), CommonHelper::getListT(UserRepaymentLog::$typeMap), ['prompt' => Yii::T('common', 'All status')]); ?>&nbsp;
collector id：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collector_id', '')); ?>" name="collector_id" class="txt" style="width:120px;">&nbsp;
collector name：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collector_name', '')); ?>" name="collector_name" class="txt" style="width:120px;">&nbsp;
<?php if (!empty($isNotMerchantAdmin)): ?>
    <?php echo Yii::T('common', 'belongsToMerchants') ?>：<?= yii\helpers\Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId() ,['prompt' => 'all']); ?>&nbsp;
<?php endif; ?>
<?php echo Yii::T('common', 'Creation time') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('begintime', '')); ?>" name="begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('endtime', '')); ?>"  name="endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>ID</th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'Amount') ?></th>
                <th><?php echo Yii::T('common', 'type') ?></th>
                <th><?php echo Yii::T('common', 'collector id') ?></th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['id'], 'log')); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode(CommonHelper::CentsToUnit($value['amount'])); ?></td>
                    <td><?php echo Html::encode(UserRepaymentLog::$typeMap[$value['type']]); ?></td>
                    <td><?php echo Html::encode($value['collector_id']); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
