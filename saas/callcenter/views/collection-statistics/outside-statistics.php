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
    [Yii::T('common', 'Collector-Daily Statistics'),Url::toRoute('loan-collection-admin-work-list'),0],
    [Yii::T('common', 'Agency-Daily Statistics'),Url::toRoute('loan-collection-outside-work-list'),1],
));

?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php if($is_outside == false):?><?php echo Yii::T('common', 'Collection agency') ?>：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::outsideRealName($merchant_id),array('prompt' => Yii::T('common', 'All agency'),'onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;<?php endif;?>
<?php echo Yii::T('common', 'Collector Group') ?>：<?=Html::dropDownList('loan_group',Html::encode(Yii::$app->getRequest()->get('loan_group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Order group') ?>：<?=Html::dropDownList('order_level',Html::encode(Yii::$app->getRequest()->get('order_level', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team"><?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->getRequest()->get('group_game', '')), $teamList,array('prompt' => '-all-')); ?></span>&nbsp;
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('oneday')) ? date("Y-m-d", time()) : Yii::$app->request->get('oneday')); ?>"  name="oneday" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<input type="hidden" name="sort_type" value="<?=Html::encode($sort_type ? $sort_type : 'desc') ;?>">
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th ><?php echo Yii::T('common', 'Collection agency') ?></th>
            <?php if (!empty($arrMerchant)): ?>
                <th>Merchant</th>
            <?php endif; ?>
            <th ><?php echo Yii::T('common', 'Collector Group') ?></th>
            <th ><?php echo Yii::T('common', 'Order group') ?></th>
            <th ><?php echo Yii::T('common', 'Accumulative total of repaid orders') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayments distributed on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayment on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayment') ?></th>
            <th ><?php echo Yii::T('common', 'Total remaining') ?></th>
            <th ><?php echo Yii::T('common', 'Cumulative total number of dispatch orders') ?></th>
            <th ><?php echo Yii::T('common', 'Number of orders dispatched on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Repayments on the day') ?></th>
            <th ><?php echo Yii::T('common', 'Total repayment') ?></th>
            <th ><a href="javascript:;" id="finish_rate"><?php echo Yii::T('common', 'Repayment rate') ?></a></th>
            <th ><?php echo Yii::T('common', 'Total amount of late payment') ?></th>
            <th ><?php echo Yii::T('common', 'Late payment amount') ?></th>
            <th ><?php echo Yii::T('common', 'Late payment recovery rate') ?></th>
            <th ><?php echo Yii::T('common', 'Total recall amount') ?></th>
            <th ><?php echo Yii::T('common', 'Daily processing capacity') ?></th>
            <th ><?php echo Yii::T('common', 'Trend') ?></th>
        </tr>
        <?php if(!empty($loan_group) || !empty($order_level) || !empty($group_game) || !empty($outside)):?>
            <tr class="header">
                <th ><?php echo Yii::T('common', 'summary') ?></th>
                <th ></th>
                <th ></th>
                <th ></th>
                <th ><?php echo Html::encode(number_format($total_all_money/100,2));?></th>
                <th ><?php echo Html::encode(number_format($today_get_total_money/100,2));?></th>
                <th ><?php echo Html::encode(number_format($total_today_money/100,2));?></th>
                <th ><?php echo Html::encode(number_format($total_yes_money/100,2));?></th>
                <th ><?php echo Html::encode(number_format(($total_all_money-$total_yes_money)/100,2));?></th>
                <th ><?php echo Html::encode($total_all_total);?></th>
                <th ><?php echo Html::encode($today_get_loan_total);?></th>
                <th ><?php echo Html::encode($total_today_total);?></th>
                <th ><?php echo Html::encode($total_yes_total);?></th>
                <th ><?php echo Html::encode(sprintf("%.2f", $finish_fee*100).'%'); ?></th>
                <th ><?php echo Html::encode(number_format($total_all_late/100,2));?></th>
                <th ><?php echo Html::encode(number_format($total_yes_late/100,2));?></th>
                <th ><?php echo Html::encode(sprintf("%.2f", $late_fee*100).'%'); ?></th>
                <th ><?php echo Html::encode(number_format($member_fee/100,2));?></th>
                <th ><?php echo Html::encode($total_operate_total);?></th>
                <!--<th ><?php /*echo date('Y-m-d H:i:s',$updated_time);*/?></th>-->
            </tr>

        <?php endif;?>
        <?php if(!$is_sort):?>
            <?php foreach ($outside_data as $one_outside): ?>
                <?php foreach ($one_outside as $key=>$value): ?>
                    <?php if (1==1) :?>
                        <?php foreach($value as $val):?>
                            <tr class="hover">
                                <td ><?php echo $outsides[$val['outside_id']]; ?></td>
                                <?php if (!empty($arrMerchant)): ?>
                                    <th>
                                        <?php echo Html::encode(!empty($arrMerchant[$val['merchant_id']]) ? $arrMerchant[$val['merchant_id']] : ''); ?>
                                    </th>
                                <?php endif; ?>
                                <td ><?php echo Html::encode(LoanCollectionOrder::$level[$val['loan_group']]); ?></td>
                                <td ><?php echo Html::encode(LoanCollectionOrder::$level[$val['order_level']]??'--');?></td>
                                <td ><?php echo Html::encode(number_format( $val['total_money'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['today_get_total_money'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['today_finish_total_money'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['finish_total_money'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format( ($val['total_money']-$val['finish_total_money']) / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format($val['loan_total'])); ?></td>
                                <td ><?php echo Html::encode(number_format($val['today_get_loan_total'])); ?></td>
                                <td ><?php echo Html::encode(number_format($val['today_finish_total'])); ?></td>
                                <td ><?php echo Html::encode(number_format($val['finish_total'])); ?></td>
                                <td ><?php echo Html::encode(sprintf("%.2f", $val['finish_total_rate']*100).'%'); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['late_fee_total'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['finish_late_fee'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(sprintf("%.2f", $val['finish_late_fee_rate']*100).'%'); ?></td>
                                <td ><?php echo Html::encode(number_format( $val['member_fee'] / 100,2)); ?></td>
                                <td ><?php echo Html::encode(number_format($val['operate_total'])); ?></td>
                                <td ><a href="<?=Url::to(['collection-statistics/loan-collection-outside-work-list','outside'=>$val['outside_id'],'loan_group'=>$val['loan_group'],'order_level'=>$val['order_level'],'compare'=>'true','this_month'=>date('Y-m',$val['created_at'])]);?>">趋势图</a></td>
                            </tr>
                        <?php endforeach;?>
                    <?php else:?>
                        <tr class="hover">
                            <td ><?php echo Html::encode($outsides[$val['outside_id']]); ?></td>
                            <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['loan_group']]); ?></td>
                            <td ><?php echo Html::encode(LoanCollectionOrder::$level[$value['order_level']]??'--');?></td>
                            <td ><?php echo Html::encode(number_format( $value['total_money'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format( $value['today_get_total_money'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format( $value['today_finish_total_money'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format( $value['finish_total_money'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format( ($value['total_money']-$value['finish_total_money']) / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format($value['loan_total'])); ?></td>
                            <td ><?php echo Html::encode(number_format($value['today_get_loan_total'])); ?></td>
                            <td ><?php echo Html::encode(number_format($value['today_finish_total'])); ?></td>
                            <td ><?php echo Html::encode(number_format($value['finish_total'])); ?></td>
                            <td ><?php echo Html::encode(sprintf("%.2f", $value['finish_total_rate']*100).'%'); ?></td>
                            <td ><?php echo Html::encode(number_format( $value['late_fee_total'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(number_format( $value['finish_late_fee'] / 100,2)); ?></td>
                            <td ><?php echo Html::encode(sprintf("%.2f", $value['finish_late_fee_rate']*100).'%'); ?></td>
                            <td ><?php echo Html::encode(number_format($value['operate_total'])); ?></td>
                            <td ><a href="<?=Url::to(['collection-statistics/loan-collection-outside-work-list','outside'=>$val['outside_id'],'loan_group'=>$val['loan_group'],'order_level'=>$val['order_level'],'compare'=>'true','this_month'=>date('Y-m',$val['created_at'])]);?>">趋势图</a></td>
                        </tr>
                    <?php endif;?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else:?>
            <?php foreach ($outside_data as $one_outside): ?>
                <?php foreach ($one_outside as $key=>$val): ?>
                    <tr class="hover">
                        <td ><?php echo Html::encode($outsides[$val['outside_id']]); ?></td>
                        <td ><?php echo Html::encode(LoanCollectionOrder::$level[$val['loan_group']]); ?></td>
                        <td ><?php echo Html::encode(LoanCollectionOrder::$level[$val['order_level']]??'--');?></td>
                        <td ><?php echo Html::encode(number_format( $val['total_money'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format( $val['today_get_total_money'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format( $val['today_finish_total_money'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format( $val['finish_total_money'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format( ($val['total_money']-$val['finish_total_money']) / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format($val['loan_total'])); ?></td>
                        <td ><?php echo Html::encode(number_format($val['today_get_loan_total'])); ?></td>
                        <td ><?php echo Html::encode(number_format($val['today_finish_total'])); ?></td>
                        <td ><?php echo Html::encode(number_format($val['finish_total'])); ?></td>
                        <td ><?php echo Html::encode(sprintf("%.2f", $val['finish_total_rate']*100).'%') ?></td>
                        <td ><?php echo Html::encode(number_format( $val['late_fee_total'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format( $val['finish_late_fee'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(sprintf("%.2f", $val['finish_late_fee_rate']*100).'%'); ?></td>
                        <td ><?php echo Html::encode(number_format( $val['member_fee'] / 100,2)); ?></td>
                        <td ><?php echo Html::encode(number_format($val['operate_total'])); ?></td>
                        <td ><a href="<?=Url::to(['collection-statistics/loan-collection-outside-work-list','outside'=>$val['outside_id'],'loan_group'=>$val['loan_group'],'order_level'=>$val['order_level'],'compare'=>'true','this_month'=>date('Y-m',$val['created_at'])]);?>">趋势图</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif;?>
    </table>
    <?php if (empty($outside_data)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<br>
<p><?php echo Yii::T('common', 'Update Time: 8-23 2:42') ?></p>
<p><?php echo Yii::T('common', 'Accumulative repayment of cumulative orders: Cumulative distribution order due amount') ?></p>
<p><?php echo Yii::T('common', 'Total repayments due on the day: The total amount due for the repayment order on the day') ?></p>
<p><?php echo Yii::T('common', 'Total repayment due: The total amount due on the repayment order') ?></p>
<p><?php echo Yii::T('common', 'Remaining repayment amount: Accumulative repayment amount of orders distributed-Repayment repayment amount') ?></p>
<p><?php echo Yii::T('common', 'Cumulative total number of dispatch orders: cumulative order number of dispatch orders') ?></p>
<p><?php echo Yii::T('common', 'Number of repayments on the day: Number of repayment orders on the day') ?></p>
<p><?php echo Yii::T('common', 'Total repayment: Total repayment orders') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: total repayment amount / amount due-total amount') ?></p>
<p><?php echo Yii::T('common', 'Total Amount of Late Payment: The total amount of late payment due to repaid orders') ?></p>
<p><?php echo Yii::T('common', 'Amount of late payment: The remaining amount should be charged except for the due date, and the total amount of late payment shall be the minimum') ?></p>
<p><?php echo Yii::T('common', 'Late payment recovery rate: amount of late payment / total amount of late payment') ?></p>
<p><?php echo Yii::T('common', 'Total Recall Amount: The repayment amount of the order that has been repaid to that day') ?>（true_total_money）</p>
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
    //根据还款率排序：
    $('#finish_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="finish_total_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
    //根据迁徙率排序：
    $('#no_finish_rate').click(function(){
        $('#search_form').append('<input type="hidden" name="btn_sort" value="no_finish_rate">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
</script>

