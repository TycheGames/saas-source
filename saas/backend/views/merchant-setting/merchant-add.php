<?php

use backend\components\widgets\ActiveForm;
use backend\models\Merchant;
?>

<?php $form = ActiveForm::begin(["id" => "merchant-add-form"]); ?>
<table class="tb tb2">
    <tr><td class="label" ><?php echo Yii::T('common', 'Merchant name') ?></td>
        <td ><?php echo $form->field($model, 'name')->textInput(); ?></td>
    </tr>

    <tr><td class="label" ><?php echo Yii::T('common', 'status') ?></td>
        <td ><?php echo $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(Merchant::$status_arr)); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', '是否隐藏通讯录') ?></td>
        <td ><?php echo $form->field($model, 'is_hidden_contacts')->dropDownList(Merchant::$is_hidden_arr); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', '是否隐藏紧急联系人') ?></td>
        <td ><?php echo $form->field($model, 'is_hidden_address_book')->dropDownList(Merchant::$is_hidden_arr); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', 'telephone') ?></td>
        <td ><?php echo $form->field($model, 'telephone')->textInput(); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', 'company name') ?></td>
        <td ><?php echo $form->field($model, 'company_name')->textInput(); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', 'company addr') ?></td>
        <td ><?php echo $form->field($model, 'company_addr')->textInput(); ?></td>
    </tr>
    <tr><td class="label" ><?php echo Yii::T('common', 'gst number') ?></td>
        <td ><?php echo $form->field($model, 'gst_number')->textInput(); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>NBFC</td>
        <td><?php echo $form->field($model, 'nbfc')->dropDownList(Merchant::$nbfc_map); ?></td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
