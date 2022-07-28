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
<?php $this->showtips(Yii::T('common', 'Tips'), [
    '<span class="bold">文案自动替换：</span><span class="show">#username# - 用户名</span> &nbsp;，&nbsp; <span class="show">#total_money# - 到期金额</span> &nbsp;，&nbsp; <span class="show">#should_repay_date# - 应还日期 格式：dd/mm/yyyy</span> &nbsp;，&nbsp; <span class="show">#remind_date# - 当前提醒日期 格式：dd/mm/yyyy</span>',
    Yii::T('common', 'Ordinary borrowing copywriting example: Dear # username #, the # total_money # loan you applied for expires at # should_repay_date #, please open the app to repay to avoid overdue fees. If repaid, please ignore.'),
]); ?>
<?php $form = ActiveForm::begin(["id" => "remind-template-form"]); ?>
<table class="tb tb2 fixpadding">
    <?php if(isset($model->id)): ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'templateId') ?></td>
        <td><?php echo $model->id; ?></td>
    </tr>
    <?php endif; ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'template name') ?></td>
        <td><?php echo $form->field($model, 'name')->textInput(['style' => 'width:200px']); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'package name') ?></td>
        <td><?php echo $form->field($model, 'package_name')->dropDownList($arrPackage); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'template content') ?></td>
        <td><?php echo $form->field($model, 'content')->textarea(); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'template status') ?></td>
        <td><?php echo $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(RemindSmsTemplate::$status_map), []); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>