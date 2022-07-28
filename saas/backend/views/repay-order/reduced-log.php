<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserRepaymentReducedLog;
use common\helpers\CommonHelper;

$this->shownav('loanOrder', 'menu_repay_order_reduced_log');
$this->showsubmenu(Yii::T('common', 'Deduction list'), array(
    array('list', Url::toRoute('repay-order/reduced-log'),1),
));

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('user_id', '')); ?>" name="user_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" name="name" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php if (!empty($isNotMerchantAdmin)): ?>
    <?php echo Yii::T('common', 'belongsToMerchants') ?>：<?= \yii\helpers\Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId() ,['prompt' => 'all']); ?>&nbsp;
<?php endif; ?>
<br/>
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th><?php echo Yii::T('common', 'repayOrder') ?>ID</th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'Deduction amount') ?></th>
                <th><?php echo Yii::T('common', 'from') ?></th>
                <th><?php echo Yii::T('common', 'Operator') ?>Id</th>
                <th><?php echo Yii::T('common', 'Operator') ?>Name</th>
                <th><?php echo Yii::T('common', 'Remarks') ?></th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['repayment_id'], 'repayment')); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['reduction_money']/100)); ?></td>
                    <td><?php echo Html::encode(UserRepaymentReducedLog::$type[$value['from']]); ?></td>
                    <td><?php echo Html::encode($value['operator_id']); ?></td>
                    <td><?php echo Html::encode($value['operator_name']); ?></td>
                    <td><?php echo Html::encode($value['remark']); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
