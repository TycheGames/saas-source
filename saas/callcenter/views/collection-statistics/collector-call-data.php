<?php

use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\CollectorCallData;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collector_attendance_day_data');
$this->showsubmenu(Yii::T('common', 'Collector Attendance'), array(
    [Yii::T('common', 'Collector Attendance'),Url::toRoute('collection-statistics/collector-attendance-day-data'),0],
    [Yii::T('common', 'Collector talk time talk times'),Url::toRoute('collection-statistics/collector-call-data'),1],
    [Yii::T('common', 'Collector punch'),Url::toRoute('collection-statistics/collector-punch-card-data'),0],
    [Yii::T('common', 'Collector NX Log'),Url::toRoute('collection-statistics/collector-nx-phone-data'),0],
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
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/collector-call-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Dial type') ?>：<?=Html::dropDownList('phone_type',Html::encode(Yii::$app->getRequest()->get('phone_type', 0)),CollectorCallData::$phone_map,array('prompt' => '-All type-'));?>&nbsp;
<?php echo Yii::T('common', 'Collection agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::allOutsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency'),'onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;
<?php echo Yii::T('common', 'Collector Group') ?>：<?=Html::dropDownList('group',Html::encode(Yii::$app->getRequest()->get('group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team">
    <?php  echo \yii\helpers\Html::dropDownList('group_game', \common\helpers\CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('group_game', [])),
        $teamList,['class' => 'form-control team-select', 'multiple' => 'multiple']); ?>&nbsp;
</span>
<?php echo Yii::T('common', 'Collector') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php if ($setRealNameCollectionAdmin): ?>
    <?php echo Yii::T('common', 'Real Name') ?>:<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('real_name', '')); ?>" name="real_name" class="txt" style="width:120px;">&nbsp;
<?php endif; ?>
<span class="s_item"><?php echo Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d'))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?php echo Yii::T('common', 'date') ?></th>
            <th ><?php echo Yii::T('common', 'agency') ?></th>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><?php echo Yii::T('common', 'Grouping') ?></th>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th ><?php echo Yii::T('common', 'Real Name') ?></th>
            <?php endif; ?>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Collector') ?></th>
            <th style="color: blue"><?php echo Yii::T('common', 'Total call (person/times/mins)') ?></th>
            <th style="color: blue"><?php echo Yii::T('common', 'Average per person(times/mins)') ?></th>
            <th style="border-right:1px solid #A9A9A9;color: blue;"><?php echo Yii::T('common', 'Total invaild call(person/times)') ?></th>

            <th style="color: red"><?php echo Yii::T('common', 'Call customer(person/times/mins)') ?></th>
            <th style="border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Invalid call customer(person/times)') ?></th>

            <th style="color: blue"><?php echo Yii::T('common', 'Call contact(person/times/mins)') ?></th>
            <th style="border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'Invaild call contact(person/times)') ?></th>

            <th style="color: red"><?php echo Yii::T('common', 'Call address book(person/times/mins)') ?></th>
            <th style="border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Invalid call address book(person/times)') ?></th>


        </tr>
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['date'] != '汇总') ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo $value['date'] ?? '-' ; ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <?php if ($setRealNameCollectionAdmin): ?>
                    <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <?php endif; ?>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'>--</td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['total_person'].'/'.$value['total_times'].'/'. floor($value['total_duration'] / 60).'分'.($value['total_duration'] % 60).'秒'); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode(empty($value['total_person']) ? '-': (floor($value['total_times']/$value['total_person']).'/'.floor(($value['total_duration'] / $value['total_person']) / 60).'分'.(floor($value['total_duration'] / $value['total_person']) % 60).'秒')); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_total_person'].'/'.$value['invalid_total_times']); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['oneself_person'].'/'.$value['oneself_times'].'/'.floor($value['oneself_duration'] / 60).'分'.($value['oneself_duration'] % 60).'秒'); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_oneself_person'].'/'.$value['invalid_oneself_times']); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['contact_person'].'/'.$value['contact_times'].'/'.floor($value['contact_duration'] / 60).'分'.($value['contact_duration'] % 60).'秒'); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_contact_person'].'/'.$value['invalid_contact_times']); ?></td>
                <td style='<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['address_book_person'].'/'.$value['address_book_times'].'/'.floor($value['address_book_duration'] / 60).'分'.($value['address_book_duration'] % 60).'秒'); ?></td>
                <td style='border-right:1px solid #A9A9A9;<?= ($value['date'] == '汇总') ?"color:blue;font-weight:bold" : "color:red;";?>'><?php echo Html::encode($value['invalid_address_book_person'].'/'.$value['invalid_address_book_times']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['outside_name']); ?></td>
                <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['group']]); ?></td>
                <td ><?php echo Html::encode($teamList[$value['group_game']] ?? '-'); ?></td>
                <?php if ($setRealNameCollectionAdmin): ?>
                    <td ><?php echo Html::encode($value['real_name']); ?></td>
                <?php endif; ?>
                <td style="border-right:1px solid #A9A9A9;"><?php echo Html::encode($value['username']); ?></td>
                <td style="color: blue"><?php echo Html::encode($value['total_person'].'/'.$value['total_times'].'/'. floor($value['total_duration'] / 60).'分'.($value['total_duration'] % 60).'秒'); ?></td>
                <td style="color: blue"><?php echo Html::encode(empty($value['total_person']) ? '-': (floor($value['total_times']/$value['total_person']).'/'.floor(($value['total_duration'] / $value['total_person']) / 60).'分'.(floor($value['total_duration'] / $value['total_person']) % 60).'秒')); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: blue;"><?php echo Html::encode($value['invalid_total_person'].'/'.$value['invalid_total_times']); ?></td>
                <td style="color: red"><?php echo Html::encode($value['oneself_person'].'/'.$value['oneself_times'].'/'.floor($value['oneself_duration'] / 60).'分'.($value['oneself_duration'] % 60).'秒'); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: red"><?php echo Html::encode($value['invalid_oneself_person'].'/'.$value['invalid_oneself_times']); ?></td>
                <td style="color: blue"><?php echo Html::encode($value['contact_person'].'/'.$value['contact_times'].'/'.floor($value['contact_duration'] / 60).'分'.($value['contact_duration'] % 60).'秒'); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: blue"><?php echo Html::encode($value['invalid_contact_person'].'/'.$value['invalid_contact_times']); ?></td>
                <td style="color: red"><?php echo Html::encode($value['address_book_person'].'/'.$value['address_book_times'].'/'.floor($value['address_book_duration'] / 60).'分'.($value['address_book_duration'] % 60).'秒'); ?></td>
                <td style="border-right:1px solid #A9A9A9;color: red"><?php echo Html::encode($value['invalid_address_book_person'].'/'.$value['invalid_address_book_times']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script>
    function onOutsideChange(outside){
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                $.each(res,function(i,val){
                    $(".team-select option").eq(i-1).html(val);
                    $(".sumo_group_game .options label").eq(i-1).html(val);
                });
            }
        });
    }
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }
</script>

