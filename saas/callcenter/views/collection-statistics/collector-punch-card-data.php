<?php

use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\CollectionCheckinLog;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collector_attendance_day_data');
$this->showsubmenu(Yii::T('common', 'Collector Attendance'), array(
    [Yii::T('common', 'Collector Attendance'),Url::toRoute('collection-statistics/collector-attendance-day-data'),0],
    [Yii::T('common', 'Collector talk time talk times'),Url::toRoute('collection-statistics/collector-call-data'),0],
    [Yii::T('common', 'Collector punch'),Url::toRoute('collection-statistics/collector-punch-card-data'),1],
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
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/collector-punch-card-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
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
<?php echo Yii::T('common', 'Check-in Address Type') ?>：<?=Html::dropDownList('address_type',Html::encode(Yii::$app->getRequest()->get('address_type', '')),CollectionCheckinLog::$address_type_map,array('prompt' => '-all-'));?>&nbsp;
<span class="s_item"><?php echo Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d'))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<input type="submit" name="submitcsv" value="join导出csv" onclick="$(this).val('joinexportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?php echo Yii::T('common', 'date') ?></th>
            <th ><?php echo Yii::T('common', 'agency') ?></th>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><?php echo Yii::T('common', 'Grouping') ?></th>
            <th ><?php echo Yii::T('common', 'Collector') ?></th>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th ><?php echo Yii::T('common', 'Real Name') ?></th>
            <?php endif; ?>
            <th ><?php echo Yii::T('common', 'Check-in Address Type') ?></th>
            <th ><?php echo Yii::T('common', 'Punch time at work') ?></th>
            <th ><?php echo Yii::T('common', 'Punch time after work') ?></th>
            <th ><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['real_title'] ?? '--'); ?></td>
                <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['group']] ?? '--'); ?></td>
                <td ><?php echo Html::encode($teamList[$value['group_game']] ?? '--'); ?></td>
                <td ><?php echo Html::encode($value['username']); ?></td>
                <?php if ($setRealNameCollectionAdmin): ?>
                    <td ><?php echo Html::encode($value['real_name']); ?></td>
                <?php endif; ?>
                <td ><?php echo Html::encode(CollectionCheckinLog::$address_type_map[$value['address_type']] ?? '--'); ?></td>
                <td ><?php echo Html::encode(!empty($value['sbsj']) ? date('Y-m-d H:i:s',$value['sbsj']) : '--'); ?></td>
                <td ><?php echo Html::encode(!empty($value['xbsj']) ? date('Y-m-d H:i:s',$value['xbsj']) : '--'); ?></td>
                <td><?php echo Html::a('edit',Url::to(['collector-punch-card-edit','id' => $value['id']]))?></td>
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
</script>

