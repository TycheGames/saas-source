<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_input_overdue_day_out');
$this->showsubmenu(Yii::T('common', 'Overdue days out of the reminder rate (by amount)'), array(
    array('list', Url::toRoute('collection-statistics/input-overdue-day-out-amount'), 1),
    array('chart', Url::toRoute('collection-statistics/input-overdue-day-out-amount-chart'), 0),
));

?>
<style>
    .fff {background:#ffffff;}
</style>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
    <script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?= Yii::T('common', 'Default all');?>'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Add collection') ?><?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?=Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'New and old types (order)') ?>：<?php echo Html::dropDownList('user_type', Html::encode(Yii::$app->getRequest()->get('user_type', 0)), $user_type_map); ?>&nbsp;
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
packageName：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
    ArrayHelper::htmlEncode($packageNameList),['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Start every hour, update every 15 minutes') ?>
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <?php if($isShowOverApr):?>
            <th ><?php echo Yii::T('common', 'Due date') ?></th>
            <th ><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Overdue rate') ?></th>
            <?php endif;?>
            <th ><?php echo Yii::T('common', 'Add collection date') ?></th>
            <th ><?php echo Yii::T('common', 'New add collection orders') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Cumulative recall rate') ?></th>
            <th >S1(D1)</th>
            <th >S1(D1-D3)</th>
            <th >S1(D4-D7)</th>
            <th style="border-right:1px solid #A9A9A9;">S1</th>
            <th >S2(8-15)</th>
            <th >S1+S2(1-15)</th>
            <th >M1(16-30)</th>
            <th style="border-right:1px solid #A9A9A9;">M2(31-60)</th>
            <?php for ($day = 1; $day <= 30; $day++): ?>
                <th <?php if($day == 30):?>style="border-right:1px solid #A9A9A9;"<?php endif;?>>D<?=Html::encode($day) ?></th>
            <?php endfor;?>
            <?php foreach (LoanCollectionOrder::$level as $lv => $val):
                if($lv < LoanCollectionOrder::LEVEL_M3) continue;
                ?>
                <th ><?=Html::encode($val) ?></th>
            <?php endforeach;?>
        </tr>
        </thead>
        <thead class="total">
        <tr class="hover">
            <?php if($isShowOverApr):?>
            <td style='color:blue;font-weight:bold'>--</td>
            <td style='color:blue;font-weight:bold'><?php echo Html::encode(empty($totalData['expire_money_0']) ? '-' : sprintf("%0.2f", ($totalData['expire_money_0'] - $totalData['repay_zc_money_0']) / $totalData['expire_money_0'] * 100) . "%");?></td>
            <td style='color:blue;font-weight:bold;border-right:1px solid #A9A9A9;'><?php echo Html::encode(empty($totalData['expire_money_0']) ? '-' : sprintf("%0.2f", (($totalData['expire_money_0'] - $totalData['repay_money_0']) / $totalData['expire_money_0']) * 100) . "%"); ?></td>
            <?php endif;?>
            <td style='color:blue;font-weight:bold'>Total</td>
            <td style='color:blue;font-weight:bold;'><?php echo Html::encode(number_format($totalData['input_amount'] / 100,2)); ?></td>
            <td style='color:blue;font-weight:bold;border-right:1px solid #A9A9A9;'><?= Html::encode($totalData['input_amount'] == 0 || $totalData['overday_total_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday_total_amount']/$totalData['input_amount']*100).'%/'.number_format($totalData['overday_total_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday1_amount'] == 0 || $totalData['overday1_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday1_amount']/$totalInputData['overday1_amount']*100).'%/'.number_format($totalData['overday1_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday1_3_amount'] == 0 || $totalData['overday1_3_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday1_3_amount']/$totalInputData['overday1_3_amount']*100).'%/'.number_format($totalData['overday1_3_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday4_7_amount'] == 0 || $totalData['overday4_7_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday4_7_amount']/$totalInputData['overday4_7_amount']*100).'%/'.number_format($totalData['overday4_7_amount'] / 100,2)) ?></td>

            <td style='color:blue;font-weight:bold;border-right:1px solid #A9A9A9;'><?= Html::encode($totalInputData['overday1_7_amount'] == 0 || $totalData['overday1_7_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday1_7_amount']/$totalInputData['overday1_7_amount']*100).'%/'.number_format($totalData['overday1_7_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday8_15_amount'] == 0 || $totalData['overday8_15_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday8_15_amount']/$totalInputData['overday8_15_amount']*100).'%/'.number_format($totalData['overday8_15_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday1_15_amount'] == 0 || $totalData['overday1_15_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday1_15_amount']/$totalInputData['overday1_15_amount']*100).'%/'.number_format($totalData['overday1_15_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold'><?= Html::encode($totalInputData['overday16_30_amount'] == 0 || $totalData['overday16_30_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday16_30_amount']/$totalInputData['overday16_30_amount']*100).'%/'.number_format($totalData['overday16_30_amount'] / 100,2)) ?></td>
            <td style='color:blue;font-weight:bold;border-right:1px solid #A9A9A9;'><?= Html::encode($totalInputData['overlevel7_amount'] == 0 || $totalData['overday4_7_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overlevel7_amount']/$totalInputData['overlevel7_amount']*100).'%/'.number_format($totalData['overlevel7_amount'] / 100,2)) ?></td>

            <?php for ($day = 1; $day <= 30; $day++): ?>
                <td style='color:blue;font-weight:bold;<?php if($day == 30):?>border-right:1px solid #A9A9A9;<?php endif;?>'><?= Html::encode($totalInputData['overday'.$day.'_amount'] == 0 || $totalData['overday'.$day.'_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overday'.$day.'_amount']/$totalInputData['overday'.$day.'_amount']*100).'%/'.number_format($totalData['overday'.$day.'_amount'] / 100,2)) ?></td>
            <?php endfor;?>
            <?php foreach (LoanCollectionOrder::$level as $lv => $val):
                if($lv < LoanCollectionOrder::LEVEL_M3) continue;
                ?>
                <td style='color:blue;font-weight:bold;'><?= Html::encode($totalInputData['overlevel'.$lv.'_amount'] == 0 || $totalData['overlevel'.$lv.'_amount'] == 0 ? '-' : sprintf("%0.2f",$totalData['overlevel'.$lv.'_amount']/$totalInputData['overlevel'.$lv.'_amount']*100).'%/'.number_format($totalData['overlevel'.$lv.'_amount'] / 100,2)) ?></td>
            <?php endforeach;?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $value): ?>
            <tr class="hover" style="<?php
            $before = date('Y-m-d',strtotime($value['date']) - 86400);
            echo (date('w', strtotime($value['date'])) == 0 || date('w', strtotime($value['date'])) == 6)?'background:#deeffa':'';
            ?>">
                 <?php if($isShowOverApr):?>
                <td class="fff"><?php echo Html::encode($before); ?></td>
                <td class="fff"><?php echo Html::encode(empty($data[$before]['expire_money_0']) ? '-' : sprintf("%0.2f", ($data[$before]['expire_money_0'] - $data[$before]['repay_zc_money_0']) / $data[$before]['expire_money_0'] * 100) . "%");?></td>
                <td class="fff" style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(empty($data[$before]['expire_money_0']) ? '-' : sprintf("%0.2f", (($data[$before]['expire_money_0'] - $data[$before]['repay_money_0']) / $data[$before]['expire_money_0']) * 100) . "%"); ?></td>
                <?php endif;?>
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode(number_format($value['input_amount'] / 100,2)); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?= Html::encode($value['input_amount'] == 0 || $value['overday_total_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday_total_amount']/$value['input_amount']*100).'%/'.number_format($value['overday_total_amount'] / 100,2)) ?></td>
                <td ><?= Html::encode($value['input_amount'] == 0 || $value['overday1_amount'] == 0 ? '--' : sprintf("%0.2f",$value['overday1_amount']/$value['input_amount']*100).'%/'.number_format($value['overday1_amount'] / 100,2)) ?></td>
                <td ><?= Html::encode($value['input_amount'] == 0 || $value['overday1_3_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday1_3_amount']/$value['input_amount']*100).'%/'.number_format($value['overday1_3_amount'] / 100,2)) ?></td>
                <td ><?= Html::encode($value['input_amount'] == 0 || $value['overday4_7_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday4_7_amount']/$value['input_amount']*100).'%/'.number_format($value['overday4_7_amount'] / 100,2)) ?></td>

                <td style="border-right:1px solid #A9A9A9;"><?= Html::encode($value['input_amount'] == 0 || $value['overday1_7_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday1_7_amount']/$value['input_amount']*100).'%/'.number_format($value['overday1_7_amount'] / 100,2)) ?></td>
                <td ><?= Html::encode($value['input_amount'] == 0 || $value['overday8_15_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday8_15_amount']/$value['input_amount']*100).'%/'.number_format($value['overday8_15_amount'] / 100,2)) ?></td>
                <td style="color:red;font-weight:bold;"><?= Html::encode($value['input_amount'] == 0 || $value['overday1_15_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday1_15_amount']/$value['input_amount']*100).'%/'.number_format($value['overday1_15_amount'] / 100,2)) ?></td>
                <td ><?= Html::encode($value['input_amount'] == 0 || $value['overday16_30_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday16_30_amount']/$value['input_amount']*100).'%/'.number_format($value['overday16_30_amount'] / 100,2)) ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?= Html::encode($value['input_amount'] == 0 || $value['overlevel7_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overlevel7_amount']/$value['input_amount']*100).'%/'.number_format($value['overlevel7_amount'] / 100,2)) ?></td>

                <?php for ($day = 1; $day <= 30; $day++): ?>
                    <td <?php if($day == 30):?>style="border-right:1px solid #A9A9A9;"<?php endif;?>><?= Html::encode($value['input_amount'] == 0 || $value['overday'.$day.'_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overday'.$day.'_amount']/$value['input_amount']*100).'%/'.number_format($value['overday'.$day.'_amount'] / 100,2)) ?></td>
                <?php endfor;?>
                <?php foreach (LoanCollectionOrder::$level as $lv => $val):
                    if($lv < LoanCollectionOrder::LEVEL_M3) continue;
                    ?>
                    <td ><?= Html::encode($value['input_amount'] == 0 || $value['overlevel'.$lv.'_amount'] == 0 ? '-' : sprintf("%0.2f",$value['overlevel'.$lv.'_amount']/$value['input_amount']*100).'%/'.number_format($value['overlevel'.$lv.'_amount'] / 100,2)) ?></td>
                <?php endforeach;?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($list)): ?>
        <div class="no-result">No record</div>
    <?php endif; ?>
</form>
<br>
<p><?php echo Yii::T('common', 'Number of incoming reminders added: the number of orders entered corresponding to the reminder date') ?></p>
<p><?php echo Yii::T('common', 'Cumulative recall rate: the ratio of completed reminders among the number of orders entered on the reminder date / number of completed orders among the number of orders entered on the recall date') ?></p>
<p>D1:<?php echo Yii::T('common', 'Orders that are overdue for 1 day, the ratio of the number of orders that are reminded on the entry date and the reminder is completed when it is overdue by 1 day / The number of orders that are recalled on the entry date and the number of orders that are completed on the due date') ?></p>
<p>S1:<?php echo Yii::T('common', 'Orders with overdue number of 1-7 days, corresponding to the reminder date, the number of orders that are reminded between 1-7 days overdue, the ratio of completion of the reminder / the number of orders corresponding to the reminder date, Number of orders completed in days') ?></p>
<p>S2:<?php echo Yii::T('common', 'Orders overdue for 1-15 days') ?></p>
<p>M1:<?php echo Yii::T('common', 'Orders with 16-30 days overdue') ?></p>
<p>M2:<?php echo Yii::T('common', 'Orders with 31-60 days overdue') ?></p>
<p>M3:<?php echo Yii::T('common', 'Orders overdue for 61-90 days') ?></p>
<p>M3+:<?php echo Yii::T('common', 'Orders overdue for the day after 91, actual (91-180)') ?></p>
<br>