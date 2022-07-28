<?php

use backend\components\widgets\ActiveForm;
?>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<?php $form = ActiveForm::begin(["id" => "add-quan-user-form", "method" => 'post']); ?>
<table class="tb tb2">
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'userId') ?>* </td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'user_id')->textarea(['placeholder' => '111
222
333','style' => 'height:500px;']); ?></td>
    </tr>
    <tr id='coupon_id_tr'><td class="td27" colspan="2"><?php echo Yii::T('common', 'Available coupon') ?> *</td></tr>
    <tr class="noborder" id='coupon_id_td'>
        <td class="vtop rowform" ><?php echo $form->field($model, 'coupon_id')->dropDownList(\common\helpers\CommonHelper::getListT($userRedPacketSlows)); ?></td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
