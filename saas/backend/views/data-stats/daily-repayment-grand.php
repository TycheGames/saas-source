<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\Merchant;
use yii\helpers\ArrayHelper;

$this->showsubmenu(Yii::T('common', 'Daily cumulative repayment data'));
/**
 * @var backend\components\View $this
 */
?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.all-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
    });
</script>
<title><?=Yii::T('common', 'Daily cumulative repayment data')?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-repayment-grand']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
        Merchant::getMerchantId(false),['class' => 'form-control all-select', 'multiple' => 'multiple']); ?>&nbsp;
<?php endif;?>
package name：<?= Html::dropDownList('package_name', Html::encode(Yii::$app->getRequest()->get('package_name', '')), $packageNameList,['prompt' => '--all--']);?>&nbsp;
<?=Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('s_date')) ? date("Y-m-d", time()-7*86400) : Yii::$app->request->get('s_date')); ?>"  name="s_date" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?=Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('e_date')) ? date("Y-m-d") : Yii::$app->request->get('e_date')); ?>"  name="e_date" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?=Yii::T('common', 'Overdue days') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:100px;">&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('export');return true;" class="btn" />
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?=Yii::T('common', 'date') ?></th>
            <th><?=Yii::T('common', 'Total repayment orders') ?>/<?=Yii::T('common', 'Total repayment amount') ?></th>
            <th><?=Yii::T('common', 'Delay orders') ?>/<?=Yii::T('common', 'Delay amount') ?></th>
            <th><?=Yii::T('common', 'Extension number') ?>/<?=Yii::T('common', 'Extension money') ?></th>
            <th><?=Yii::T('common', 'overdue') ?>16-30<?=Yii::T('common', 'Total repayment orders') ?>/<?=Yii::T('common', 'Total repayment amount') ?></th>
            <th><?=Yii::T('common', 'overdue') ?>31-60<?=Yii::T('common', 'Total repayment orders') ?>/<?=Yii::T('common', 'Total repayment amount') ?></th>
            <th><?=Yii::T('common', 'overdue') ?>61-90<?=Yii::T('common', 'Total repayment orders') ?>/<?=Yii::T('common', 'Total repayment amount') ?></th>
            <th><?=Yii::T('common', 'overdue') ?>91-<?=Yii::T('common', 'Total repayment orders') ?>/<?=Yii::T('common', 'Total repayment amount') ?></th>
        </tr>
        <tr style='color:blue;'>
            <th>total</th>
            <th><?php echo Html::encode(($totalInfo['all_repay_order_num'] ?? 0).'/'.number_format(($totalInfo['all_repay_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['delay_repay_order_num'] ?? 0).'/'.number_format(($totalInfo['delay_repay_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['extend_order_num'] ?? 0).'/'.number_format(($totalInfo['extend_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['16_30repay_order_num'] ?? 0).'/'.number_format(($totalInfo['16_30repay_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['31_60repay_order_num'] ?? 0).'/'.number_format(($totalInfo['31_60repay_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['61_90repay_order_num'] ?? 0).'/'.number_format(($totalInfo['61_90repay_amount'] ?? 0)/100)); ?></th>
            <th><?php echo Html::encode(($totalInfo['91_repay_order_num'] ?? 0).'/'.number_format(($totalInfo['91_repay_amount'] ?? 0)/100)); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($info as $value): ?>
        <tr class="hover">
            <td><?php echo $value['date']; ?></td>
            <td><?php echo $value['all_repay_order_num'].'/'.number_format($value['all_repay_amount']/100); ?></td>
            <td><?php echo $value['delay_repay_order_num'].'/'.number_format($value['delay_repay_amount']/100); ?></td>
            <td><?php echo $value['extend_order_num'].'/'.number_format($value['extend_amount']/100); ?></td>
            <td><?php echo Html::encode($value['16_30repay_order_num'].'/'.number_format($value['16_30repay_amount']/100)); ?></td>
            <td><?php echo Html::encode($value['31_60repay_order_num'].'/'.number_format($value['31_60repay_amount']/100)); ?></td>
            <td><?php echo Html::encode($value['61_90repay_order_num'].'/'.number_format($value['61_90repay_amount']/100)); ?></td>
            <td><?php echo Html::encode($value['91_repay_order_num'].'/'.number_format($value['91_repay_amount']/100)); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($info)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</form>