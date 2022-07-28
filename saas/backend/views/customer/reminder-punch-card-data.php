<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use backend\models\RemindCheckinLog;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_reminder_attendance_day_data');
$this->showsubmenu(Yii::T('common', 'Reminder punch'), array(
    [Yii::T('common', 'Reminder calling length and times'),Url::toRoute('customer/reminder-call-data'),0],
    [Yii::T('common', 'Reminder punch'),Url::toRoute('customer/reminder-punch-card-data'),1],
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['customer/reminder-punch-card-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
<?=Yii::T('common', 'groups') ?>：<?=Html::dropDownList('remind_group',Html::encode(Yii::$app->getRequest()->get('remind_group')),$groupList,array('prompt' => '-all-','onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;
<?=Yii::T('common', 'Reminder') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Check-in Address Type') ?>：<?=Html::dropDownList('address_type',Html::encode(Yii::$app->getRequest()->get('address_type', '')),RemindCheckinLog::$address_type_map,array('prompt' => '-all-'));?>&nbsp;
<span class="s_item"><?=Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?=Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d'))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value="<?=Yii::T('common', 'filter') ?>" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="<?=Yii::T('common', 'export') ?>" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?=Yii::T('common', 'date') ?></th>
            <th ><?=Yii::T('common', 'groups') ?></th>
            <th ><?=Yii::T('common', 'Reminder') ?></th>
            <th ><?=Yii::T('common', 'Check-in Address Type') ?></th>
            <th ><?= Yii::T('common', 'Punch time at work') ?></th>
            <th ><?= Yii::T('common', 'Punch time after work') ?></th>
        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['name'] ?? '--'); ?></td>
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode(RemindCheckinLog::$address_type_map[$value['address_type']] ?? '--'); ?></td>
                <td ><?php echo Html::encode(!empty($value['sbsj']) ? date('Y-m-d H:i:s',$value['sbsj']) : '--'); ?></td>
                <td ><?php echo Html::encode(!empty($value['xbsj']) ? date('Y-m-d H:i:s',$value['xbsj']) : '--'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>

