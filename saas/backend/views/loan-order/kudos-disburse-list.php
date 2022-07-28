<?php

use Carbon\Carbon;
use common\helpers\CommonHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrder;
use common\models\product\ProductPeriodSetting;
use common\models\user\LoanPerson;

/**
 * @var array $data_list
 * @var array $pages
 */

$this->shownav('loanOrder', 'menu_loan_order_list');
$this->showsubmenu('Kudos Disburse', array(
    array('list', Url::toRoute('loan-order/list'), 1),
));
?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['loan-order/list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php echo Yii::T('common', 'Due date') ?>：
    <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('begintime', '')); ?>" name="begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
    <?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('endtime', '')); ?>"  name="endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />&nbsp;
<?php $form = ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'Tranche') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'Order number') ?></th>
        <th><?php echo Yii::T('common', 'Payment amount') ?>(Rs.)</th>
        <th><?php echo Yii::T('common', 'date') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($data_list as $value): ?>
        <tr class="hover">
            <th><?= Html::encode($value['tranche_id']); ?></th>
            <th><?= Html::encode($value['kudos_status'] == 0 ? '未确认' : ($value['kudos_status'] == 1 ? '已确认' : '可打款')); ?></th>
            <th><?= Html::encode($value['tranche_num']); ?></th>
            <th><?= Html::encode(CommonHelper::CentsToUnit($value['tranche_amt'])); ?></th>
            <th><?= Html::encode(Carbon::createFromTimestamp($value['created_at'])->toDateString()); ?></th>
            <th>
                <?php if($value['kudos_status'] == 1):?>
                    <a href="<?= Url::to(['loan-order/kudos-disburse-confirm', 'id' => $value['id']]);?>">Disburse</a>
                <?php endif;?>
            </th>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($data_list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>