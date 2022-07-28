<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_dispatch_outside_finish');
$this->showsubmenu(Yii::T('common', 'Agency daily order statistics'), array(
    array('list', Url::toRoute('collection-statistics/dispatch-outside-finish'), 1)
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?=Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency')));?>&nbsp;
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th ><?php echo Yii::T('common', 'Distribution date') ?></th>
            <th> <?php echo Yii::T('common', 'agency') ?></th>
            <th ><?php echo Yii::T('common', 'Total number of dispatches / amount') ?></th>
            <th ><?php echo Yii::T('common', 'Total completed orders / amount') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Total recall rate (by order / by amount)') ?></th>
            <th >S1(1D)<?php echo Yii::T('common', 'Distribute') ?></th>
            <th >S1(1D)<?php echo Yii::T('common', 'complete') ?></th>
            <th style="border-right:1px solid #A9A9A9;">S1(1D)<?php echo Yii::T('common', 'Recall rate') ?></th>
            <th >S1(1D-3D)<?php echo Yii::T('common', 'Distribute') ?></th>
            <th >S1(1D-3D)<?php echo Yii::T('common', 'complete') ?></th>
            <th style="border-right:1px solid #A9A9A9;">S1(1D-3D)<?php echo Yii::T('common', 'Recall rate') ?></th>
            <th >S1(1D-5D)<?php echo Yii::T('common', 'Distribute') ?></th>
            <th >S1(1D-5D)<?php echo Yii::T('common', 'complete') ?></th>
            <th style="border-right:1px solid #A9A9A9;">S1(1D-5D)<?php echo Yii::T('common', 'Recall rate') ?></th>
            <?php foreach (LoanCollectionOrder::$level as $lv => $val): ?>
                <th>
                    <?=Html::encode($val.'派分') ?>
                </th>
                <th>
                    <?=Html::encode($val.'完成') ?>
                </th>
                <th style="border-right:1px solid #A9A9A9;">
                    <?=Html::encode($val.'催回率') ?>
                </th>
            <?php endforeach;?>
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
                <td ><?php echo Html::encode($value['total_dispatch_num'] .'/'. number_format($value['total_dispatch_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['total_repay_num'] .'/'. number_format($value['total_repay_amount'] / 100,2)); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(($value['total_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['total_repay_num']/$value['total_dispatch_num']*100).'%')
                        .'/'. ($value['total_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['total_repay_amount']/$value['total_dispatch_amount']*100).'%')); ?></td>

                <td ><?php echo Html::encode($value['overday1_dispatch_num'] .'/'. number_format($value['overday1_dispatch_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['overday1_repay_num'] .'/'. number_format($value['overday1_repay_amount'] / 100,2)); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(($value['overday1_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['overday1_repay_num']/$value['overday1_dispatch_num']*100).'%')
                        .'/'. ($value['overday1_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['overday1_repay_amount']/$value['overday1_dispatch_amount']*100).'%')); ?></td>

                <td ><?php echo Html::encode($value['overday1_3_dispatch_num'] .'/'. number_format($value['overday1_3_dispatch_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['overday1_3_repay_num'] .'/'. number_format($value['overday1_3_repay_amount'] / 100,2)); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(($value['overday1_3_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['overday1_3_repay_num']/$value['overday1_3_dispatch_num']*100).'%')
                        .'/'. ($value['overday1_3_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['overday1_3_repay_amount']/$value['overday1_3_dispatch_amount']*100).'%')); ?></td>

                <td ><?php echo Html::encode($value['overday1_5_dispatch_num'] .'/'. number_format($value['overday1_5_dispatch_amount'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['overday1_5_repay_num'] .'/'. number_format($value['overday1_5_repay_amount'] / 100,2)); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(($value['overday1_5_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['overday1_5_repay_num']/$value['overday1_5_dispatch_num']*100).'%')
                        .'/'. ($value['overday1_5_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['overday1_5_repay_amount']/$value['overday1_5_dispatch_amount']*100).'%')); ?></td>


                <?php foreach (LoanCollectionOrder::$level as $lv => $val): ?>
                    <td>
                        <?php echo Html::encode($value['overlevel'.$lv.'_dispatch_num'] .'/'. number_format($value['overlevel'.$lv.'_dispatch_amount'] / 100,2)); ?>
                    </td>
                    <td>
                        <?php echo Html::encode($value['overlevel'.$lv.'_repay_num'] .'/'. number_format($value['overlevel'.$lv.'_repay_amount'] / 100,2)); ?>
                    </td>
                    <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode(($value['overlevel'.$lv.'_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['overlevel'.$lv.'_repay_num']/$value['overlevel'.$lv.'_dispatch_num']*100).'%')
                            .'/'. ($value['overlevel'.$lv.'_dispatch_amount'] == 0 ? '--' : sprintf("%.2f", $value['overlevel'.$lv.'_repay_amount']/$value['overlevel'.$lv.'_dispatch_amount']*100).'%')); ?>
                    </td>
                <?php endforeach;?>
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
<p><?php echo Yii::T('common', 'Note: The number of completed orders on the dispatch day is the number of orders completed in the number of dispatched orders on the day. The historical dispatch order will be fixed, but the completed order number will be updated, and the script will be run once every half an hour.') ?></p>
<p><?php echo Yii::T('common', 'Amount: The amount due on the due date of the order') ?></p>
<p><?php echo Yii::T('common', 'Recall rate: per order / per amount') ?></p>
