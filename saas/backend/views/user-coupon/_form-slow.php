<?php

use backend\components\widgets\ActiveForm;
use common\models\coupon\UserRedPacketsSlow;
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/admin.js?<?php echo time(); ?>; ?>" type="text/javascript"></script>


<?php $form = ActiveForm::begin(["id" => "red-packet-form"]); ?>
<table class="tb tb2">
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Coupon title') ?></td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'title')->textInput(); ?></td>
    </tr>
    <tr>
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'scenes to be used') ?></td>
    </tr>
    <tr class="noborder">
        <td class="vtop rowform" ><?php echo $form->field($model, 'use_case')->dropDownList(\common\helpers\CommonHelper::getListT(UserRedPacketsSlow::$use_case_arr)); ?></td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Coupon content') ?></td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?= $form->field($model, 'remark')->textArea(['rows' => '6']); ?></td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Serial number prefix') ?></td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'code_pre')->textInput(); ?></td>
        <td class="vtop tips2"><?php echo Yii::T('common', 'default') ?>: dkq </td>
    </tr>
    <tr id="amount_tr">
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'coupon amount') ?></td>
    </tr>
    <tr class="noborder" id="amount_td">
        <td class="vtop rowform">
            <?php echo $form->field($model, 'amount')->textInput(); ?>
        </td>
        <td class="vtop tips2 "></td>
    </tr>
    <tr>
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'Use validity') ?>(<?php echo Yii::T('common', 'day') ?>)</td>
    </tr>
    <tr class="noborder">
        <td class="vtop rowform" >
            <input type="radio" name="sel_date" value="0" <?php if(!$model->use_type):?> checked="checked" <?php endif;?> /> <?php echo Yii::T('common', 'day') ?>
            <input type="radio" name="sel_date" value="1" <?php if($model->use_type):?> checked="checked" <?php endif;?> /> <?php echo Yii::T('common', 'Start and end time') ?>
        </td>
    </tr>
    <tr class="showDay3" style="display: none;">
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'Limited days') ?></td>
    </tr>
    <tr class="noborder showDay3" style="display: none;">
        <td class="vtop rowform"><?php echo $form->field($model, 'user_use_days')->textInput(); ?></td>
        <td class="vtop tips2"><?php echo Yii::T('common', 'Tip: The user collects the time to start the calculation') ?> </td>
    </tr>
    <tr class="showTime3" style="display: none;">
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'Start time') ?></td>
    </tr>
    <tr class="noborder showTime3" style="display: none;">
        <td class="vtop rowform"><?php echo $form->field($model, 'use_start_time')->textInput(array("onfocus" => "WdatePicker({startDate:'%y/%M/%d 00:00:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true});")) ?></td>
    </tr>
    <tr class="showTime3" style="display: none;">
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'End time') ?></td>
    </tr>
    <tr class="noborder showTime3" style="display: none;">
        <td class="vtop rowform"><?php echo $form->field($model, 'use_end_time')->textInput(array("onfocus" => "WdatePicker({startDate:'%y/%M/%d 23:59:59',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true});")) ?></td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    $(function () {
        $("input:radio[name='sel_date']").change(function () {
            toggleChecked();
        });
    });
    function toggleChecked() {
        var checkVal = $("input:radio[name='sel_date']:checked").val();

        if (checkVal == "0") {
            $(".showDay3").show();
            $(".showTime3").hide();
        }
        if (checkVal == "1") {
            $(".showDay3").hide();
            $(".showTime3").show();
        }
    }
    window.onload = function () {
        <?php if($model->use_type):?>
            $(".showDay3").hide();
            $(".showTime3").show();
        <?php else:?>
            $(".showDay3").show();
            $(".showTime3").hide();
        <?php endif;?>
    }
</script>
