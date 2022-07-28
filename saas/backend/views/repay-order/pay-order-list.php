<?php

use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\helpers\CommonHelper;
use common\models\financial\FinancialPaymentOrder;
use yii\helpers\Html;

$this->shownav('loanOrder', 'menu_pay_order_list');

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Payment order number') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('pay_order_id', '')); ?>" name="pay_order_id" class="txt" style="width:200px;">&nbsp;
pay_payment_id：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('pay_payment_id', '')); ?>" name="pay_payment_id" class="txt" style="width:200px;">&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('user_id', '')); ?>" name="user_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'status') ?>：<?= yii\helpers\Html::dropDownList('status', Html::encode(Yii::$app->request->get('status', '')), FinancialPaymentOrder::$status_map, ['prompt' => Yii::T('common', 'All status')]); ?>
<?php echo Yii::T('common', 'Whether certification') ?>：<?= yii\helpers\Html::dropDownList('auth_status', Html::encode(Yii::$app->request->get('auth_status', '')), FinancialPaymentOrder::$auth_map, ['prompt' => Yii::T('common', 'All status')]); ?>
<br/>
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>ID</th>
                <th><?php echo Yii::T('common', 'Payment order number') ?></th>
                <th>pay_payment_id</th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'email') ?></th>
                <th><?php echo Yii::T('common', 'Amount') ?></th>
                <th><?php echo Yii::T('common', 'status') ?></th>
                <th><?php echo Yii::T('common', 'Whether certification') ?></th>
                <th><?php echo Yii::T('common', 'Success time') ?></th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode($value['id']); ?></td>
                    <td><?php echo Html::encode($value['pay_order_id']); ?></td>
                    <td><?php echo Html::encode($value['pay_payment_id']); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode($value['email_address']); ?></td>
                    <td><?php echo Html::encode(CommonHelper::CentsToUnit($value['amount'])); ?></td>
                    <td><?php echo Html::encode(FinancialPaymentOrder::$status_map[$value['status']]); ?></td>
                    <td><?php echo Html::encode(FinancialPaymentOrder::$auth_map[$value['auth_status']]); ?></td>
                    <td><?php echo Html::encode(empty($value['success_time']) ? '-' :date('Y-m-d H:i:s', $value['success_time'])); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
