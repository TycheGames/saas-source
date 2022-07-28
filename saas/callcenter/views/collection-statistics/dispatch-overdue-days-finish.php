<?php

use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\AdminUser;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_dispatch_overdue_days_finish');
$this->showsubmenu(Yii::T('common', 'Distribute Order Rate'));

?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/dispatch-overdue-days-finish']),'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Collector') ?>:<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Collection agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::allOutsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency'),'onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team">
    <?php  echo \yii\helpers\Html::dropDownList('group_game', \common\helpers\CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('group_game', [])),
        $teamList,['class' => 'form-control team-select', 'multiple' => 'multiple']); ?>&nbsp;
</span>
<?php echo Yii::T('common', 'Collector Group') ?>：<?=Html::dropDownList('loan_group',Html::encode(Yii::$app->getRequest()->get('loan_group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<span class="s_item"><?php echo Yii::T('common', 'Days past due for dispatch orders') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:80px;" placeholder="格式:1或1-4">&nbsp;
<span class="s_item"><?php echo Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d'))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?php echo Yii::T('common', 'Dispatch date') ?></th>
            <th ><?php echo Yii::T('common', 'Dispatch collector') ?>
            <th ><?php echo Yii::T('common', 'agency') ?></th>
            <th ><?php echo Yii::T('common', 'Grouping') ?></th>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><?php echo Yii::T('common', 'Days past due for dispatch orders') ?></th>
            <th ><?php echo Yii::T('common', 'Dispatch orders') ?></th>
            <th ><?php echo Yii::T('common', 'Distribute on the day and complete singular') ?></th>
            <th ><?php echo Yii::T('common', 'Dispatch complete singular') ?></th>
        </tr>
        <tr class="header">
            <th style='color:blue;font-weight:bold'><?php echo Yii::T('common', 'summary') ?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['username']);?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['outside']);?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($teamList[$totalData['group_game']] ?? '-');?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode(LoanCollectionOrder::$level[$totalData['group']] ?? '-');?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['overdue_day']);?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['dispatch_count']);?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['today_repay_count']);?></th>
            <th style='color:blue;font-weight:bold'><?php echo Html::encode($totalData['total_repay_count']);?></th>
        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date']); ?></td>
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode($value['outside']); ?></td>
                <td ><?php echo Html::encode(AdminUser::$group_games[$value['group_game']] ?? '-'); ?></td>
                <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['group']]); ?></td>
                <td ><?php echo Html::encode($value['overdue_day']); ?></td>
                <td ><?php echo Html::encode($value['dispatch_count']); ?></td>
                <td ><?php echo Html::encode($value['today_repay_count']); ?></td>
                <td ><?php echo Html::encode($value['total_repay_count']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">暂无记录</div>
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
