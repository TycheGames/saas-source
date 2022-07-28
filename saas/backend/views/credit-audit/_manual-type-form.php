<?php
use backend\components\widgets\ActiveForm;

?>
    <style type="text/css">
        .item{ float: left; width: 400px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
    </style>

<?php $form = ActiveForm::begin(['id' => 'manual-type-form']); ?>
    <table class="tb tb2">
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'module_id')->dropDownList($moduleIds, ['prompt' => 'Select head_code']); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'type_name'); ?></td></tr>
        <tr class="noborder">type
            <td class="vtop rowform"><?php echo $form->field($model, 'type_name')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'status'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'status')->dropDownList(\common\models\manual_credit\ManualCreditModule::$status_list,['prompt' => 'Select status']); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr>
            <td colspan="5">
                <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>