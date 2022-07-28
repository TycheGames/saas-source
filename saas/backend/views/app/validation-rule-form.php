<?php

use backend\components\widgets\ActiveForm;
use common\models\enum\validation_rule\ValidationServiceProvider;
use common\models\enum\validation_rule\ValidationServiceType;

?>

<?php $form = ActiveForm::begin(["id" => "validation-form"]); ?>
<table class="tb tb2 fixpadding">
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Certification type') ?></td>
        <td><?php echo $form->field($model, 'validation_type')->dropDownList(\common\helpers\CommonHelper::getListT(ValidationServiceType::$map), []); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Number of triggers') ?></td>
        <td><?php echo $form->field($model, 'service_error')->textInput(); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Trigger time (minutes)') ?></td>
        <td><?php echo $form->field($model, 'service_time')->textInput(); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Current service provider') ?></td>
        <td><?php echo $form->field($model, 'service_current')->dropDownList(\common\helpers\CommonHelper::getListT(ValidationServiceProvider::$map), []); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Replace service provider') ?></td>
        <td><?php echo $form->field($model, 'service_switch')->dropDownList(\common\helpers\CommonHelper::getListT(ValidationServiceProvider::$map), []); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Whether to enable') ?></td>
        <td><?php echo $form->field($model, 'is_used')->dropDownList([1 => Yii::T('common', 'Enable'), 0 => Yii::T('common', 'Disable')]); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
