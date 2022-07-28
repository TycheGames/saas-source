<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\order\UserLoanOrder;
use common\models\order\UserOrderLoanCheckLog;
use common\models\enum\verify\PassCode;
use common\models\manual_credit\ManualCreditRules;

/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_credit_log_list');
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
<?php if(!empty($manualQuestion)): ?>
    <table class="tb tb2 fixpadding">
        <?php foreach ($manualQuestion as $module_id => $module): ?>
            <tr>
                <td class="partition" colspan="2"><?php echo Html::encode($conversionRules['module_name'][$module_id])  ?> check question</td>
            </tr>
            <?php foreach ($module as $type_id => $type): ?>
                <?php foreach ($type as $id => $rule): ?>
                    <tr>
                        <th><?php echo Html::encode($conversionRules['type_name'][$type_id])?></th>
                        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
                            <table class="tb tb2 fixpadding">
                                <?php foreach ($rule as $key => $question): ?>
                                    <tr>
                                        <td><?php echo Html::encode($question['question']) ?></td>
                                        <td width="200px;"><?php echo ManualCreditRules::$question_pass_list[$question['res']] ?></td>
                                    </tr>
                                <?php endforeach;?>
                            </table>
                        </td>
                    </tr>
                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    </table>
<?php endif;?>

<table class="tb tb2 fixpadding">
    <tr>
        <td class="partition" colspan="2">Credit Remark</td>
    </tr>
    <tr>
        <th>Remark</th>
        <td style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;"><?php echo Html::encode($manualCreditLog->remark); ?></td>
    </tr>
</table>