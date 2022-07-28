<?php

use common\models\CheckVersion;
use backend\components\widgets\ActiveForm;


$this->shownav('system', 'menu_check_version_config');


?>
<style>
    .rowform .txt{width:450px;height:25px;font-size:15px}
    .tb2 .txt{
        width: 200px;
        margin-right: 10px;
    }
</style>
<?php $form = ActiveForm::begin(); ?>
<table class="tb tb2">
    <tr><td class="td27" colspan="2">App牛信开关配置</td></tr>
    <tr class="noborder">
        <td colspan="2">
            <div style="width:780px;height:20px;margin:5px auto 40px 0;">
                <?php echo $form->field($model, 'value')->dropDownList(\common\models\message\NxPhoneLog::$status_map); ?>
            </div>
            <div class="help-block"></div>
        </td>
    </tr>
    <tr>
        <td colspan="1">
            <input type="submit" value="提交" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
<script>
    $(function () {

    })
</script>