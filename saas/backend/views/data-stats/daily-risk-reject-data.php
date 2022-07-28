<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
$this->showtips('节点说明', ["T101:前置决策; T102:主决策"]);
/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Daily statistics of reasons for rejection of risk control') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:<?php echo Yii::T('common', 'Default all channels') ?>});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-risk-reject']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('add_start')); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode(Yii::$app->request->get('add_end')); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('tree_code', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('tree_code', [])),
    $treeCodeList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'tree_code']); ?>&nbsp;
<?php echo Yii::T('common', 'reason') ?>： <input type="text" value="<?= Html::encode(Yii::$app->request->get('txt')); ?>"  name="txt">&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'channel') ?></th>
            <th><?php echo Yii::T('common', 'node') ?></th>
            <th><?php echo Yii::T('common', 'Refuse to reason') ?></th>
            <th><?php echo Yii::T('common', 'Refused to number') ?></th>
            <th><?php echo Yii::T('common', 'Creation time') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['date']); ?></td>
            <td><?php echo Html::encode($value['app_market']); ?></td>
            <td><?php echo Html::encode($value['tree_code']); ?></td>
            <td><?php echo Html::encode($value['txt']); ?></td>
            <td><?php echo Html::encode($value['reject_count']); ?></td>
            <td><?php echo date('Y-m-d H:i',$value['created_at'])?></td>
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