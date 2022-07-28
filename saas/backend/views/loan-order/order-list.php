<?php

use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\helpers\CommonHelper;
use common\models\order\UserLoanOrder;
use common\models\kudos\LoanKudosPerson;
use yii\helpers\Html;

$this->shownav('loanOrder', 'menu_pay_order_list');

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('user_id', '')); ?>" name="user_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'order status') ?>：<?= yii\helpers\Html::dropDownList('status', Html::encode(Yii::$app->request->get('status', '')), CommonHelper::getListT(UserLoanOrder::$order_status_map), ['prompt' => Yii::T('common', 'All status')]); ?>
 kudos <?php echo Yii::T('common', 'status') ?>：<?= yii\helpers\Html::dropDownList('kudos_account_status', Html::encode(Yii::$app->request->get('kudos_account_status', '')), CommonHelper::getListT(LoanKudosPerson::$account_status_map), ['prompt' => Yii::T('common', 'All status')]); ?>
 <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>kudos<?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'Amount paid') ?></th>
                <th><?php echo Yii::T('common', 'status') ?></th>
                <th><?php echo Yii::T('common', 'data validation') ?></th>
                <th><?php echo Yii::T('common', 'data validation time') ?></th>
                <th><?php echo Yii::T('common', 'Status check') ?></th>
                <th><?php echo Yii::T('common', 'Status check time') ?></th>
                <th>kudos <?php echo Yii::T('common', 'status') ?></th>
                <th><?php echo Yii::T('common', 'order status') ?></th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode($value['kudos_loan_id']); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode(CommonHelper::CentsToUnit($value['repayment_amt'])); ?></td>
                    <td><?php echo Html::encode($value['kudos_status']); ?></td>
                    <td><?php echo Html::encode($value['validation_status']); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['next_validation_time'])); ?></td>
                    <td><?php echo Html::encode($value['need_check_status']); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['next_check_status_time'])); ?></td>
                    <td><?php echo Html::encode($value['kudos_account_status']); ?></td>
                    <td><?php echo Html::encode(UserLoanOrder::$order_status_map[$value['status']]); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
