<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_out_outside_day_data');
$this->showsubmenu(Yii::T('common', 'Agency daily order statistics'), array(
    array('list', Url::toRoute('collection-statistics/outside-day-data'), 1)
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/outside-day-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?=Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency')));?>&nbsp
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th ><?php echo Yii::T('common', 'date') ?></th>
            <td> <?php echo Yii::T('common', 'agency') ?></td>
            <th ><?php echo Yii::T('common', 'Total number of orders completed') ?></th>
            <th ><?php echo Yii::T('common', 'Accumulative total amount due for order completion') ?></th>
            <th ><?php echo Yii::T('common', 'Total number of orders in progress') ?></th>
            <th ><?php echo Yii::T('common', 'The total amount due for the order currently in progress due') ?></th>
            <th ><?php echo Yii::T('common', 'The total number of orders distributed on the day') ?></th>
            <th ><?php echo Yii::T('common', 'The total number of orders completed on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Completion rate of the day (by order)') ?></th>
            <th ><?php echo Yii::T('common', 'The total amount due on the day the distribution order is due') ?></th>
            <th ><?php echo Yii::T('common', 'The total amount due on the day when the order is due') ?></th>
            <th ><?php echo Yii::T('common', 'Completion rate of the day (according to the amount due)') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $value): ?>
            <tr class="hover" style="<?php
            $before = date('Y-m-d',strtotime($value['date']));
            echo (date('w', strtotime($value['date'])) == 0 || date('w', strtotime($value['date'])) == 6)?'background:#deeffa':'';
            ?>">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($outsideInfo[$value['outside']] ?? '--'); ?></td>
                <td ><?php echo Html::encode($value['total_finish_num']); ?></td>
                <td ><?php echo Html::encode(number_format($value['total_finish_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['current_progress_num']); ?></td>
                <td ><?php echo Html::encode(number_format($value['current_progress_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['today_dispatch_num']); ?></td>
                <td ><?php echo Html::encode($value['today_finish_num']); ?></td>
                <td ><?php echo Html::encode($value['today_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['today_finish_num']/$value['today_dispatch_num']*100).'%'); ?></td>
                <td ><?php echo Html::encode(number_format($value['today_dispatch_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode(number_format($value['today_finish_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['today_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['today_finish_amount']/$value['today_dispatch_amount']*100).'%'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($list)): ?>
        <div class="no-result">No record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
