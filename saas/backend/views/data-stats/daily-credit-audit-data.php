<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\manual_credit\ManualCreditLog;
use yii\helpers\ArrayHelper;

/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Letter review daily statistics') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.operator-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all reviewers') ?>'});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-credit-audit']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($addStart); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($addEnd); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'Reviewer') ?>: <input type="text" value="<?= Html::encode(Yii::$app->request->get('username')); ?>"  name="username">&nbsp;
<?php echo Yii::T('common', 'Multiple reviewers') ?>：<?php  echo \yii\helpers\Html::dropDownList('operators', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('operators', [])),
    ArrayHelper::htmlEncode($operatorList),['class' => 'form-control operator-select', 'multiple' => 'multiple']); ?>&nbsp;
<?php echo Yii::T('common', 'Audit Type') ?>：<?= \yii\helpers\Html::dropDownList('action', Html::encode(\yii::$app->request->get('action', '')), ManualCreditLog::$action_list,['prompt' => 'all']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'Reviewer') ?></th>
            <th><?php echo Yii::T('common', 'Audit Type') ?></th>
            <th><?php echo Yii::T('common', 'Number of orders reviewed') ?></th>
            <th><?php echo Yii::T('common', 'Pass count') ?></th>
            <th><?php echo Yii::T('common', 'Passing rate') ?></th>
            <th><?php echo Yii::T('common', 'Number of loans') ?></th>
            <th><?php echo Yii::T('common', 'First overdue loans') ?></th>
            <th><?php echo Yii::T('common', 'First overdue rate') ?></th>
            <th><?php echo Yii::T('common', 'Creation time') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date'] ?? '-') ; ?></td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>-</td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>-</td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['audit_count']; ?></td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['pass_count']; ?></td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php if($value['audit_count'] <= 0){echo 0;}else{echo sprintf("%0.2f",$value['pass_count']/$value['audit_count']*100);}  ?>%</td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['loan_success_count']; ?></td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['first_overdue_count']; ?></td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php if($value['loan_success_count'] <= 0){echo 0;}else{echo sprintf("%0.2f",$value['first_overdue_count']/$value['loan_success_count']*100);}  ?>%</td>
                <td <?= ($value['type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>--</td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['date']); ?></td>
            <td><?php echo Html::encode($operatorList[$value['operator_id']] ?? '-'); ?></td>
            <td><?php echo Html::encode(ManualCreditLog::$action_list[$value['action']]); ?></td>
            <td><?php echo $value['audit_count']; ?></td>
            <td><?php echo $value['pass_count']; ?></td>
            <td><?php if($value['audit_count'] <= 0){echo 0;}else{echo sprintf("%0.2f",$value['pass_count']/$value['audit_count']*100);}  ?>%</td>
            <td><?php echo $value['loan_success_count']; ?></td>
            <td><?php echo $value['first_overdue_count']; ?></td>
            <td><?php if($value['loan_success_count'] <= 0){echo 0;}else{echo sprintf("%0.2f",$value['first_overdue_count']/$value['loan_success_count']*100);}  ?>%</td>
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
<script type="text/javascript">
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }
</script>