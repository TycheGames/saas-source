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
<?php echo $this->render('/loan-order/view', [
    'informationAll' => $informationAll,
    'information' => $information,
    'verification' => $verification
]); ?>
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'Bank card information') ?></th></tr>
        <tr>
            <th><?php echo Yii::T('common', 'username') ?></th>
            <th><?php echo Yii::T('common', 'Third Party Name') ?></th>
            <th>IFSC</th>
            <th><?php echo Yii::T('common', 'Bank Name') ?></th>
        </tr>
        <tr>
            <td><?php echo Html::encode($information['loanPerson']['name'] ?? '');?></td>
            <td><?php echo Html::encode($information['userBankAccount']['report_account_name']);?></td>
            <th><?php echo Html::encode($information['userBankAccount']['ifsc']);?></th>
            <th>
                <?php if(!empty($information['userBankAccount']['bank_name'])):;?>
                    <?=Html::encode($information['userBankAccount']['bank_name']); ?>
                <?php endif;?>
            </th>
        </tr>
    </table>
    <table class="tb tb2 fixpadding">

        <?php foreach ($allRules['data'] as $module_id => $module): ?>
            <tr>
                <td class="partition" colspan="2"><?php echo Html::encode($allRules['module_name'][$module_id])  ?> <?php echo Yii::T('common', 'check question') ?></td>
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
                                        <td align="center" width="200px;"><?php echo Yii::T('common', 'pass count is') ?> <b><span id="count_<?=Html::encode($id)?>" style="color: red">0</span></b></td>
                                    </tr>
                                    <?php foreach (json_decode($rule['questions'],true) as $key => $question): ?>
                                        <tr>
                                            <td><?php echo Html::encode($question) ?></td>
                                            <td width="200px;"><?php echo Html::radioList('question['.Html::encode($id).']['.Html::encode($key).']', 0, ManualCreditRules::$question_pass_list, ['class' => 'rule_'.Html::encode($id)]); ?></td>
                                        </tr>
                                    <?php endforeach;?>
                                <?php elseif ($rule['type'] == ManualCreditRules::TYPE_SINGLE):?>
                                    <tr>
                                        <td><?php echo Html::encode($rule['rule_name']) ?></td>
                                        <td width="200px;"><?php echo Html::radioList('question['.Html::encode($id).']', 0, ManualCreditRules::$question_pass_list, ['class' => 'rule_'.Html::encode($id)]); ?></td>
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
        <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'Audit this project') ?></th></tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Final Decision') ?></td>
            <td><?php echo Html::radioList('operation', 1, [
                    '1' => 'check pass',
                    '2' => UserLoanOrder::$order_status_map[UserLoanOrder::STATUS_CHECK_REJECT]
                ]); ?></td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Audit code') ?>：</td>
            <td class="pass"><?php echo Html::dropDownList('code', Yii::$app->getRequest()->get('code', ''), PassCode::getRemarkCode()); ?></td>
            <td class="reject" style="display: none"><?php echo Html::dropDownList('nocode', Yii::$app->getRequest()->get('code', ''), $remarkCode); ?></td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Audit remark') ?>：</td>
            <td><?= Html::textarea('audit_remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input id="submit_btn" type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn" style="align-items: flex-start;text-align: center;">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>

<script>
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
</script>