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

$this->shownav('manage', 'menu_collection_work_statistics');
$this->showsubmenu(Yii::T('common', 'Daily statistics'), array(
    [Yii::T('common', 'Collector-Daily Statistics'),Url::toRoute('loan-collection-admin-work-list'),1],
    [Yii::T('common', 'Agency-Daily Statistics'),Url::toRoute('loan-collection-outside-work-list'),0],
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get','action'=>Url::to(['collection-statistics/loan-collection-admin-work-list']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Collector') ?>:<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php if ($setRealNameCollectionAdmin): ?>
<?php echo Yii::T('common', 'Real Name') ?>:<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('real_name', '')); ?>" name="real_name" class="txt" style="width:120px;">&nbsp;
<?php endif; ?>
<?php echo Yii::T('common', 'Collector Group') ?>：<?=Html::dropDownList('loan_group',Html::encode(Yii::$app->getRequest()->get('loan_group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Order group') ?>：<?=Html::dropDownList('order_level',Html::encode(Yii::$app->getRequest()->get('order_level', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php if($is_outside ==false):?><?php echo Yii::T('common', 'Collection agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency'),'onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;<?php endif;?>
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team"><?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->getRequest()->get('group_game', '')), $teamList,array('prompt' => '-all-')); ?></span>
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('oneday')) ? date("Y-m-d", time()) : Yii::$app->request->get('oneday')); ?>"  name="oneday" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<input type="hidden" name="flag" value="<?php echo  $flag ?>">
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php echo Yii::T('common', '(Update time 8: 42 ~ 22: 42 updated every hour)') ?>
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?php echo Yii::T('common', 'agency') ?></th>
            <?php if (!empty($arrMerchant)): ?>
                <th>Merchant</th>
            <?php endif; ?>
            <th ><?php echo Yii::T('common', 'Collector Group') ?></th>
            <th ><?php echo Yii::T('common', 'Collector') ?></th>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th ><?php echo Yii::T('common', 'Real Name') ?></th>
            <?php endif; ?>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><?php echo Yii::T('common', 'Total due due') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayment due') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayments distributed on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayments due on the day') ?></th>

            <th ><?php echo Yii::T('common', 'Total remaining') ?></th>
            <th ><?php echo Yii::T('common', 'Total orders') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayment') ?></th>
            <th ><?php echo Yii::T('common', 'Number of orders dispatched on the day') ?></th>
            <th ><a id="today_finish_total" href="javascript:;"><?php echo Yii::T('common', 'Repayments on the day') ?></a></th>
            <th ><a id="finish_total_rate" href="javascript:;"><?php echo Yii::T('common', 'Repayment rate') ?></a></th>
            <th > <?php echo Yii::T('common', 'Total amount of late payment') ?></th>
            <th ><?php echo Yii::T('common', 'Late payment amount') ?></th>
            <th ><?php echo Yii::T('common', 'Late payment recovery rate') ?>
            <th ><?php echo Yii::T('common', 'Total recall amount') ?></th>
            <th ><?php echo Yii::T('common', 'Daily processing capacity') ?></th>
            <th ><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        <tr class="header">
            <th ><?php echo Yii::T('common', 'summary') ?></th>
            <?php if (!empty($arrMerchant)): ?>
                <th >--</th>
            <?php endif; ?>
            <th >--</th>
            <th >--</th>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th >--</th>
            <?php endif; ?>
            <th ><?php echo Html::encode(number_format($total_all_money/100,2));?></th>
            <th ><?php echo Html::encode(number_format($total_yes_money/100,2));?></th>
            <th ><?php echo Html::encode(number_format($today_get_total_money/100,2));?></th>
            <th ><?php echo Html::encode(number_format($total_today_money/100,2));?></th>
            <th ><?php echo Html::encode(number_format(($total_all_money-$total_yes_money)/100,2));?></th>
            <th ><?php echo Html::encode($total_all_total);?></th>
            <th ><?php echo Html::encode($total_yes_total);?></th>
            <th ><?php echo Html::encode($today_get_loan_total);?></th>
            <th ><?php echo Html::encode($total_today_total);?></th>
            <th ><?php echo Html::encode(sprintf("%.2f", $finish_fee*100).'%'); ?></th>
            <th ><?php echo Html::encode(sprintf("%.2f", $no_finish_fee*100).'%'); ?></th>
            <th ><?php echo Html::encode(number_format($total_all_late/100,2));?></th>
            <th ><?php echo Html::encode(number_format($total_yes_late/100,2));?></th>
            <th ><?php echo Html::encode(sprintf("%.2f", $late_fee*100).'%'); ?></th>
            <th ><?php echo Html::encode(number_format($member_fee/100,2));?></th>
            <th ><?php echo Html::encode($total_operate_total);?></th>
            <th >--</th>
        </tr>
        <?php foreach ($loan_collection_statistics as $value): ?>

                <tr class="hover">
                    <td ><?php echo Html::encode($outsides[$value['outside']] ?? '--'); ?></td>
                    <?php if (!empty($arrMerchant)): ?>
                        <td>
                            <?php echo Html::encode(!empty($arrMerchant[$value['merchant_id']]) ? $arrMerchant[$value['merchant_id']] : ''); ?>
                        </td>
                    <?php endif; ?>
                    <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['loan_group']]); ?></td>
                    <td ><?php echo Html::encode($value['username']); ?></td>
                    <?php if ($setRealNameCollectionAdmin): ?>
                        <td ><?php echo Html::encode($value['real_name']); ?></td>
                    <?php endif; ?>
                    <td ><?= isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:''; ?>
                    </td>
                    <td ><?php echo Html::encode(number_format($value['total_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['finish_total_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['today_get_total_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['today_finish_total_money'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format( ($value['total_money']-$value['finish_total_money']) / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['loan_total'])); ?></td>
                    <td ><?php echo Html::encode(number_format($value['finish_total'])); ?></td>
                    <td ><?php echo Html::encode(number_format($value['today_get_loan_total'])); ?></td>
                    <td ><?php echo Html::encode(number_format($value['today_finish_total'])); ?></td>
                    <td ><?php echo Html::encode(sprintf("%.2f", $value['finish_total_rate']*100).'%'); ?></td>
                    <td ><?= Html::encode(sprintf('%.2f', $value['late_fee_total'] / 100)); ?></td>
                    <td ><?php echo Html::encode(number_format( $value['finish_late_fee'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(sprintf("%.2f", $value['finish_late_fee_rate']*100).'%'); ?></td>
                    <td ><?php echo Html::encode(number_format( $value['member_fee'] / 100,2)); ?></td>
                    <td ><?php echo Html::encode(number_format($value['operate_total'])); ?></td>
                    <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['updated_at'])); ?></td>
                </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($loan_collection_statistics)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<div style="color:#428bca;font-size: 14px;font-weight:bold;" >每页&nbsp;<?php echo Html::dropDownList('page_size', Yii::$app->getRequest()->get('page_size', 15), LoanCollectionRecord::$page_size); ?>&nbsp;条</div>

<br>
<p><?php echo Yii::T('common', 'Update Time: 8-23 2:42') ?></p>
<p><?php echo Yii::T('common', 'Total due due: The total amount due due up to the day of the cumulative distribution order') ?></p>
<p><?php echo Yii::T('common', 'Total repayment of repayment: the total amount of repayment due to the due date of the repayment order') ?></p>
<p><?php echo Yii::T('common', 'The total repayment of the repayment of the day: the total amount of the repayment of the order that has been repaid on the day') ?></p>
<p><?php echo Yii::T('common', 'Total remaining repayable: Total due due-Total repayment due') ?></p>
<p><?php echo Yii::T('common', 'Total number of orders: the total number of cumulative distribution orders up to the current day') ?></p>
<p><?php echo Yii::T('common', 'Total repayment: the total number of repayment orders to that day') ?></p>
<p><?php echo Yii::T('common', 'Repayments on the day: the total number of repayment orders on the day') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: Total due due / repayment due') ?></p>
<p><?php echo Yii::T('common', 'Migration rate: S1 / S2 / M1 not displayed (1-total due due / repayment due)') ?></p>
<p><?php echo Yii::T('common', 'Total Amount of Late Payment: The total amount of late payment due to repaid orders') ?></p>
<p><?php echo Yii::T('common', 'Amount of late payment: The remaining amount should be charged except for the due date, and the total amount of late payment shall be the minimum') ?></p>
<p><?php echo Yii::T('common', 'Late payment recovery rate: amount of late payment / total amount of late payment') ?></p>
<p><?php echo Yii::T('common', 'Total Recall Amount: The repayment amount of the order that has been repaid to that day') ?></p>

<br>
<script type="text/javascript">
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
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#search_form').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">');
        $('#search_form').submit();
    });

    //还款率排序
    $('#finish_total_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="finish_total_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //首日完成数
    $('#today_finish_total').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="today_finish_total">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
</script>

