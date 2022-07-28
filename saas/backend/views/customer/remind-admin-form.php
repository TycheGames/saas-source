<?php

use backend\components\widgets\ActiveForm;
use yii\helpers\Url;

?>

<?php $form = ActiveForm::begin(["id" => "remind-admin-form"]); ?>
<table class="tb tb2 fixpadding">
    <?php if($isNotMerchantAdmin):?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'merchant') ?></td>
        <td><?php echo $form->field($model, 'merchant_id')->dropDownList($merchants, [
                'onchange' => 'getGroupList($(this).val())'
            ]); ?></td>
    </tr>
    <?php endif;?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Group') ?></td>
        <td><?php echo $form->field($model, 'remind_group')->dropDownList($remindGroups); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'username') ?></td>
        <td><?php echo $form->field($model, 'username')->textInput(); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
<script>
    var groups = <?php echo json_encode(\yii\helpers\ArrayHelper::htmlEncode($groups));?>;
    //方式切换
    function getGroupList(merchant_id)
    {
        var trElement = '<option value="0"><?=Yii::T('common', 'No grouping') ?></option>';
        if(groups[merchant_id]){
            $.each(groups[merchant_id], function(key,val){
                trElement += '<option value="'+ key +'">'+ val +'</option>';
                console.log(val);

            });
            if(trElement == ''){
                trElement = '<option value="">--</option>'
            }
        }
        $('#remindadmin-remind_group').html(trElement);
    }
</script>