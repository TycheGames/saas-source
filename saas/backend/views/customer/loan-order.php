<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrder;
use common\models\product\ProductPeriodSetting;
use common\models\user\LoanPerson;
use common\helpers\CommonHelper;

$this->shownav('customer', 'menu_customer_loan_order_search');
$this->showsubmenu(Yii::T('common', 'Loan order search'), array(
    array(Yii::T('common', 'search'), Url::toRoute('customer/loan-order'), 1),
));
?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['customer/loan-order']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('name', '')); ?>" name="name" class="txt" />&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" />&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />&nbsp;
<?php $form = ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'orderId') ?>/<?php echo Yii::T('common', 'userId') ?>/<?php echo Yii::T('common', 'name') ?></th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'Order APP') ?></th>
        <th><?php echo Yii::T('common', 'Loan Product') ?></th>
        <th><?php echo Yii::T('common', 'Is an old user') ?></th>
        <th><?php echo Yii::T('common', 'Loan amount') ?>(Rs)</th>
        <th><?php echo Yii::T('common', 'Loan Term') ?></th>
        <th><?php echo Yii::T('common', 'application time') ?></th>
        <th><?php echo Yii::T('common', 'Lending time') ?></th>
        <th><?php echo Yii::T('common', 'Repayment time') ?></th>
        <!--        <th>子类型</th>-->
        <th><?php echo Yii::T('common', 'order status') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($data_list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode(CommonHelper::idEncryption($value['id'], 'order')); ?>/<?= Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?>/<?=Html::encode($value['name']); ?></td>
            <th><?= Html::encode($value['phone']); ?></th>
            <td><?= Html::encode($value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name']); ?></td>
            <th><?= Html::encode($value['product_name']); ?></th>
            <th><?= Html::encode(LoanPerson::$customer_type_list[$value['customer_type']] ?? '--'); ?></th>
            <th><?= Html::encode(sprintf("%0.2f",($value['amount'] + $value['interests'])/100)); ?></th>
            <th><?= Html::encode(($value['loan_term']*$value['periods']).ProductPeriodSetting::$loan_method_map[$value['loan_method']]); ?></th>
            <th><?= Html::encode(empty($value['order_time']) ? '--' : date('Y-m-d H:i:s',$value['order_time'])); ?></th>
            <th><?= Html::encode(empty($value['loan_time']) ? '--' : date('Y-m-d H:i:s',$value['loan_time'])); ?></th>
            <th><?= Html::encode(empty($value['closing_time'])?'--':date('Y-m-d H:i:s',$value['closing_time'])); ?></th>
            <th><?= Html::encode(isset($status_data[$value['id']])?$status_data[$value['id']]:""); ?></th>
            <th>
                <a target="_blank" href="<?= Url::to(['loan-detail', 'id' => CommonHelper::idEncryption($value['id'], 'order')]);?>"><?php echo Yii::T('common', 'detail') ?></a>
            </th>
        </tr>
    <?php endforeach; ?>
</table>
<?= LinkPager::widget(['pagination' => $pages]); ?>