<?php

use yii\widgets\ActiveForm;
use backend\models\remind\RemindGroup;
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\CollectorCallData;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_reminder_call_data');
$this->showsubmenu(Yii::T('common', 'Reminder calling length and times'), array(
    [Yii::T('common', 'Reminder calling length and times'),Url::toRoute('customer/reminder-call-data'),1],
    [Yii::T('common', 'Reminder punch'),Url::toRoute('customer/reminder-punch-card-data'),0],
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
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['customer/reminder-call-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
<?=Yii::T('common', 'Dial type') ?>：<?=Html::dropDownList('phone_type',Html::encode(Yii::$app->getRequest()->get('phone_type', 0)),CollectorCallData::$phone_map,array('prompt' => '-所有类型-'));?>&nbsp;
<?=Yii::T('common', 'groups') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),RemindGroup::allGroupName(),array('prompt' => '-所有分组-'));?>&nbsp;
<?=Yii::T('common', 'Reminder') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
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
            <th style="border-right:1px solid #A9A9A9;"><?=Yii::T('common', 'Reminder') ?></th>
            <th style="color: blue"><?=Yii::T('common', 'Total call (person/times/mins)') ?><br></th>
            <th style="color: blue"><?=Yii::T('common', 'Average per person(times/mins)') ?><br></th>
            <th style="border-right:1px solid #A9A9A9;color: blue;"><?=Yii::T('common', 'Total invaild call(person/times)') ?><br></th>

            <th style="color: red"><?=Yii::T('common', 'Call customer(person/times/mins)') ?><br> </th>
            <th style="border-right:1px solid #A9A9A9;color: red"><?=Yii::T('common', 'Invalid call customer(person/times)') ?><br> </th>

            <th style="color: blue"><?=Yii::T('common', 'Call contact(person/times/mins)') ?><br></th>
            <th style="border-right:1px solid #A9A9A9;color: blue"><?=Yii::T('common', 'Invaild call contact(person/times)') ?><br></th>
        </tr>
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['date'] != '汇总') ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['date'] ?? '-') ; ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['total_person'].'/'.$value['total_times'].'/'. floor($value['total_duration'] / 60).'分'.($value['total_duration'] % 60).'秒'); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode(empty($value['total_person']) ? '-': (floor($value['total_times']/$value['total_person']).'/'.floor(($value['total_duration'] / $value['total_person']) / 60).'分'.(floor($value['total_duration'] / $value['total_person']) % 60).'秒')); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_total_person'].'/'.$value['invalid_total_times']); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['oneself_person'].'/'.$value['oneself_times'].'/'.floor($value['oneself_duration'] / 60).'分'.($value['oneself_duration'] % 60).'秒'); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_oneself_person'].'/'.$value['invalid_oneself_times']); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['contact_person'].'/'.$value['contact_times'].'/'.floor($value['contact_duration'] / 60).'分'.($value['contact_duration'] % 60).'秒'); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_contact_person'].'/'.$value['invalid_contact_times']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['name']); ?></td>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode($value['username']); ?></td>
                <td style="color: blue"><?php echo Html::encode($value['total_person'].'/'.$value['total_times'].'/'. floor($value['total_duration'] / 60).'分'.($value['total_duration'] % 60).'秒'); ?></td>
                <td style="color: blue"><?php echo Html::encode(empty($value['total_person']) ? '-': (floor($value['total_times']/$value['total_person']).'/'.floor(($value['total_duration'] / $value['total_person']) / 60).'分'.(floor($value['total_duration'] / $value['total_person']) % 60).'秒')); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: blue;"><?php echo Html::encode($value['invalid_total_person'].'/'.$value['invalid_total_times']); ?></td>
                <td style="color: red"><?php echo Html::encode($value['oneself_person'].'/'.$value['oneself_times'].'/'.floor($value['oneself_duration'] / 60).'分'.($value['oneself_duration'] % 60).'秒'); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: red"><?php echo Html::encode($value['invalid_oneself_person'].'/'.$value['invalid_oneself_times']); ?></td>
                <td style="color: blue"><?php echo Html::encode($value['contact_person'].'/'.$value['contact_times'].'/'.floor($value['contact_duration'] / 60).'分'.($value['contact_duration'] % 60).'秒'); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: blue"><?php echo Html::encode($value['invalid_contact_person'].'/'.$value['invalid_contact_times']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>

