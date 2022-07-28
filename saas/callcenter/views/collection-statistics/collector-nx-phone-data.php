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
    [Yii::T('common', 'Collector talk time talk times'),Url::toRoute('collection-statistics/collector-call-data'),0],
    [Yii::T('common', 'Collector punch'),Url::toRoute('collection-statistics/collector-punch-card-data'),0],
    [Yii::T('common', 'Collector NX Log'),Url::toRoute('collection-statistics/collector-nx-phone-data'),1],
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
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/collector-nx-phone-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
order_id：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
collector：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
phone：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<span class="s_item">date：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
to<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time',  date('Y-m-d H:i:s',strtotime('today')+86399))); ?>"  name="end_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!--<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">-->
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th >order_id</th>
            <th >collector</th>
            <th >nx_phone</th>
            <th >phone</th>
            <th >duration</th>
            <th >record_url</th>
            <th >start_time</th>
            <th >answer_time</th>
            <th >end_time</th>
            <th >hangup_cause</th>
        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['order_id'] ?? '-'); ?></td>
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode($value['nx_name']); ?></td>
                <td ><?php echo Html::encode($value['phone']); ?></td>
                <td ><?php echo Html::encode($value['duration']); ?>(s)</td>
                <td ><a target="_blank" href=<?php echo Html::encode($value['record_url']); ?>>Play recording</a></td></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['start_time'])); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['answer_time'])); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['end_time'])); ?></td>
                <td ><?php echo Html::encode($value['hangup_cause']); ?></td>

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

