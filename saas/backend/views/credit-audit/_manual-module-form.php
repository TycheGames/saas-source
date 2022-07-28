<?php
use backend\components\widgets\ActiveForm;
use common\models\manual_credit\ManualCreditModule;

?>

    <style type="text/css">
        .item{ float: left; width: 300px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
    </style>

<?php $form = ActiveForm::begin(['id' => 'manual-module-form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'head_code'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'head_code')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'head_name'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'head_name')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'status'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(ManualCreditModule::$status_list), ['prompt' => Yii::T('common', 'Select status')]); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr>
            <td colspan="5">
                <input type="submit" value="submit" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>