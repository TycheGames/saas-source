<?php

use backend\components\widgets\ActiveForm;
use backend\models\remind\RemindSmsTemplate;

?>
<style type="text/css">
    .hover{height: 80px;}
    .show{color: green;border: 1px solid green;padding: 2px;}
    .bold{font-weight: bold;}
    .aisle_type select option{padding: 20px;}
</style>

<?php $form = ActiveForm::begin(["id" => "remind-template-form"]); ?>
<table class="tb tb2 fixpadding">
    <?php if(isset($model->id)): ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>remind group id</td>
        <td><?php echo $model->id; ?></td>
    </tr>
    <?php endif; ?>
    <?php if($isNotMerchantAdmin): ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'merchant') ?></td>
        <td><?php echo $form->field($model, 'merchant_id')->dropDownList($merchants); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>team leader username</td>
        <td><?php echo $form->field($model, 'team_leader_id')->textarea(['style' => 'width:200px']); ?></td>
    </tr>
    <?php endif; ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>group name</td>
        <td><?php echo $form->field($model, 'name')->textInput(['style' => 'width:200px']); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>