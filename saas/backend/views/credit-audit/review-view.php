<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/5/16
 * Time: 11:29
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\order\UserLoanOrder;
use common\models\order\UserOrderLoanCheckLog;
use common\models\enum\verify\PassCode;
use common\models\enum\verify\RejectCode;
use common\models\manual_credit\ManualCreditRules;

/**
 * @var backend\components\View $this
 */
$this->shownav('staff', 'menu_ygb_zc_lqd_lb');
?>
<style>
    .person {
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
    }
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
    .mark {
        font-weight: bold;
        /*background-color:indianred;*/
        color:red;
    }

    .hide {
        display: none;
    }
</style>
<?= $this->render('/public/order-info', [
    'information' => $information,
]); ?>

<?= $this->render('/public/person-info', [
    'information' => $information,
]); ?>
<!--
<?//= $this->render('/public/credit-info', [
//    'information' => $information,
//]); ?>
-->
<!--
<?//= $this->render('/public/review-record-info', [
//    'informationAll' => $informationAll,
//]); ?>
-->
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>

    <table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">Second Mobile</th></tr>
    <tr>
        <td class="td24">Second Mobile</td>
        <td><?= Html::textInput('second_mobile', '', ['style' => 'width:150px;','placeholder' => 'second mobile number']); ?>&nbsp;(If not, ignore it)</td>
    </tr>
</table>
    <table class="tb tb2 fixpadding" id="creditreport">
        <tr><th class="partition" colspan="10">Show name</th></tr>
        <tr>
            <td style="padding: 2px;margin-bottom: 1px; border:1px solid darkgray;">
                <table style="margin-bottom: 0px" class="table">
                    <tr>
                        <th class="td24">Pan ocr</th>
                        <td class="mark">
                            <?php
                                if(isset($information['userLoanOrder']->userCreditechOCRPan->full_name)) {
                                    echo Html::encode($information['userLoanOrder']->userCreditechOCRPan->full_name);
                                }else if(isset($information['userPanReport']->full_name)){
                                    echo Html::encode($information['userPanReport']->full_name);
                                }
                            ?>
                        </td>
                        <th class="td24">pan verification</th>
                        <td class="mark">
                            <?php if(isset($information['userVerifyPanReport']->full_name)){
                                echo Html::encode($information['userVerifyPanReport']->full_name);

                            }
                            ?>
                        </td>
                        <th class="td24">Aadhaar ocr</th>
                        <td class="mark">
                            <?php
                                if(isset($information['userLoanOrder']->userCreditechOCRAadhaar->full_name)){
                                    echo Html::encode($information['userLoanOrder']->userCreditechOCRAadhaar->full_name);
                                }else if(isset($information['userAadhaarReport']->full_name)){
                                    echo Html::encode($information['userAadhaarReport']->full_name);
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="td24">User apply</th>
                        <td class="mark"><?= Html::encode($information['userBasicInfo']['full_name'] ?? '--');?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="tb tb2 fixpadding">

        <?php foreach ($allRules['data'] as $module_id => $module): ?>
            <tr>
                <td class="partition" colspan="2"><?php echo Html::encode($allRules['module_name'][$module_id]);  ?> check question</td>
            </tr>
            <?php foreach ($module as $type_id => $type): ?>
                <?php foreach ($type as $id => $rule): ?>
                <tr>
                    <th><?php echo Html::encode($allRules['type_name'][$type_id])?></th>
                    <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
                        <table class="tb tb2 fixpadding">
                        <?php if($rule['type'] == ManualCreditRules::TYPE_MULTI):?>
                            <tr>
                                <td align="center"><?php echo Html::encode($rule['rule_name']); ?></td>
                                <td align="center" width="200px;">pass count is <b><span id="count_<?=Html::encode($id)?>" style="color: red">0</span></b></td>
                            </tr>
                            <?php foreach (json_decode($rule['questions'],true) as $key => $question): ?>
                                <tr>
                                    <td><?php echo Html::encode($question); ?></td>
                                    <td width="200px;"><?php echo Html::radioList('question['.Html::encode($id).']['.Html::encode($key).']', 0, ManualCreditRules::$question_pass_list, ['class' => 'rule_'.Html::encode($id)]); ?></td>
                                </tr>
                            <?php endforeach;?>
                        <?php elseif ($rule['type'] == ManualCreditRules::TYPE_SINGLE):?>
                            <tr>
                                <td><?php echo Html::encode($rule['rule_name']) ?></td>
                                <td width="200px;"><?php echo Html::radioList('question['.Html::encode($id).']', 0, ManualCreditRules::$question_pass_list, ['class' => 'rule_'.Html::encode($id),'onchange' => 'getSelectResult($("input[name=\'question['.$id.']\']:checked").val(),'.Html::encode($id).')']); ?></td>
                            </tr>
                        <?php endif;?>
                        </table>
                    </td>
                </tr>
                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    </table>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15">Audit this project</th></tr>
        <tr>
            <td class="td24">Final Decision</td>
            <td><?php echo Html::radioList('operation', 1, [
                    '1' => 'check pass',
                    '2' => UserLoanOrder::$order_status_map[UserLoanOrder::STATUS_CHECK_REJECT]
                ]); ?></td>
        </tr>
        <tr>
            <td class="td24">Audit code：</td>
            <td class="pass"><?php echo Html::dropDownList('code', Html::encode(Yii::$app->getRequest()->get('code', '')), PassCode::getRemarkCode()); ?></td>
            <td class="reject" style="display: none"><?php echo Html::dropDownList('nocode', Html::encode(Yii::$app->getRequest()->get('code', '')), $remarkCode); ?></td>
        </tr>
        <tr class="loan_cation" style="display: none">
            <td>Apply limit：</td>
            <td>
                <?php echo Html::dropDownList('loan_action', UserOrderLoanCheckLog::WEEK_LOAN, UserOrderLoanCheckLog::$can_loan_type_list); ?>
            </td>
        </tr>
        <tr>
            <td class="td24">Audit remark：</td>
            <td><?= Html::textarea('audit_remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input id="submit_btn" value="submit" name="submit_btn" class="btn" style="align-items: flex-start;text-align: center;">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<div id="end"></div>
<script>
    //MR问题PASS个数
    <?php foreach ($allRules['pass_question_count'] as $k=>$v):?>
        mrDefault<?=$k?>();
        $(".rule_<?=$k?> input[type='radio']").click(mrDefault<?=$k?>);
        function mrDefault<?=$k?>(){
            var count = 0;
            $(".rule_<?=$k?> input[type='radio']").each(function (i,item) {
                // item.children 代表的是label array
                if(item.checked){
                    if($(this).val() == '1'){
                        count++;
                    }
                }
            });
            var str = '';
            if(count >= <?=$v?>){
                str = '<font color="green">'+ count +'</font>'
            }else{
                str= '<font color="red">'+ count +'</font>'
            }
            $('#count_<?=$k?>').html(str);
        }
    <?php endforeach;?>

    //operation选择更新
    operationDefault($(':radio[name="operation"]:checked').val());
    $('input[name="operation"]').click(function(){
        var code = $(this).val();
        operationDefault(code);
    });
    function operationDefault(code) {
        if(code == 1){
            $('.pass').show();
            $('.pass select').attr('name','code');
            $('.loan_cation').hide();
            $('.reject').hide();
            $('.reject_mark').hide();
            $('.reject select').attr('name','nocode');
        }else{
            $('.pass').hide();
            $('.pass select').attr('name','nocode');
            $('.reject').show();
            $('.loan_cation').show();
            $('.reject_mark').show();
            $('.reject select').attr('name','code');
        }
        $(':input[name="code"]').children('option').eq(0).attr("selected", "selected");
    }


    $("#submit_btn").click(function(){
        var code = $(":radio:checked").val();
        var text = "";
        if(code == 1){
            text = $('.pass select  option:selected').text();
        }else{
            text = $('.reject select  option:selected').text();
        }

        if (text.indexOf("Reasons to be noted") != -1) {
            alert("Reasons to be noted");
            return;
        }

        $("#review-form").submit();
    })

    function getSelectResult(val,id) {
        if(val == 2){
            alert('You can directly submit the audit results');
            document.getElementById("end").scrollIntoView();
            $(':radio[name="operation"]').attr("checked",'2');
            operationDefault(2);
            $('select[name="code"]').val(id);
            $(':input[name="audit_remark"]').focus();
        }
    }
</script>
