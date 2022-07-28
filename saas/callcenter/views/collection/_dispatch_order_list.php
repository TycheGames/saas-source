<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;
use common\models\order\UserLoanOrderRepayment;

$this->shownav('manage', 'menu_dispatch_order_list');
?>
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>

<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $this->baseUrl ?>/bootstrap/js/bootstrap.min.js"></script>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<style>
    .tb2 th{ font-size: 12px;}
    .container{
        width: 100%;
    }
    .panel{
        margin-bottom: 0;
    }
    span.s_item{
        margin-left: 10px;
    }
    .pagination{
        margin-top: 0;
    }
    tr, td{
        height: 41px;
    }

</style>
<div class="panel panel-default">
    <div class="panel panel-body" style="font-size: 12px;">
        <?php $form = ActiveForm::begin(['id' => 'search_form','method' => "get",'action'=>Url::to(['collection/dispatch-order-list']),'options' => ['style' => 'margin-top: 0px;margin-bottom:0;'] ]); ?>
        <span class="s_item">ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_collection_order_id', '')); ?>" name="loan_collection_order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">loan order ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">loan person name：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" placeholder="" name="name" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">loan person phone：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_phone', '')); ?>" name="loan_phone" class="txt" style="width:110px;">&nbsp;
        <span class="s_item">collector：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('cuishou_name', '')); ?>" placeholder="" name="cuishou_name" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">overdue days：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:80px;" placeholder="ex:2 or 2-3">&nbsp;
        <span class="s_item">overdue level：</span><?php echo Html::dropDownList('current_overdue_level', Html::encode(Yii::$app->getRequest()->get('current_overdue_level', 0)), LoanCollectionOrder::$level,array('prompt' => '-all level-')); ?>&nbsp;
        <span class="s_item">is new customer：</span><?php echo Html::dropDownList('is_first', Html::encode(Yii::$app->getRequest()->get('is_first', '')), \common\models\order\UserLoanOrder::$first_loan_map,array('prompt' => '-all-')); ?>
        <?php if($is_self):?>
            <span class="s_item">company：</span><?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', 0)), $companyList,array('prompt' => '-all company-')); ?>
        <?php endif;?>
         <span class="s_item">collection time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_last_collection_time', '')); ?>" name="s_last_collection_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_last_collection_time', '')); ?>"  name="e_last_collection_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">

<!--        <span class="s_item">repayment complete time：</span><input type="text" value="--><?php //echo Yii::$app->getRequest()->get('s_true_pay_time', ''); ?><!--" name="s_true_pay_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">-->
<!--        to<input type="text" value="--><?php //echo Yii::$app->getRequest()->get('e_true_pay_time', ''); ?><!--"  name="e_true_pay_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">-->
        <br/>
        <span class="s_item">dispatch time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_dispatch_time', '')); ?>" name="s_dispatch_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_dispatch_time', '')); ?>"  name="e_dispatch_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<!--        &nbsp;<input type="checkbox" name="is_summary" value="1"  --><?php //if(Yii::$app->request->get('is_summary', '0')==1):?><!-- checked --><?php //endif; ?><!-- > 显示汇总(勾选后，查询变慢)&nbsp;&nbsp;&nbsp;-->
        <input type="hidden" name="search_submit" value="search">
        <button type="submit" name="search_submit" value="search" class="btn btn-success btn-xs">search</button>
        <?php $form = ActiveForm::end(); ?>
    </div>
</div>
<div class="panel panel-body" style="    padding-top: 0;">
    <table class="tb tb2 fixpadding" id="info_table">
        <tr class="header">
            <th>Choose</th>
            <th class="hidden-xs hidden-sm">ID</th>
            <th>Order id</th>
            <th>Name</th>
            <th>is new customer</th>
            <th>Phone</th>
            <th>Money</th>
            <th><a id="overdue_day_up">up</a>|Overdue days|<a id="overdue_day_down">down</a></th>
            <th>Overdue fee</th>
            <th  class="hidden-xs hidden-sm  hidden-md">Should repayment time</th>
            <th>Overdue level</th>
            <?php if(LoanCollectionOrder::STATUS_COLLECTION_PROMISE == Yii::$app->request->get('status')):?>
                <th>Promise repayment time</th>
            <?php endif;?>
            <th  class="hidden-xs hidden-sm">Repayment status</th>
            <th  class="hidden-xs hidden-sm"><a id="last_collection_time"><a id="last_collection_time_up">up</a>|Last collection time|<a id="last_collection_time_down">down</a></th>
            <th>Repaid amount</th>
            <th  class="hidden-xs hidden-sm  hidden-md">Repayment complete time</th>
            <th>next loan suggest</th>
            <th  class="hidden-xs hidden-sm ">Current collector</th>
            <th>Company</th>
            <th  class="hidden-xs hidden-sm hidden-md">Dispatch time</th>
        </tr>
        <?php foreach ($loan_collection_list as $value): ?>
            <tr class="hover">
                <td><input type="checkbox" name="ids[]" value="<?=$value['id']?>"></td>
                <td  class="hidden-xs hidden-sm"><?php echo Html::encode($value['id']); ?></td>
                <td><?php echo Html::encode($value['user_loan_order_id']);?></td>
                <td><?php echo Html::encode($value['name']); ?></td>
                <td><?= Html::encode($value['status'] == LoanCollectionOrder::STATUS_COLLECTION_FINISH ?  substr_replace($value['phone'],'****',3,4) : $value['phone']); ?></td>
                <td><?php echo \common\models\order\UserLoanOrder::$first_loan_map[$value['is_first']] ?? '-';?></td>
                <td><?php echo Html::encode(($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100); ?></td>
                <td><?php echo Html::encode($value['overdue_day']); ?></td>
                <td><?php echo Html::encode($value['overdue_fee']/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time']); ?></td>
                <td><?php echo LoanCollectionOrder::$level[$value['current_overdue_level']]; ?></td><!--逾期等级-->
                <?php if(isset($value['red']) && $value['red'] == 1):?>
                    <td style="color:red;font-weight: bold"><?php echo $value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-'; ?></td>
                <?php elseif(isset($value['red']) && $value['red'] == 2):?>
                    <td><?php echo $value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-'; ?></td>
                <?php endif;?>
                <td  class="hidden-xs hidden-sm"><?php echo isset(UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']])?UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']]:""  ; ?></td>
                <td  class="hidden-xs hidden-sm"><?php echo empty($value['last_collection_time'])?"--":date("m/d",$value['last_collection_time']).' '.date("H:i",$value['last_collection_time']); ?></td>
                <td><?php echo Html::encode($value['true_total_money']/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['closing_time'])?"--":date("Y-m-d H:i",$value['closing_time']); ?></td>
                 <td><?php echo empty($value['next_loan_advice']) ? LoanCollectionOrder::$next_loan_advice[0] : LoanCollectionOrder::$next_loan_advice[$value['next_loan_advice']]; ?></td>
                <td  class="hidden-xs hidden-sm "><?php echo Html::encode(empty($value['username']) ? '--' : $value['username']);?></td><!--当前催收人-->
                <td><?php echo Html::encode($companyList[$value['outside']] ?? '--'); ?>
                </td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['dispatch_time'])?"--":date("y/m/d H:i",$value['dispatch_time']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <label><input type="checkbox" id="allchecked"><span>check all</span></label>
    <button id="zhipai_button">dispatch</button>&nbsp;&nbsp;&nbsp;&nbsp;

</div>

<?php if (empty($loan_collection_list)): ?>
    <div class="no-result">No record</div>
<?php else:?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<?php endif;?>

<script>
    $("#allchecked").click(function(){  
        if(this.checked){    
            $("input[name^=ids]").each(function() {  
                $(this).prop("checked", true); 
            });  
        }else{
             $("input[name^=ids]").each(function() {  
                $(this).prop("checked", false); 
            }); 
        }
    }); 
    $('#submit_button').click(function(){
        $("input[name^=ids]").each(function() { 
                if($(this).prop("checked")){
                    $('#allchecked_form').submit();
                }
            });
        return false;
    });
    $('#zhipai_button').click(function(){
        var ids = [];
        $("input[name^=ids]").each(function() {
            if($(this).prop("checked")){
                ids.push($(this).val());
            }
        });
        if (ids.length == 0) {
            alert('Please check before operation！');
            return false;
        }

        if (ids.length > 0) {
            var url = "index.php?r=collection/loan-collection-dispatch&ids="+ids.join();
            window.location = url;
        };
        return false;
    });
</script>
<script type="text/javascript">
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#search_form').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">');
        $('#search_form').submit();
    });

    $('#overdue_day_up').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="D.overdue_day">')
        $('#search_form').append('<input type="hidden" name="sort_val" value="1">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    $('#overdue_day_down').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="D.overdue_day">')
        $('#search_form').append('<input type="hidden" name="sort_val" value="0">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    $('#last_collection_time_up').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="A.last_collection_time">')
        $('#search_form').append('<input type="hidden" name="sort_val" value="1">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    $('#last_collection_time_down').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="A.last_collection_time">')
        $('#search_form').append('<input type="hidden" name="sort_val" value="0">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });
</script>

