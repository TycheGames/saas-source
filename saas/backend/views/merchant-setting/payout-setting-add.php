<?php

use backend\components\widgets\ActiveForm;
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/admin.js?<?php echo time(); ?>; ?>" type="text/javascript"></script>


<?php $form = ActiveForm::begin(["id" => "account-add-form"]); ?>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'merchant_id'); ?>
    <?php echo $form->field($accountModel, 'merchant_id')->dropDownList(\backend\models\Merchant::getMerchantId(false)); ?>
</tr>
    <tr class="noborder">
        <?php echo $this->activeLabel($accountModel, 'name'); ?>
        <?php echo $form->field($accountModel, 'name')->textInput(); ?>
    </tr>
    <tr class="noborder">
        <?php echo $this->activeLabel($accountModel, 'remark'); ?>
        <?php echo $form->field($accountModel, 'remark')->textarea(); ?>
    </tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'account_id'); ?>
    <?php echo $form->field($accountModel, 'account_id')->dropDownList($accountMap); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'group'); ?>
    <?php echo $form->field($accountModel, 'group')->textInput(); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'weight'); ?>
    <?php echo $form->field($accountModel, 'weight')->textInput(); ?>
</tr>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'status'); ?>
    <?php echo $form->field($accountModel, 'status')->dropDownList($statusMap); ?>
</tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
        </td>
    </tr>
<?php ActiveForm::end(); ?>
