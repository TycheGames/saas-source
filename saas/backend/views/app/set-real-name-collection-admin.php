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
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Set an editable collection name for the real name of the collection administrator (enter the user name, multiple users are separated by commas, such as: zhangsan, lisi, wangfugui)') ?></td></tr>
    <tr class="noborder">
        <td colspan="2">
            <div style="width:780px;height:400px;margin:5px auto 40px 0;">
                <?php echo $form->field($model, 'value')->textarea(['style' => 'width:780px;height:295px;']); ?>
            </div>
            <div class="help-block"></div>
        </td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
<script>
    $(function () {
        
    })
</script>