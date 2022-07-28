<?php

use common\models\manual_credit\ManualCreditRules;
use yii\helpers\Html;
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
<?= $this->render('/public/review-record-info', [
    'informationAll' => $informationAll,
]); ?>
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
                                        <td width="200px;"><?php echo Html::encode(ManualCreditRules::$question_pass_list[$question['res']]) ?></td>
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

<script>
$('.more_info').click(function(){
    if($(this).html() == '点击查看更多'){
        $(this).html('点击隐藏非高风险项');
        $('.hide').show();
    }else{
        $(this).html('点击查看更多');
        $('.hide').hide();
    }
});
</script>
