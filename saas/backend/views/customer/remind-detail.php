<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\order\UserLoanOrderRepayment;
use backend\models\remind\RemindOrder;
use callcenter\models\CollectorCallData;
use common\models\order\UserLoanOrder;

/**
 * @var backend\components\View $this
 */
$this->shownav('customer', 'menu_my_remind_order_list');
?>
<style>
    .table {
        max-width: 100%;
        width: 100%;
        border:1px solid #ddd;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
</style>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10"><?php echo Yii::T('common', 'Payment information') ?></th></tr>
    <tr>
        <td class="td21"><?php echo Yii::T('common', 'Repay Order NO') ?>：</td>
        <td width="200"><?php echo Html::encode($remindOrder['id']); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'name') ?>：</td>
        <td width="200"><?php echo Html::encode($remindOrder['name']); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'phone') ?>：</td>
        <td width="200"><a href="javascript:;" onclick="callPhone(<?php echo Html::encode($remindOrder['phone']);?>,<?php echo CollectorCallData::TYPE_ONE_SELF ?>)"><?php echo Html::encode($remindOrder['phone']); ?></a></td>
    </tr>

    <tr>
        <td class="td21"><?php echo Yii::T('common', 'Expected total amount of repayment') ?>：</td>
        <td ><?php echo sprintf("%0.2f",($remindOrder['principal']+$remindOrder['interests'])/100); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'Expected repayment interest') ?>：</td>
        <td ><?php echo sprintf("%0.2f",($remindOrder['interests'])/100); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'Expected repayment principal') ?>：</td>
        <td ><?php echo sprintf("%0.2f",$remindOrder['principal']/100); ?></td>
    </tr>

    <tr>
        <td class="td21"><?php echo Yii::T('common', 'Repayment status') ?>：</td>
        <td ><?php echo Yii::T('common', UserLoanOrderRepayment::$repayment_status_map[$remindOrder['status']]); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'Expected repayment time') ?>：</td>
        <td ><?php echo date('Y-m-d',$remindOrder['plan_repayment_time']); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'Actual repayment time') ?> ：</td>
        <td ><?php echo empty($remindOrder['closing_time'])?'':date('Y-m-d',$remindOrder['closing_time']); ?></td>
    </tr>
    <tr>
        <td class="td21"><?php echo Yii::T('common', 'Loan Product') ?>：</td>
        <td ><?= Html::encode($order->clientInfoLog['package_name'] ?? '--'); ?></td>
        <td class="td21"><?php echo Yii::T('common', 'From APP') ?>：</td>
        <td ><?php if($order->is_export == UserLoanOrder::IS_EXPORT_YES) {
                echo Html::encode(explode('_',$order->clientInfoLog['app_market'])[1] ?? '--');
            }else{
                echo Html::encode($order->clientInfoLog['package_name'] ?? '--');
            }
            ?></td>

        <?php if($order->is_export == UserLoanOrder::IS_EXPORT_YES): ?>
            <td class="td21">App url：</td>
            <td ><?php
                $fromApp = explode('_',$order->clientInfoLog['app_market'])[1] ?? '--';
                switch ($fromApp){
                    case 'cashbowl':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/cashbowl/cashbowl_offical.apk';
                        break;
                    case 'cashcow':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/cashcow/cashcow_offical.apk';
                        break;
                    case 'icredit':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/icredit/icredit_offical.apk';
                        break;
                    case 'needrupee':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/needrupee/needrupee_offical.apk';
                        break;
                    case 'rupeeplus':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/rupeeplus/rupeeplus_offical.apk';
                        break;
                    case 'topcash':
                        echo 'https://sashakt-rupee.oss-ap-south-1.aliyuncs.com/apk/topcash/topcash_offical.apk';
                        break;
                }
                ?>
            </td>
        <?php endif;?>
    </tr>
</table>
<?php if($remindOrder['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_NO):?>
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'Remind Result') ?></th></tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Whether reach') ?></td>
            <td><?php echo Html::radioList('is_reach', 1, [
                    '1' => 'reach',
                    '2' => 'no reach'
                ]); ?></td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Remind Result') ?>：</td>
            <td>
                <select name="remind_turn" id ="remind_turn" >
                    <?php foreach(RemindOrder::$remind_reach_return as $key => $item):?>
                        <option value="<?=Html::encode($key);?>"><?php echo Html::encode(Yii::T('common', $item));?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr class="payment_after_days" style="display: none">
            <td><?php echo Yii::T('common', 'After days') ?>：</td>
            <td>
                <?php echo Html::dropDownList('payment_after_days', 1, RemindOrder::$payment_after_days_map); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo Yii::T('common', 'Send sms') ?>：</td>
            <td>
                <?php echo Html::dropDownList('sms_template', 0, $templateList['downList'], [
                    'onchange' => 'onTemplateChange($(this).val())',
                ]); ?>
                &nbsp;&nbsp;&nbsp;<span id="sms_content">-</span>
            </td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Remind remark') ?>：</td>
            <td><?= Html::textarea('remind_remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input id="submit_btn" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn" style="align-items: flex-start;text-align: center;">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<?php endif;?>
<table class="tb tb2 fixpadding" id="creditreport">
    <tr><th class="partition" colspan="5"><?php echo Yii::T('common', 'Remind history') ?></th></tr>
    <tr>
        <td style=" padding: 2px;margin-bottom: 1px">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th><?php echo Yii::T('common', 'dispatch') ?> <?php echo Yii::T('common', 'userId') ?></th>
                    <th><?php echo Yii::T('common', 'Operator') ?> <?php echo Yii::T('common', 'userId') ?></th>
                    <th><?php echo Yii::T('common', 'Whether reach') ?></th>
                    <th><?php echo Yii::T('common', 'Reach result') ?></th>
                    <th><?php echo Yii::T('common', 'Send sms') ?></th>
                    <th><?php echo Yii::T('common', 'Remind remark') ?></th>
                    <th><?php echo Yii::T('common', 'Creation time') ?></th>
                </tr>
                <?php foreach ($remindLog as $log): ?>
                    <tr>
                        <td><?= Html::encode($log['customer_name'])?></td>
                        <td><?= Html::encode($log['operator_name'])?></td>
                        <td><?= in_array($log['remind_return'],RemindOrder::$remind_reach_return) ? Yii::T('common', 'reach') : Yii::T('common', 'no reach');?></td>
                        <td><?= Html::encode(RemindOrder::$remind_return_map_all[$log['remind_return']]);
                        echo Html::encode($log['remind_return'] == RemindOrder::REMIND_RETURN_PAYMENT_AFTER_DAYS ? ': '.$log['payment_after_days'].'days' : '')?></td>
                        <td><?= Html::encode($templateList['downList'][$log['sms_template']] ?? '-');?></td>
                        <td><?= Html::encode($log['remind_remark']);?></td>
                        <td><?= Html::encode(date("Y-m-d H:i:s",$log['created_at']));?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>
<script>
    var remind_reach_return = <?php echo json_encode(\common\helpers\CommonHelper::getListT(RemindOrder::$remind_reach_return));?>;
    var remind_no_reach_return = <?php echo json_encode(\common\helpers\CommonHelper::getListT(RemindOrder::$remind_no_reach_return));?>;
    var sms_template_content = <?php echo json_encode($templateList['contentList']);?>;

    $(document).ready(function(){
        afterDays();
    });

    //operation选择更新
    operationDefault($(':radio[name="is_reach"]:checked').val());
    $('input[name="is_reach"]').click(function(){
        var code = $(this).val();
        $('.payment_after_days').hide();
        operationDefault(code);
    });
    $("select[name='remind_turn']").change(afterDays);

    function afterDays() {
        if( $("select[name='remind_turn']").val() == 3 ){
            $('.payment_after_days').show();
        }else{
            $('.payment_after_days').hide();
        }
    }
    function operationDefault(code) {
        var trElement = '';
        if(code == 1){
            $.each(remind_reach_return, function(n,v){
                trElement += '<option value="'+ n +'">'+ v +'</option>';
            });
            if( $("select[name='remind_turn'] option:selected").val() == 3){
                $('.payment_after_days').show();
            }else{
                $('.payment_after_days').hide();
            }
        }else{
            $.each(remind_no_reach_return, function(n,v){
                trElement += '<option value="'+ n +'">'+ v +'</option>';
            });
            $('.payment_after_days').hide();
        }
        $('#remind_turn').html(trElement);
        $(':input[name="remind_turn"]').children('option').eq(0).attr("selected", "selected");
    }


    $("#submit_btn").click(function(){
        var code = $(":radio:checked").val();
        var text = "";
        if(code == 1){
            text = $('.reach select  option:selected').text();
        }else{
            text = $('.no_reach select  option:selected').text();
        }

        if (text.indexOf("Reasons to be noted") != -1) {
            alert("Reasons to be noted");
            return;
        }

        $("#review-form").submit();
    })

    function onTemplateChange(templateId)
    {
        if(templateId > 0)
        {
            $("#sms_content").html(sms_template_content[templateId]);
        }else{
            $("#sms_content").html('-');
        }
    }

    function callPhone(phone,type) {
        var data = {};
        data.phone = phone;
        data.type = type;
        var nx_phone = "<?= $nx_phone;?>";
        if(nx_phone == 0) {
            return false;
        }
        $.ajax({
            url: "<?= Url::toRoute(['customer/call-phone', 't' => time()]); ?>",
            type: 'get',
            dataType: 'json',
            data: data,
            async:false,
            success: function(data){
                if (data.code == 0) {
                    window.location.href = "sip:"+phone+","+data.orderid;
                } else {
                    alert(data.message);
                }
            },
            error: function(){
                alert('Please log in nx phone');
            }
        });
    }
</script>
