<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\enum\City;
use common\models\user\UserActiveTime;
use common\helpers\CommonHelper;
use backend\models\Merchant;

$this->shownav('manage', 'menu_order_list');
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
<?php if($openSearchLabel):?>
    <script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
    <link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
    <script language="JavaScript">
        $(function () {
            $('.willing-select').SumoSelect({ placeholder:'select all'});
        });
    </script>
    <style>
        .greenRound{background: green;}
        .blueRound{background: blue;}
        .redRound{background: red;}
        .blackRound{background: black;}
        .dazzlingRound{background: black; animation: dazzline .5s linear infinite }
        @keyframes dazzline {
            0%{
                background: black;
            }
            30%{
                background: red;}
            60%{
                background: yellow;
            }
            90%{
                background: black;
            }
        }
        .willing-wrapper{
            display: flex;
            align-items: center;
        }
        .willing-status{
            position: relative;
            width: 10px;height: 10px;
            border-radius:50%;
            margin-right: 10px;
        }
        .willing-status > span{
            width: 2000%;
            position: absolute;
            top: -150%;
            left: -150%;
            display: none;

        }
        .willing-status:hover > span{
            display: block;
        }
        .SumoSelect{
            width:250px;
        }
    </style>
<?php endif;?>
<div>
    <div style="font-size: 12px;">
        <?php $form = ActiveForm::begin(['id' => 'search_form','method' => "get",'action'=>Url::to(['collection/collection-order-list']),'options' => ['style' => 'margin-top: 0px;margin-bottom:0;'] ]); ?>
        <span class="s_item">ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_collection_order_id', '')); ?>" name="loan_collection_order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">loan order ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:80px;">&nbsp;
        <?php if($isNotMerchantAdmin):?>
            <span class="s_item">merchant：</span><?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), Merchant::getMerchantId(false),array('prompt' => '-all merchant-')); ?>&nbsp;
        <?php endif; ?>
        <span class="s_item">loan person name：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" placeholder="" name="name" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">loan person phone：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_phone', '')); ?>" name="loan_phone" class="txt" style="width:110px;">&nbsp;
        <span class="s_item">collector：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('cuishou_name', '')); ?>" placeholder="" name="cuishou_name" class="txt" style="width:80px;">&nbsp;
<!--        <span class="s_item">overdue days：</span><input type="text" value="--><?php //echo Yii::$app->getRequest()->get('overdue_day', ''); ?><!--" name="overdue_day" class="txt" style="width:80px;" placeholder="ex:2 or 2-3">&nbsp;-->
        <?php if ($setRealNameCollectionAdmin): ?>
            <span class="s_item">collector real name：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('cuishou_real_name', '')); ?>" placeholder="" name="cuishou_real_name" class="txt" style="width:80px;">&nbsp;
        <?php endif; ?>
        <span class="s_item">overdue level：</span><?php echo Html::dropDownList('current_overdue_level', Html::encode(Yii::$app->getRequest()->get('current_overdue_level', 0)), LoanCollectionOrder::$level,array('prompt' => '-all level-')); ?>&nbsp;
        <span class="s_item">status：</span><?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', 0)), LoanCollectionOrder::$status_list,array('prompt' => '-all status-')); ?>
        <?php if($is_self):?>
            <span class="s_item">company：</span><?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', 0)), $companyList,[
                'prompt' => '--all company--',
                'onchange' => 'onCompanyChange($(this).val());']); ?>
            <span class="s_item"><span id="team">team：</span></span><?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->getRequest()->get('group_game', '')),$teamList ,
                [
                    'id' => 'group_game',
                    'prompt' => '--all team--',
                    'onchange' => 'onGroupGameChange($(this).val());']); ?>
            <span class="s_item">overdue level：</span><?php echo Html::dropDownList('current_overdue_level', Html::encode(Yii::$app->getRequest()->get('current_overdue_level', 0)), LoanCollectionOrder::$level,['prompt' => '-all level-','onchange' => 'onLevelChange($(this).val());']); ?>&nbsp;
            <span class="s_item">collector：</span><?php echo Html::dropDownList('admin_user_id', Html::encode(Yii::$app->getRequest()->get('admin_user_id', '')),$adminUserList,
                [
                    'id' => 'admin_user_id',
                    'prompt' => '--all collector--'
                ]
            ); ?>
        <?php endif;?>
        <br/>
        <span class="s_item">old customer：</span><?php echo Html::dropDownList('customer_type', Html::encode(Yii::$app->getRequest()->get('customer_type', '')),LoanCollectionOrder::$customer_types ,array('prompt' => '-all-')); ?>
        <span class="s_item">overdue day：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">is finish：</span><?php echo Html::dropDownList('is_finish', Html::encode(Yii::$app->getRequest()->get('is_finish', '')),[1 => 'finish',0 => 'no_finish'] ,array('prompt' => '-all-')); ?>
        <span class="s_item">finish principal interest：</span><?php echo Html::dropDownList('is_finish_principal_interest', Html::encode(Yii::$app->getRequest()->get('is_finish_principal_interest', '')),[1 => 'finish',0 => 'no finish'] ,array('prompt' => '-all-')); ?>
        <span class="s_item">collection time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_last_collection_time', '')); ?>" name="s_last_collection_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_last_collection_time', '')); ?>"  name="e_last_collection_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <span class="s_item">input time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_input_time', '')); ?>" name="s_input_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_input_time', '')); ?>"  name="e_input_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <span class="s_item">repayment complete time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_true_pay_time', '')); ?>" name="s_true_pay_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_true_pay_time', '')); ?>"  name="e_true_pay_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <br/>
        <span class="s_item">dispatch collector time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_dispatch_time', '')); ?>" name="s_dispatch_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_dispatch_time', '')); ?>"  name="e_dispatch_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <span class="s_item">dispatch company time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_dispatch_outside_time', '')); ?>" name="s_dispatch_outside_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        to <input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_dispatch_outside_time', '')); ?>"  name="e_dispatch_outside_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <!--        &nbsp;<input type="checkbox" name="is_summary" value="1"  --><?php //if(Yii::$app->request->get('is_summary', '0')==1):?><!-- checked --><?php //endif; ?><!-- > 显示汇总(勾选后，查询变慢)&nbsp;&nbsp;&nbsp;-->
        <span class="s_item">Residential State：</span><?php echo Html::dropDownList('state', Html::encode(Yii::$app->getRequest()->get('state', '')),City::getAllStateName() ,
            [
                    'prompt' => '--all--',
                    'onchange' => 'onStateChange($(this).val());']); ?>
        <span class="s_item">Residential City：</span><?php echo Html::dropDownList('city', Html::encode(Yii::$app->getRequest()->get('city', '')),(isset($city[Yii::$app->getRequest()->get('state', '')]['children']) ? array_column($city[Yii::$app->getRequest()->get('state', '')]['children'],'value') : []) ,array('id' => 'city','prompt' => '--all--')); ?>
        <?php if($openSearchLabel):?>
            <?php
            $willing_blinker_list = [];
            $colorArr = UserActiveTime::$colorMap;
            foreach ($colorArr as $key => $val){
                $willing_blinker_list[$key] = '<div class="willing-wrapper"><div class="'.$val[0].'Round willing-status"></div>'.$val[1].'</div>';
            }
            ?>
            <span class="s_item">Willing Blinker： <?= Html::dropDownList('willing_blinker', CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('willing_blinker', [])), $willing_blinker_list,['class' => 'form-control willing-select', 'multiple' => 'multiple']); ?></span>&nbsp;&nbsp;
        <?php endif;?>
        <input type="hidden" name="search_submit" value="search">
        <button type="submit" name="search_submit" value="search" class="btn btn-success btn-xs">search</button>
        <?php if($strategyOperating):?>
        <input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportData');return true;" class="btn btn-success btn-xs" />
        <?php endif;?>
        <?php $form = ActiveForm::end(); ?>
    </div>
</div>
<div style="    padding-top: 0;">
    <table class="tb tb2 fixpadding" id="info_table">
        <tr class="header">
            <th>Choose</th>
            <th class="hidden-xs hidden-sm">ID</th>
            <th>Order id</th>
            <th>Name</th>
            <th>OldCustomer</th>
            <th>Repay Count</th>
            <th>Phone</th>
            <th>Money</th>
            <th><a id="overdue_day_up">up</a>|Overdue days|<a id="overdue_day_down">down</a></th>
            <th>Overdue fee</th>
            <th>Scheduled Payment Amount</th>
            <th  class="hidden-xs hidden-sm  hidden-md">Should repayment time</th>
            <th>Overdue level</th>
            <th>Status</th>
            <th>promise repayment time</th>
            <th  class="hidden-xs hidden-sm">Repayment status</th>
            <th  class="hidden-xs hidden-sm">Last collection time</th>
            <th>Record count</th>
            <th>Repaid amount</th>
            <th  class="hidden-xs hidden-sm  hidden-md"><a id="closing_time_up">up</a>Repayment complete time<a id="closing_time_down">down</a></th>
            <th>next loan suggest</th>
            <th  class="hidden-xs hidden-sm ">Current collector</th>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th  class="hidden-xs hidden-sm ">collector real name</th>
            <?php endif; ?>
            <th>Company</th>
            <th  class="hidden-xs hidden-sm hidden-md">Dispatch Collector time</th>
            <th  class="hidden-xs hidden-sm hidden-md">Dispatch Company time</th>
            <?php if($openSearchLabel):?>
            <th>Willing Blinker</th>
            <?php endif;?>
            <th>
                operation
            </th>
        </tr>
        <?php foreach ($loan_collection_list as $value): ?>
            <tr class="hover">
                <td><input type="checkbox" name="ids[]" value="<?=$value['id']?>"></td>
                <td  class="hidden-xs hidden-sm"><?php echo Html::encode($value['id']); ?></td>
                <td><?php echo Html::encode($value['user_loan_order_id']);?></td>
                <td><?php echo Html::encode($value['name']); ?></td>
                <td><?php echo LoanPerson::$customer_type_list[$value['customer_type']] ?? '-'; ?></td>
                <td><?php echo $repayCount[$value['user_id']]['count'] ?? 0; ?></td>
                <td><div style="width: 100px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['phone']); ?></span></div></td>
                <td><?php echo Html::encode(($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100); ?></td>
                <td><?php echo Html::encode($value['overdue_day']); ?></td>
                <td><?php echo Html::encode($value['overdue_fee']/100); ?></td>
                <td><?php echo Html::encode(($value['total_money'] -  $value['true_total_money'] - $value['coupon_money'] - $value['delay_reduce_amount'])/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time']); ?></td>
                <td><?php echo LoanCollectionOrder::$level[$value['current_overdue_level']]; ?></td><!--催收分组-->
                <td ><?php echo isset(LoanCollectionOrder::$status_list[$value['status']])?LoanCollectionOrder::$status_list[$value['status']]:""  ; ?></td>
                <td><?php echo $value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-'; ?></td>
                <td  class="hidden-xs hidden-sm"><?php echo isset(UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']])?UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']]:""  ; ?></td>
                <td  class="hidden-xs hidden-sm"><?php echo empty($value['last_collection_time'])?"--":date("m/d",$value['last_collection_time']).' '.date("H:i",$value['last_collection_time']); ?></td>
                <td><?php echo Html::encode($recordCount[$value['id']] ?? 0); ?></td>
                <td><?php echo Html::encode($value['true_total_money']/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['closing_time'])?"--":date("Y-m-d H:i",$value['closing_time']); ?></td>
                 <td><?php echo empty($value['next_loan_advice']) ? LoanCollectionOrder::$next_loan_advice[0] : LoanCollectionOrder::$next_loan_advice[$value['next_loan_advice']]; ?></td>
                <td  class="hidden-xs hidden-sm "><?php echo Html::encode(empty($value['username']) ? '--':$value['username']) ;?></td><!--当前催收人-->
                <?php if ($setRealNameCollectionAdmin): ?>
                    <td  class="hidden-xs hidden-sm "><?php echo Html::encode(empty($value['real_name']) ? '--':$value['real_name']) ;?></td><!--当前催收人-->
                <?php endif; ?>
                <td><?php echo Html::encode($companyList[$value['outside']] ?? '--'); ?>
                </td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['dispatch_time'])?"--":date("y/m/d H:i",$value['dispatch_time']); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['dispatch_outside_time'])?"--":date("y/m/d H:i",$value['dispatch_outside_time']); ?></td>
                <?php if($openSearchLabel):?>
                <td class="willing-wrapper">
                    <?php
                    $arr = UserActiveTime::colorBlinkerShow($value);
                    foreach ($arr as $val): ?>
                        <div class="<?=$val[0] ?>Round willing-status">
                            <span><?=Html::encode($val[1]) ?></span>
                        </div>
                    <?php endforeach;?>
                </td>
                <?php endif;?>
                <td>
                <?php if($value['isCanReduce']) :?>
                    <a href="<?php echo Url::to(['repay-order/apply-reduce', 'id' => $value['id']]);?>">Apply reduce</a>
                <?php endif;?>
                <?php if($value['current_collection_admin_user_id'] > 0):?>
                    <?php if($value['status'] ==LoanCollectionOrder::STATUS_COLLECTION_FINISH): ?>
                        <a style="color: gray;" href="<?php echo Url::to(['collection/collection-record-list','order_id'=>$value['id']]); ?>">Collection record</a>
                    <?php elseif($value['status'] == LoanCollectionOrder::STATUS_STOP_URGING):?>
                        Stop collection
                    <?php elseif($value['status'] == LoanCollectionOrder::STATUS_DELAY_STOP_URGING):?>
                        Delay Stop
                    <?php elseif( in_array($value['status'],LoanCollectionOrder::$collection_status)):?>
                        <a style="color: green;" href="<?php echo Url::to(['work-desk/collection-view','order_id'=>$value['id'],'loan_order_id'=>$value['user_loan_order_id']]); ?>">Collection</a>
                    <?php endif; ?>
                <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <label><input type="checkbox" id="allchecked"><span>check all</span></label>
    <button id="huishou_button">Batch recycle</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</div>

<?php if (empty($loan_collection_list)): ?>
    <div class="no-result">No record</div>
<?php else:?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<?php endif;?>
<script type="text/javascript">
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
    $('#closing_time_up').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="D.closing_time">')
        $('#search_form').append('<input type="hidden" name="sort_val" value="1">')
        $('#search_form').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#search_form').submit();
    });

    $('#closing_time_down').click(function(){
        $('#search_form').append('<input type="hidden" name="sort_key" value="D.closing_time">')
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
<script>
    var city = <?= json_encode($city,JSON_UNESCAPED_UNICODE);?>;
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
    $('#huishou_button').click(function(){
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
        if(!confirm('Do you confirm to recycle these orders ?')){
            return false;
        }
        if (ids.length > 0) {
            var url = "index.php?r=collection/loan-collection-back&ids="+ids.join();
            window.location = url;
        };
        return false;
    });
    //催收方式切换
    function onStateChange(value)
    {
        var cityHtml = '<option value="">--all--</option>';
        if(value == ''){
            $('#city').html(cityHtml);
            return;
        }
        //console.log(city[value].children);
        $.each(city[value].children, function(i,item){
            cityHtml += '<option value="'+i+'">'+item.value+'</option>';
        });
        $('#city').html(cityHtml);

    }

    //公司切换
    function onCompanyChange(outside)
    {
        $('#admin_user_id').html('');
        $('#admin_user_id').append('<option value>--all collector--</option>');
        if(outside){
            $("#team").html('load：');
            $.ajax({
                url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
                type:"get",
                dataType:"json",
                data:{outside:outside},
                success:function(res){
                    var htmlStr = '<select name="group_game"><option value=>--all team--</option>';
                    $.each(res,function(i,val){
                        htmlStr += '<option value='+i+'>'+val+'</option>';
                    });

                    htmlStr+='</select>';
                    $("#group_game").html(htmlStr);
                    $("#team").html('team：');
                }
            });
        }else{
            $('#group_game').html('');
            $('#group_game').append('<option value>--all team--</option>');
        }
    }

    function onGroupGameChange(group_game) {
        var outside = $('select[name=outside]').val();
        var current_overdue_level = $('select[name=current_overdue_level]').val();
        $.post({
            url:"<?= Url::toRoute(['collection/collection-order-list']) ?>",
            dataType:"json",
            data:{action:'update',outside:outside,group_game:group_game,current_overdue_level:current_overdue_level},
            success:function(res){
                $('#admin_user_id').html('');
                var htmlStr = '<option value>-all collector-</option>';
                $.each(res,function(i,val){
                    htmlStr += '<option value='+i+'>'+val+'</option>';
                });
                $('#admin_user_id').append(htmlStr);
            }
        });
    }

    function onLevelChange(current_overdue_level) {
        var outside = $('select[name=outside]').val();
        var group_game = $('select[name=group_game]').val();
        $.post({
            url:"<?= Url::toRoute(['collection/collection-order-list']) ?>",
            dataType:"json",
            data:{action:'update',outside:outside,group_game:group_game,current_overdue_level:current_overdue_level},
            success:function(res){
                $('#admin_user_id').html('');
                var htmlStr = '<option value>-all collector-</option>';
                $.each(res,function(i,val){
                    htmlStr += '<option value='+i+'>'+val+'</option>';
                });
                $('#admin_user_id').append(htmlStr);
            }
        });
    }

    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>
