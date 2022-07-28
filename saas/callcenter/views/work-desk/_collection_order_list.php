<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\UserActiveTime;
use common\helpers\CommonHelper;

$this->shownav('workbench', 'menu_admin_order_list');
?>
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>

<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $this->baseUrl ?>/bootstrap/js/bootstrap.min.js"></script>

<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
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
    <div  style="font-size: 12px;">
        <?php $form = ActiveForm::begin(['method' => "get",'action'=>Url::to(['work-desk/admin-collection-order-list']),'options' => ['style' => 'margin-top: 0px;margin-bottom:0;'] ]); ?>
        <span class="s_item">ID：</span><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('loan_collection_order_id', '')); ?>" name="loan_collection_order_id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">OrderID：</span><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order id" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">CustomerName：</span><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" placeholder="user name" name="name" class="txt" style="width:80px;">&nbsp;
        <span class="s_item">Phone：</span><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('loan_phone', '')); ?>" name="loan_phone" class="txt" style="width:110px;">&nbsp;
        <span class="s_item">Status：</span><?= Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', 0)), LoanCollectionOrder::$status_list,array('prompt' => '-all-')); ?>
        <?php if($openSearchLabel):?>
            <?php
            $willing_blinker_list = [];
            $colorArr = UserActiveTime::$colorMap;
            foreach ($colorArr as $key => $val){
                $willing_blinker_list[$key] = '<div class="willing-wrapper"><div class="'.Html::encode($val[0]).'Round willing-status"></div>'.Html::encode($val[1]).'</div>';
            }
            ?>
            <span class="s_item">Willing Blinker： <?= Html::dropDownList('willing_blinker', CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('willing_blinker', [])), $willing_blinker_list,['class' => 'form-control willing-select', 'multiple' => 'multiple']); ?></span>&nbsp;&nbsp;
        <?php endif;?>
        <input type="hidden" name="search_submit" value="search">
        <button type="submit" name="search_submit" value="过滤" class="btn btn-success btn-xs">submit</button>
        <?php $form = ActiveForm::end(); ?>
    </div>
</div>
<script type="text/javascript">
    $('button[name=search_submit]').click(function(){
        $('#w0').submit();
        $(this).attr('disabled','true');
        $(this).css('background','#ddd');
    });
</script>

<div style="    padding-top: 0;">
    <?php $form = ActiveForm::begin([
            'action'=>'index.php?r=work-desk/loan-collection-outside-edit&id=0',
            'method' => "get",
            'id'=>"allchecked_form",
    ]);?>
    <table class="tb tb2 fixpadding watermark" id="info_table">
        <tr class="header">
            <th>Choose</th>
            <th class="hidden-xs hidden-sm">ID</th>
            <th>Order id</th>
            <th>Name</th>
            <th>phone</th>
            <th><a id="amount" href="javascript:;">Money</a></th>
            <th><a id="overdue_day" href="javascript:;">Overdue days</a></th>
            <th>Overdue fee</th>
            <th>Scheduled Payment Amount</th>
            <th class="hidden-xs hidden-sm  hidden-md">Should repayment time</th>
            <th>Collection status</th>
            <th>Promise repayment time</th>
            <th class="hidden-xs hidden-sm">Repayment status</th>
            <th class="hidden-xs hidden-sm"><a href="javascript:;" id="last_collection_time">Last collection time</a></th>
            <th>Repaid amount</th>
            <th class="hidden-xs hidden-sm  hidden-md"><a id="true_repayment_time"></a>Repayment complete time</th>
<!--            <th>Next loan proposal</th>-->
            <th class="hidden-xs hidden-sm  hidden-md">Repayment remark/User Comments</th>
            <th class="hidden-xs hidden-sm hidden-md"><a id="dispatch_time">Dispatch time</a></th>
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
                <td class="hidden-xs hidden-sm"><?= $value['id']; ?></td>
                <td><?= Html::encode($value['user_loan_order_id']);?></td>
                <td><?= Html::encode($value['name']); ?></td>
                <td><div style="width: 100px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=$value['phone']; ?></span></div></td>
                <td><?= Html::encode(($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100); ?></td>
                <td><?= Html::encode($value['overdue_day']); ?></td>
                <td><?= Html::encode($value['overdue_fee']/100); ?></td>
                <td><?= Html::encode(($value['total_money'] -  $value['true_total_money'] - $value['coupon_money'] - $value['delay_reduce_amount'])/100); ?></td>
                <td class="hidden-xs hidden-sm  hidden-md"><?= Html::encode(empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time'])); ?></td>
                <td ><?= Html::encode(isset(LoanCollectionOrder::$status_list[$value['status']])?LoanCollectionOrder::$status_list[$value['status']]:""  ); ?></td>
                <td><?= Html::encode($value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-'); ?></td>
                <td class="hidden-xs hidden-sm"><?= Html::encode(isset(UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']])?UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']]:""  ); ?></td>
                <td class="hidden-xs hidden-sm"><?= Html::encode(empty($value['last_collection_time'])?"--":date("m/d",$value['last_collection_time']).' '.date("H:i",$value['last_collection_time'])); ?></td>
                <td><?= Html::encode($value['true_total_money']/100); ?></td>
                <td  class="hidden-xs hidden-sm  hidden-md"><?php echo Html::encode(empty($value['closing_time'])?"--":date("Y-m-d H:i",$value['closing_time'])); ?></td>
                <?php if($value['next_loan_advice'] == LoanCollectionOrder::RENEW_DEFAULT):?>
                    <td>
                    <?php elseif ($value['next_loan_advice'] == LoanCollectionOrder::RENEW_PASS):?>
                        <td style="background: green;padding: 0;">
                    <?php elseif ($value['next_loan_advice'] == LoanCollectionOrder::RENEW_REJECT):?>
                        <td style="background: red">
                    <?php elseif ($value['next_loan_advice'] == LoanCollectionOrder::RENEW_CHECK):?>
                        <td style="background: yellow">
                    <?php endif;?>
                    <?php $renew = empty($value['next_loan_advice'])? 0:$value['next_loan_advice'];?>
                    <?php
                        if($value['status'] ==LoanCollectionOrder::STATUS_COLLECTION_FINISH || $value['cuishou_status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE) {
                            echo Html::dropDownList('renew', Html::encode($value['next_loan_advice']), LoanCollectionOrder::$next_loan_advice,['onchange'=>"suggest(this,{$value['id']},{$renew})"]);
                        }else{
                            echo Html::dropDownList('renew', Html::encode($value['next_loan_advice']), LoanCollectionOrder::$before_next_loan_advice,['onchange'=>"suggest(this,{$value['id']},{$renew})"]);
                        }
                    ?>
                    </td>
                <td class="hidden-xs hidden-sm  hidden-md"><?= empty($value['dispatch_time'])?"--":date("y/m/d",$value['dispatch_time']); ?></td>
                <?php if($openSearchLabel):?>
                    <td class="willing-wrapper">
                        <?php
                        $arr = UserActiveTime::colorBlinkerShow($value);
                        foreach ($arr as $val): ?>
                            <div class="<?=Html::encode($val[0]) ?>Round willing-status">
                                <span><?=Html::encode($val[1]) ?></span>
                            </div>
                        <?php endforeach;?>
                    </td>
                <?php endif;?>
                <td>
                    <?php if($value['isCanReduce']) :?>
                        <a href="<?php echo Url::to(['repay-order/apply-reduce', 'id' => $value['id']]);?>">Apply reduce</a>
                    <?php endif;?>
                    <?php if($value['status'] ==LoanCollectionOrder::STATUS_COLLECTION_FINISH): ?>
                        <a style="color: gray;" href="<?php echo Url::to(['work-desk/admin-record-list','order_id'=>$value['id']]); ?>">Collection record</a>
                    <?php elseif($value['status'] == LoanCollectionOrder::STATUS_STOP_URGING):?>
                        Stop collection
                    <?php elseif($value['status'] == LoanCollectionOrder::STATUS_DELAY_STOP_URGING):?>
                        Delay Stop
                    <?php elseif( in_array($value['status'],LoanCollectionOrder::$collection_status) ):?>
                        <a style="color: green;" href="<?php echo Url::to(['work-desk/collection-view','order_id'=>$value['id'],'loan_order_id'=>$value['user_loan_order_id']]); ?>">Collection</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php $form = ActiveForm::end();?>

</div>

<?php if (empty($loan_collection_list)): ?>
    <div class="no-result">no records</div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>
<script>
  function suggest(obj,id,pre_status){
    $('#myModal').find(".my_suggestion").html("【"+$(obj).find(":selected").html()+"】");
    $('#myModal').find("#collection_suggestion_id").val(id);
    $('#myModal').find("#collection_suggestion_id_before").val(pre_status);
    $('#myModal').find("#collection_suggestion").val($(obj).val());
    $('#myModal').find(".modal_warning").css('display','none');
    $('#myModal').modal();
        // if(!confirmMsg('确认修改催收建议')){
        //     obj.value = pre_status;
        //     return false;
        // }

    }
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
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
    //根据【逾期天数】排序：
    $('#overdue_day').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="overdue_day">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });
    //根据【逾期等级】排序：
    $('#overdue_level').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="current_overdue_level">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });
    //根据【催收分组】排序：
    $('#overdue_group').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="current_overdue_group">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });

    //根据【实际还款时间】排序：
    $('#true_repayment_time').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="true_repayment_time">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });

    //根据【金额】排序：
    $('#amount').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="principal">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });

    //根据【最后催收时间】排序：
    $('#last_collection_time').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="last_collection_time">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });
    //根据【派单】排序：
    $('#dispatch_time').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="dispatch_time">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });
    //根据【已还金额】排序：
    $('#true_total_money').click(function(){
        $('#w0').append('<input type="hidden" name="btn_sort" value="true_total_money">')
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">')
        $('#w0').submit();
    });
</script>
<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">You chose：<span class="my_suggestion"></span></h4>
      </div>
      <div class="modal-body">
        <p>reason：</p>
        <textarea name="collection_suggestion_remark" id="collection_suggestion_remark" class="form-control"></textarea>
        <input type="hidden" name="collection_suggestion" id="collection_suggestion">
        <input type="hidden" name="collection_suggestion_id" id="collection_suggestion_id">
        <input type="hidden" id="collection_suggestion_id_before">
        <p class="modal_warning" style="color: red;display: none;">please input reason</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">cancel</button>
        <button type="button" id="btn_suggest" class="btn btn-primary">submit</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
    $(function(){
        $("#btn_suggest").click(function(){

            if(($('#collection_suggestion').val() == -1 || $('#collection_suggestion_id_before').val() == -1  ) && $.trim($('#collection_suggestion_remark').val()) == ''){
                // alert('请填写建议原因');
                $('.modal_warning').css('display','block');
                return;
            }
            var pre_status = $('#collection_suggestion_id_before').val();

            var url = '<?= Url::to(['work-desk/next-loan-advice']);?>';
            var params = {
                id : $('#collection_suggestion_id').val(),
                suggest : $('#collection_suggestion').val(),
                remark:$('#collection_suggestion_remark').val()
            };

            $.get(url,params,function(data){
                if(data.code == 0){
                    alert(data.message);
                    location.reload(true);
                }else{
                    obj.value = pre_status;
                    alert(data.message);
                }
            },'json');
        });
    });
</script>
