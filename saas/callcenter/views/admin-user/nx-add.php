<?php

use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminNxUser;
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/admin.js?<?php echo time(); ?>; ?>" type="text/javascript"></script>

<?php $form = ActiveForm::begin(["id" => "nx-add-form"]); ?>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'collector_id'); ?>
    <?php echo $form->field($accountModel, 'collector_id')->textInput(); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'nx_name'); ?>
    <?php echo $form->field($accountModel, 'nx_name')->textInput(); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'password'); ?>
    <?php echo $form->field($accountModel, 'password')->textInput(); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'status'); ?>
    <?php echo $form->field($accountModel, 'status')->dropDownList(AdminNxUser::$status_map, []); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'type'); ?>
    <?php echo $form->field($accountModel, 'type')->dropDownList(AdminNxUser::$type_map, []); ?>
</tr>

<tr>
    <td colspan="15">
        <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
    </td>
</tr>
<?php ActiveForm::end(); ?>
