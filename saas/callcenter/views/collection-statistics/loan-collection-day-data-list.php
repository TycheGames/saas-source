<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminUser;
use yii\helpers\Url;
/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collection_day_data_list');

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/loan-collection-day-data-list']),'options' => ['style' => 'margin-top:5px;']]); ?>
Operator：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
Group Of Overdue：<?=Html::dropDownList('group',Html::encode(Yii::$app->getRequest()->get('group', 0)),LoanCollectionOrder::$level,array('prompt' => '-aLl group-'));?>&nbsp;
<?php if($is_outside ==false):?>Company：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),['prompt' => '-all company-','onchange' => 'onOutsideChange($(this).val())']);?>&nbsp;<?php endif;?>
Small Group：<span id="team"><?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->getRequest()->get('group_game', '')), $teamList,array('prompt' => '-all-')); ?></span>
Date：<input type="text" value="<?= Html::encode(empty($startDate) ? '' : $startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?= Html::encode(empty($endDate) ? '' : $endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<input type="hidden" value="<?php echo Html::encode(isset($sort_type)?$sort_type:'asc'); ?>" name="sort_type">
<input type="hidden" value="<?php echo Html::encode(isset($sort_key)?$sort_key:'id'); ?>" name="sort_key">
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th >Date</th>
            <th >Operator</th>
            <th >Group</th>
            <th >Company</th>
            <th >Allocation Count</th>
            <th >Allocation Amount</th>
            <th >Operation Count In Current Date</th>
            <th >Repayment Count</th>
            <th >Repayment Amount</th>
            <th >Repayment Rate By Count</th>
            <th >Repayment Rate By Amount</th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($dateData as $value): ?>
            <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['username'] ?? '--'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(isset($value['group']) ? (LoanCollectionOrder::$level[$value['group']] ?? '-') : '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['real_title'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['get_total_count']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format($value['get_total_money'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['operate_total']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['finish_total_count']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format($value['finish_total_money'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['get_total_count'] == 0 ? '0%' : sprintf("%.2f", $value['finish_total_count']/$value['get_total_count']*100).'%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['get_total_money'] == 0 ? '0%' : sprintf("%.2f", $value['finish_total_money']/$value['get_total_money']*100).'%'); ?></td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['date'] ?? '-'); ?></td>
                <td ><?php echo Html::encode($value['username'] ?? '-'); ?></td>
                <td ><?php echo Html::encode(isset($value['group']) ? (LoanCollectionOrder::$level[$value['group']] ?? '-') : '-'); ?></td>
                <td ><?php echo Html::encode($value['real_title'] ?? '-'); ?></td>
                <td ><?php echo Html::encode($value['get_total_count']); ?></td>
                <td ><?php echo Html::encode(number_format($value['get_total_money'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['operate_total']); ?></td>
                <td ><?php echo Html::encode($value['finish_total_count']); ?></td>
                <td ><?php echo Html::encode(number_format($value['finish_total_money'] / 100,2)); ?></td>
                <td ><?php echo Html::encode($value['get_total_count'] == 0 ? '0%' : sprintf("%.2f", $value['finish_total_count']/$value['get_total_count']*100).'%'); ?></td>
                <td ><?php echo Html::encode($value['get_total_money'] == 0 ? '0%' : sprintf("%.2f", $value['finish_total_money']/$value['get_total_money']*100).'%'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<div style="color:#428bca;font-size: 14px;font-weight:bold;" >per page&nbsp;<?php echo Html::dropDownList('page_size', Yii::$app->getRequest()->get('page_size', 15), LoanCollectionRecord::$page_size); ?>&nbsp;item</div>
<br>
<p>更新时间：每20分更新一次</p>
<p>Allocation Count: 派单数,重复派单会叠加</p>
<p>Allocation Amount:派单金额,重复派单会叠加</p>
<p>Operation Count In Current Date:进行催记的订单数(分派之后有进行催记的)</p>
<p>Repayment Count:还款完成出催的订单数</p>
<p>Repayment Amount:还款完成出催的订单的还款金额</p>
<p>Repayment Rate By Count:  Repayment Count/Allocation Count</p>
<p>Repayment Rate By Amount:  Repayment Amount/Allocation Amount</p>

<script type="text/javascript">
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#search_form').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">');
        $('#search_form').submit();
    });
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }

    function DateToUnix(string) {
        var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
            parseInt(d[0], 10) || null,
            (parseInt(d[1], 10) || 1) - 1,
            parseInt(d[2], 10) || null,
            parseInt(t[0], 10) || null,
            parseInt(t[1], 10) || null,
            parseInt(t[2], 10) || null
        )).getTime() / 1000;

    }

    function onOutsideChange(outside){
        $("#team").html('loading');
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                var htmlStr = '<select name="group_game"><option value=>-all-</option>';
                $.each(res,function(i,val){
                    htmlStr += '<option value='+i+'>'+val+'</option>';
                });

                htmlStr+='</select>';
                $("#team").html(htmlStr);
            }
        });
    }
</script>

