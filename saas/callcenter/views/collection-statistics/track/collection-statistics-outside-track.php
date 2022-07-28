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

echo $this->render('submenus');
?>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/loan-collection-outside-track']),'options' => ['style' => 'margin-top:5px;']]); ?>
<span class="s_item"><?php echo Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-01'))) ; ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d',time()))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php if($is_outside ==false):?><?php echo Yii::T('common', 'Collection agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency'),'onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;<?php endif;?>
<?php echo Yii::T('common', 'Collection Groups') ?>：<?=Html::dropDownList('loan_group',Html::encode(Yii::$app->getRequest()->get('loan_group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Order group') ?>：<?=Html::dropDownList('order_level',Html::encode(Yii::$app->getRequest()->get('order_level', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team">
    <?php  echo \yii\helpers\Html::dropDownList('group_game', \common\helpers\CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('group_game', [])),
        $teamList,['class' => 'form-control team-select', 'multiple' => 'multiple']); ?>&nbsp;</span>
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="hidden" name="flag" value="<?php echo  $flag ?>">
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>日期</th>
            <?php if (!empty($arrMerchant)): ?>
                <th>Merchant</th>
            <?php endif; ?>
            <th ><?php echo Yii::T('common', 'agency') ?></th>
            <th ><?php echo Yii::T('common', 'Collection Groups') ?></th>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><a id="today_all_money"><?php echo Yii::T('common', 'Amount due-total') ?></a></th>
            <th ><a id="today_finish_money"><?php echo Yii::T('common', 'Amount due-paid') ?></a></th>
            <th ><?php echo Yii::T('common', 'Amount due-remaining') ?></th>
            <th ><?php echo Yii::T('common', 'number of order') ?></th>
            <th ><?php echo Yii::T('common', 'Orders returned') ?></th>
            <th ><a id="finish_total_rate"><?php echo Yii::T('common', 'Repayment rate (amount)') ?></a></th>
            <th ><a id="oneday_money_rate"><?php echo Yii::T('common', 'First day completion rate') ?></a></th>
            <th ><?php echo Yii::T('common', 'Total amount of late payment') ?></th>
            <th ><?php echo Yii::T('common', 'Late payment amount') ?></th>
            <th ><a id="late_fee"><?php echo Yii::T('common', 'Late payment recovery rate') ?></a></th>
            <th ><?php echo Yii::T('common', 'Processing capacity') ?></th>
        </tr>
        <?php foreach ($dateData as $value): ?>
            <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['date'] ?? '汇总'; ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo '-'; ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo '-';?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo '-'; ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format($value['today_all_money'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format($value['today_finish_money'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format(($value['today_all_money']-$value['today_finish_money']) / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['loan_total']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['loan_finish_total']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['today_all_money']) ? sprintf("%.2f", number_format(($value['today_finish_money']/$value['today_all_money']),4)*100).'%' : '0.00%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['today_all_money']) ? sprintf("%.2f", number_format(($value['oneday_money']/$value['today_all_money']),4)*100).'%' : '0.00%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format( $value['today_finish_late_fee'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format( $value['finish_late_fee'] / 100,2)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['today_finish_late_fee'])?sprintf("%.2f", number_format(($value['finish_late_fee']/$value['today_finish_late_fee']),4)*100).'%':'0.00%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(number_format($value['operate_total'])); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php foreach ($outside_data as $value): ?>
                <tr class="hover">
                    <td ><?php echo Html::encode($value['date']); ?></td>
                    <?php if (!empty($arrMerchant)): ?>
                        <td>
                            <?php echo Html::encode(!empty($arrMerchant[$value['merchant_id']]) ? $arrMerchant[$value['merchant_id']] : ''); ?>
                        </td>
                    <?php endif; ?>
                    <td ><?php echo Html::encode($outsides[$value['outside_id']]); ?></td>
                    <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['loan_group']]); ?></td>
                    <td ><?php echo Html::encode(isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:'');?></td>
                    <td ><?php echo Html::encode(number_format($value['today_all_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['today_finish_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format(($value['today_all_money']-$value['today_finish_money'])  / 100,2)); ?></td>
                    <td ><?php echo Html::encode($value['loan_total']); ?></td>
                    <td ><?php echo Html::encode($value['loan_finish_total']); ?></td>
                    <td ><?php echo Html::encode(sprintf("%.2f", number_format(($value['today_finish_money']/$value['today_all_money']),4)*100).'%'); ?></td>
                    <td ><?php echo Html::encode(sprintf("%.2f", number_format(($value['oneday_money']/$value['today_all_money']),4)*100).'%'); ?></td>
                    <td ><?php echo Html::encode(number_format( $value['today_finish_late_fee'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format( $value['finish_late_fee'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(!empty($value['today_finish_late_fee'])?sprintf("%.2f", number_format(($value['finish_late_fee']/$value['today_finish_late_fee']),4)*100).'%':'0.00%'); ?></td>
                    <td ><?php echo Html::encode(number_format($value['operate_total'])); ?></td>
                </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($outside_data)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<?php if(!empty($outside_data)):?>
    <div style="color:#428bca;font-size: 14px;font-weight:bold;" >每页&nbsp;<?php echo Html::dropDownList('page_size', Yii::$app->getRequest()->get('page_size', 15), LoanCollectionRecord::$page_size); ?>&nbsp;条</div>
    <script type="text/javascript">
        $('select[name=page_size]').change(function(){
            var pages_size = $(this).val();
            $('#search_form').append("<input type='hidden' name='page_size' value="+ pages_size+">");
            $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">');
            $('#search_form').submit();
        });
    </script>
<?php endif;?>
<br>
<p><?php echo Yii::T('common', 'Update Time: 45 minutes 8-23') ?></p>
<p><?php echo Yii::T('common', 'Institution-cumulative tracking Yes Institution-daily tracking Aggregated results based on time range') ?></p>
<p><?php echo Yii::T('common', 'Amount due date-total amount: the total amount of the due date of the order dispatched on the same day, the amount of the repeated order will be superimposed') ?></p>
<p><?php echo Yii::T('common', 'Amount due date-repaid: The amount of the due date of the payment and repayment (or subsequent repayment will also be updated), and repeated distribution will not be superimposed') ?></p>
<p><?php echo Yii::T('common', 'Amount due date-remaining: Amount due date-total-Amount due date-remaining') ?></p>
<p><?php echo Yii::T('common', 'Number of orders: The number of orders distributed on the day, the amount of repeated orders will be superimposed') ?></p>
<p><?php echo Yii::T('common', 'Orders repaid: The number of orders that are dispatched and repaid (or subsequent repayments will also be updated), and duplicate dispatches will not be superimposed') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: Amount due-paid / amount due-total') ?></p>
<p><?php echo Yii::T('common', 'First Day Completion Rate: Amount due on the day completed / Amount due on the day-total') ?></p>
<p><?php echo Yii::T('common', 'Total Late Payment Amount: The total late payment fee for orders that have been returned') ?></p>
<p><?php echo Yii::T('common', 'Amount of late payment: The remaining amount of the order has been returned, and the total amount of the late payment is the smallest, and there is a difference between the total amount of the late payment and the amount of the late payment') ?></p>
<p><?php echo Yii::T('common', 'Late payment recovery rate: amount of late payment / total amount of late payment') ?></p>
<p><?php echo Yii::T('common', 'Processing volume: the number of orders that have been reminded in the number of orders distributed on the same day') ?></p>

<br>
<script type="text/javascript">
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
    //还款率排序
    $('#finish_total_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="finish_total_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    //迁移率排序
    $('#no_finish_total_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="no_finish_total_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    //派单数排序
    $('#loan_total').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="loan_total">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    //滞纳金回收率排序
    $('#late_fee').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="late_fee">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //本金排序
    $('#today_all_money').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="today_all_money">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //已还本金排序
    $('#today_finish_money').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="today_finish_money">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //完成单数排序
    $('#loan_finish_total').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="loan_finish_total">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //首日完成率排序
    $('#oneday_money_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="oneday_money_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
</script>

