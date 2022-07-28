<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_collection_order_statistics');
$this->showsubmenu('total input and out', array(
    array('list', Url::toRoute('collection-statistics/order-statistics'), 1)
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/order-statistics']), 'options' => ['style' => 'margin-top:5px;']]); ?>
Date：<input type="text" value="<?= Html::encode($startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?=Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th >Date</th>
            <th >Input Count</th>
            <th >Out Count</th>
            <th >Out/Input rate</th>
            <th >Update Time</th>
        </tr>
        </thead>
        <thead class="total">
        <tr class="hover">
            <td style='color:blue;font-weight:bold'>Total</td>
            <td style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['loan_num']); ?></td>
            <td style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['repay_num']); ?></td>
            <td style='color:blue;font-weight:bold'><?php if($totalData['loan_num'] > 0){echo Html::encode(sprintf("%0.2f",$totalData['repay_num']/$totalData['loan_num']*100));}else{echo '0';}; ?>%</td>
            <td style='color:blue;font-weight:bold'>--</td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['loan_num']); ?></td>
                <td ><?php echo Html::encode($value['repay_num']); ?></td>
                <td ><?php if($value['loan_num'] > 0){echo Html::encode(sprintf("%0.2f",$value['repay_num']/$value['loan_num']*100));}else{echo '0';}; ?>%</td>
                <td ><?php echo date('Y-m-d H:i:s',$value['updated_at']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($list)): ?>
        <div class="no-result">No record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<br>
<p><?php echo Yii::T('common', 'Update Time: 40 minutes per hour') ?></p>
<p>Input Count: <?php echo Yii::T('common', 'The number of new collection orders per day, that is, the total number of incoming orders') ?></p>
<p>Out Count: <?php echo Yii::T('common', 'The number of successful collection orders per day') ?></p>