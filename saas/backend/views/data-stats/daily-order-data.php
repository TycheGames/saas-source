<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Daily order data') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:<?php echo Yii::T('common', 'Default all channels') ?>});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-order-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('add_start')); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode(Yii::$app->request->get('add_end')); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('app_market', \yii\helpers\ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th>appMarket</th>
            <th><?php echo Yii::T('common', 'Order application (number/amount)') ?>)</th>
            <th><?php echo Yii::T('common', 'Approved order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Card binding through order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Loan order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
        <tr class="hover">
            <td><?php echo $value['date']; ?></td>
            <td><?php echo Html::encode($value['app_market']); ?></td>
            <td><?php echo $value['all_num'].'/'.number_format($value['all_amount']/100); ?></td>
            <td><?php echo $value['audit_pass_num'].'/'.number_format($value['audit_pass_amount']/100)  ?></td>
            <td><?php echo $value['bind_card_pass_num'].'/'.number_format($value['bind_card_pass_amount']/100); ?></td>
            <td><?php echo $value['loan_success_num'].'/'.number_format($value['loan_success_amount']/100)  ?></td>
            <td><?php echo date('Y-m-d H:i',$value['updated_at'])?></td>

        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</form>