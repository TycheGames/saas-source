<?php

use yii\widgets\ActiveForm;
use backend\models\Merchant;
use yii\helpers\Html;
/**
 * @var backend\components\View $this
 */
$this->showsubmenu(Yii::T('common', 'Overview of real-time amount recovery'), array(
));
?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="JavaScript">
    $(function () {
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<?php
$form = ActiveForm::begin([
    'id' => 'search_form',
    'method'=>'get',
    'action' => ['data-stats/amount-recovery-overview'],
    'options' => ['style' => 'margin-top:5px;'],
]);
?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('endTime')) ?>" name="endTime"
          onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')),
        Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<input type="submit" name="search_submit" value="search" class="btn">&nbsp
<?php ActiveForm::end() ;?>
<div id="tb_data">

    <table class="tb tb2 fixpadding" id="tb">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'management') ?></th>
            <th><?php echo Yii::T('common', 'Total principal and interest to be received') ?></th>
            <th><?php echo Yii::T('common', 'Principal and interest to be received (overdue)') ?></th>
            <th><?php echo Yii::T('common', 'Principal and interest to be received (not overdue)') ?></th>
            <th><?php echo Yii::T('common', 'Late fees to be received') ?></th>
            <th><?php echo Yii::T('common', 'Late fees received') ?></th>
            <th><?php echo Yii::T('common', 'Principal and interest received') ?></th>
        </tr>
        </thead>
        <thead class="total">
            <tr>
                <td style='color:blue;font-weight:bold'> <?php echo Yii::T('common', 'summary') ?> </td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['wait_finish_total_principal_and_interest'] ?? 0) / 100)?></td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['wait_finish_principal_and_interest_expire'] ?? 0) / 100)?></td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['wait_finish_principal_and_interest_before_expire'] ?? 0) / 100)?></td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['wait_finish_overdue_fee'] ?? 0) / 100)?></td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['finish_overdue_fee'] ?? 0) / 100)?></td>
                <td style='color:blue;font-weight:bold'><?= number_format(($totalData['finish_principal_and_interest'] ?? 0) / 100)?></td>
            </tr>
        </thead>
        <!--  显示每日流失情况数据   -->
        <?php foreach($data as $fundId => $rows):?>
            <tr class="hover">
                <td class="td25"><?php echo Html::encode($loanFundList[$fundId] ?? '--')?></td>
                <td class="td25"><?=number_format(($rows['wait_finish_total_principal_and_interest'] ?? 0) / 100)?></td>
                <td class="td25"><?=number_format(($rows['wait_finish_principal_and_interest_expire'] ?? 0) / 100)?></td>
                <td class="td25"><?=number_format(($rows['wait_finish_principal_and_interest_before_expire'] ?? 0) / 100)?></td>
                <td class="td25"><?=number_format(($rows['wait_finish_overdue_fee'] ?? 0) / 100)?></td>
                <td class="td25"><?=number_format(($rows['finish_overdue_fee'] ?? 0) / 100)?></td>
                <td class="td25"><?=number_format(($rows['finish_principal_and_interest'] ?? 0) / 100)?></td>
            </tr>
        <?php endforeach; ?>

    </table>
</div>







