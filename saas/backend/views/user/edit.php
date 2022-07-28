<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 16:06
 */
use backend\components\widgets\ActiveForm;
use common\models\enum\Gender;
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'loan-person-edit']); ?>
<table class="tb fixpadding">
    <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'edit user info') ?></th></tr>
    <tr>
        <td class="label" id="phone"><?php echo Yii::T('common', 'phone') ?>：</td>
        <td ><?php echo $form->field($loan_person, 'phone')->textInput(); ?><p id = "phone_msg"></p></td>
    </tr>
    <?php echo $form->field($loan_person, 'id')->hiddenInput(); ?>
    <tr>
        <td class="label" id="name"><?php echo Yii::T('common', 'username') ?>：</td>
        <td ><?php echo $form->field($loan_person, 'name')->textInput(); ?></td>
    </tr>
    <tr>
        <td class="label" id="aadhaar_number"><?php echo Yii::T('common', 'aadhaarNumber') ?>：</td>
        <td ><?php echo $form->field($loan_person, 'aadhaar_number')->textInput(); ?></td>
    </tr>
    <tr>
        <td class="label" id="pan_code">pan_code：</td>
        <td ><?php echo $form->field($loan_person, 'pan_code')->textInput(); ?></td>
    </tr>
    <tr>
        <td class="label" id="birthday"><?php echo Yii::T('common', 'birthday') ?>：</td>
        <td ><?php echo $form->field($loan_person, 'birthday', ['options' => ['style' => 'float:left;']])
                ->textInput(array("onfocus"=>"WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true});")); ?></td>
    </tr>
    <tr>
        <td class="label" id="property"><?php echo Yii::T('common', 'gender') ?>：</td>
        <td ><?php echo $form->field($loan_person, 'gender')->dropDownList(Gender::$map); ?></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <input type="submit" value="submit" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
