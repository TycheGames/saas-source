<?php 
use backend\components\widgets\ActiveForm;
use common\models\coupon\UserRedPacketsSlow;

$form = ActiveForm::begin(['id' => 'update-packet-form']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <td colspan="15" style="color: red">
            <label><?php echo Yii::T('common', 'Note: Please be careful to modify the enable status') ?></label>
        </td>
    </tr>
    <tr>
        <td class="td24 label"><label><?php echo Yii::T('common', 'title') ?></label></td>
        <td ><?php echo $model->title; ?></td>
    </tr>
    <tr>
        <td class="td24 label"><label><?php echo Yii::T('common', 'status') ?></label></td>
        <td ><?php echo $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(UserRedPacketsSlow::$status_arr)); ?></td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="ok" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>