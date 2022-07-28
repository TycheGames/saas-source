<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrderRepayment;
use common\helpers\CommonHelper;

$this->shownav('customer', 'menu_customer_repay_order_search');
$this->showsubmenu(Yii::T('common', 'Repay order search'), array(
    array('search', Url::toRoute('customer/repay-order'),1),
));

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" name="name" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
Loan ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_uuid', '')); ?>" name="order_uuid" class="txt" style="width:200px;">&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th><?php echo Yii::T('common', 'repayOrder') ?></th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'Principal') ?></th>
                <th><?php echo Yii::T('common', 'Interest') ?></th>
                <th><?php echo Yii::T('common', 'Overdue Fee') ?></th>
                <th><?php echo Yii::T('common', 'Cost Fee') ?></th>
                <th><?php echo Yii::T('common', 'Completed Money') ?></th>
                <th><?php echo Yii::T('common', 'Loan date') ?></th>
                <th><?php echo Yii::T('common', 'Should Repay Date') ?></th>
                <th><?php echo Yii::T('common', 'Completed Date') ?></th>
                <th><?php echo Yii::T('common', 'Is Overdue') ?></th>
                <th><?php echo Yii::T('common', 'Overdue days') ?></th>
                <th><?php echo Yii::T('common', 'Repayment status') ?></th>
                <th><?php echo Yii::T('common', 'operation') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo $value['id']; ?></td>
                    <td>
                        <?= Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?>
                    </td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['principal']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['interests']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['overdue_fee']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['cost_fee']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['true_total_money']/100)); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d',$value['loan_time'])); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d',$value['plan_repayment_time'])); ?></td>
                    <td><?php echo Html::encode($value['closing_time'] ? date('Y-m-d',$value['closing_time']) : '-'); ?></td>
                    <td><?php echo Html::encode($value['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_YES ? "YES" : "NO"); ?></td>
                    <td><?php echo Html::encode($value['overdue_day']); ?></td>
                    <td><?php echo Html::encode(isset(UserLoanOrderRepayment::$repayment_status_map[$value['status']])?UserLoanOrderRepayment::$repayment_status_map[$value['status']]:""); ?></td>
                    <td>
                        <a target="_blank" href="<?php echo Url::to(['repay-detail', 'id' => $value['id']]);?>">Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
