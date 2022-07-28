<?php

use common\models\CheckVersion;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use common\models\AppBanner;
use common\models\LoanPerson;

$this->shownav('system', 'menu_check_version_config');
$this->showsubmenu(Yii::T('common', 'App update management'), array(
    array(Yii::T('common', 'List of version update rules'), Url::toRoute('list'), 1),
    array(Yii::T('common', 'Add version update rule'), Url::toRoute('add'), 0),
));

?>
<style>
    .rowform .txt{width:450px;height:25px;font-size:15px}
    .tb2 .txt{
        width: 200px;
        margin-right: 10px;
    }
</style>
<?php $form = ActiveForm::begin(['id' => 'banner-form','options' => ['enctype' => 'multipart/form-data']]); ?>
<table class="tb tb2">

    <tr><td class="td27" colspan="2">app_market</td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'app_market'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Version matching rules') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'rules'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Whether to prompt upgrade') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php if(!isset($model->has_upgrade)){$model->has_upgrade = 1;}?>
            <?php echo $form->field($model, 'has_upgrade')->radioList([
                CheckVersion::HAS_UPGRADE_SUCCESS =>Yii::T('common', 'Prompt upgrade'),
                CheckVersion::HAS_UPGRADE_FALSE   =>Yii::T('common', 'Not prompted to upgrade'),
            ]);?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Whether to force upgrade') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php if(!isset($model->is_force_upgrade)){$model->is_force_upgrade = 1;}?>
            <?php echo $form->field($model, 'is_force_upgrade')->radioList([
                CheckVersion::FORCE_UPGRADE_SUCCESS =>Yii::T('common', 'Force upgrade'),
                CheckVersion::FORCE_UPGRADE_FALSE   =>Yii::T('common', 'Do not force upgrade'),
            ]);?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'ios version number') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'new_ios_version'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'android version number') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'new_version'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'android package address') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'ard_url'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Android package size') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php echo $form->field($model, 'ard_size'); ?>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Characteristic description') ?></td></tr>
    <tr class="noborder">
        <td colspan="2">
            <div style="width:780px;height:400px;margin:5px auto 40px 0;">
                <?php echo $form->field($model, 'new_features')->textarea(['style' => 'width:780px;height:295px;']); ?>
            </div>
            <div class="help-block"></div>
        </td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'status') ?></td></tr>
    <tr class="noborder">
        <td class="vtop tips2">
            <?php if(!isset($model->status)){$model->status = 1;}?>
            <?php echo $form->field($model, 'status')->radioList([
                1=>Yii::T('common', 'Enable'),
                0=>Yii::T('common', 'Disable'),
            ]); ?>
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