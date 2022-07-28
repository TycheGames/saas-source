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
use common\models\user\UserActiveTime;

$this->shownav('manage','menu_company_dispatch_order_list');
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
<div class="panel panel-default">
    <div class="panel panel-body" style="font-size: 12px;">
        <?php $form = ActiveForm::begin(['id' => 'search_form','method' => "get",'action'=>Url::to(['collection/company-dispatch-order-list']),'options' => ['style' => 'margin-top: 0px;margin-bottom:0;'] ]); ?>
        <span class="s_item">ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_collection_order_id', '')); ?>" name="loan_collection_order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">订单号ID：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">借款人姓名：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" placeholder="名字可能重复" name="name" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">借款人手机：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('loan_phone', '')); ?>" name="loan_phone" class="txt" style="width:110px;">&nbsp;
        <span class="s_item">逾期天数：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:80px;" placeholder="格式:2或2-3">&nbsp;
        <span class="s_item">逾期等级：</span><?php echo Html::dropDownList('current_overdue_level', Html::encode(Yii::$app->getRequest()->get('current_overdue_level', 0)), LoanCollectionOrder::$level,array('prompt' => '-all level-')); ?>
        <span class="s_item">是否新客：</span><?php echo Html::dropDownList('is_first', Html::encode(Yii::$app->getRequest()->get('is_first', '')), \common\models\order\UserLoanOrder::$first_loan_map,array('prompt' => '-all-')); ?>
        <br/>
         <span class="s_item">催收时间：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_last_collection_time', '')); ?>" name="s_last_collection_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_last_collection_time', '')); ?>"  name="e_last_collection_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">

        <span class="s_item">派单时间：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_dispatch_time', '')); ?>" name="s_dispatch_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_dispatch_time', '')); ?>"  name="e_dispatch_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
        <?php if($openSearchLabel):?>
            <?php
            $willing_blinker_list = [];
            $colorArr = UserActiveTime::$colorMap;
            foreach ($colorArr as $key => $val){
                $willing_blinker_list[$key] = '<div class="willing-wrapper"><div class="'.$val[0].'Round willing-status"></div>'.$val[1].'</div>';
            }
            ?>
            <span class="s_item">Willing Blinker： <?= Html::dropDownList('willing_blinker', \common\helpers\CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('willing_blinker', [])), $willing_blinker_list,['class' => 'form-control willing-select', 'multiple' => 'multiple']); ?></span>&nbsp;&nbsp;
        <?php endif;?>
        <input type="hidden" name="search_submit" value="search">
        <button type="submit" name="search_submit" value="search" class="btn btn-success btn-xs">search</button>
        <?php $form = ActiveForm::end(); ?>
    </div>
</div>
<div class="panel panel-body" style="    padding-top: 0;">
    <table class="tb tb2 fixpadding" id="info_table">
        <tr class="header">
            <th>选择</th>
            <th class="hidden-xs hidden-sm">ID</th>
            <th>订单ID</th>
            <th>姓名</th>
            <th>手机号</th>
            <th>是否新客</th>
            <th>金额</th>
            <th><a id="overdue_day_up">升序</a>|逾期天数|<a id="overdue_day_down">降序</a></th>
            <th>滞纳金</th>
            <th  class="hidden-xs hidden-sm  hidden-md">应还时间</th>
            <th>当前逾期等级</th>
            <th>催收状态</th>
            <?php if(LoanCollectionOrder::STATUS_COLLECTION_PROMISE == Yii::$app->request->get('status')):?>
                <th>承诺还款时间</th>
            <?php endif;?>
            <th  class="hidden-xs hidden-sm">还款状态</th>
            <th  class="hidden-xs hidden-sm"><a id="last_collection_time"><a id="last_collection_time_up">升序</a>|最后催收时间|<a id="last_collection_time_down">升序</a></th>
            <th>已还金额</th>
            <th  class="hidden-xs hidden-sm  hidden-md">实际还款时间</th>
            <th>下次贷款建议</th>
            <th  class="hidden-xs hidden-sm ">当前催收人</th>
            <th  class="hidden-xs hidden-sm hidden-md">派单时间</th>
            <?php if($openSearchLabel):?>
                <th>Willing Blinker</th>
            <?php endif;?>
        </tr>
        <?php foreach ($loan_collection_list as $value): ?>
            <tr class="hover">
                <td><input type="checkbox" name="ids[]" value="<?=$value['id']?>"></td>
                <td  class="hidden-xs hidden-sm"><?php echo Html::encode($value['id']); ?></td>
                <td><?php echo Html::encode($value['user_loan_order_id']);?></td>
                <td><?php echo Html::encode($value['name']); ?></td>
                <td><?= Html::encode($value['status'] == LoanCollectionOrder::STATUS_COLLECTION_FINISH ?  substr_replace($value['phone'],'****',3,4) : $value['phone']); ?></td>
                <td><?php echo \common\models\order\UserLoanOrder::$first_loan_map[$value['is_first']] ?? '-' ;?></td>
                <td><?php echo Html::encode(($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100); ?></td>
                <td><?php echo Html::encode($value['overdue_day']); ?></td>
                <td><?php echo Html::encode($value['overdue_fee']/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time']); ?></td>
                <td><?php echo LoanCollectionOrder::$level[$value['current_overdue_level']]; ?></td><!--逾期等级-->
                <td ><?php echo isset(LoanCollectionOrder::$status_list[$value['status']])?LoanCollectionOrder::$status_list[$value['status']]:""  ; ?></td>
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
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo Html::encode(empty($value['dispatch_time'])?"--":date("y/m/d",$value['dispatch_time'])); ?></td>
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
            </tr>
        <?php endforeach; ?>
    </table>
    <label><input type="checkbox" id="allchecked"><span>全选</span></label>
    <button id="dispatch_company_button">机构派分</button>&nbsp;&nbsp;&nbsp;&nbsp;
</div>

<?php if (empty($loan_collection_list)): ?>
    <div class="no-result">暂无记录</div>
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
    $('#dispatch_company_button').click(function(){
        var ids = [];
        $("input[name^=ids]").each(function() {
            if($(this).prop("checked")){
                ids.push($(this).val());
            }
        });
        if (ids.length == 0) {
            alert('请选择后再操作！');
            return false;
        }

        if (ids.length > 0) {
            var url = "index.php?r=collection/loan-collection-dispatch-company&ids="+ids.join();
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
