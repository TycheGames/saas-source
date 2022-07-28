
<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 16:06
 */
use backend\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

$this->shownav('system', 'menu_remind_setting_list');
$this->showsubmenu(Yii::T('common', 'Add remind Plan'), array(
    array('List', Url::toRoute(['customer/remind-setting-list']), 1),
));
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
    .txt{ width: 100px;}

    .tb2 .txt, .tb2 .txtnobd {
        width: 200px;
        margin-right: 10px;
    }
</style>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'remind-setting-add', 'options' => ['enctype' => 'multipart/form-data']]); ?>
<table class="tb tb2 fixpadding">
    <?php if($isNotMerchantAdmin): ?>
        <tr>
            <td class="label">Merchant</td>
            <td><?php echo Html::dropDownList('merchant_id','',\common\helpers\CommonHelper::getListT(\backend\models\Merchant::getMerchantId(false))); ?></td>
        </tr>
    <?php endif;?>
    <tr>
        <td class="label"><?php echo Yii::T('common', 'run time') ?>：</td>
        <td >
            <input type="text" value="" name="run_time" class="txt" onfocus="WdatePicker({lang:'en',startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" /></td>
    </tr>
    <tr>
        <td class="label"><?php echo Yii::T('common', 'plan date before day') ?>：</td>
        <td ><input name="plan_date_before_day" type="text" value=""></td>
    </tr>

    <tr class="submit" style="text-align: left">
        <td colspan="15" >
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn" id="submit_btn">&nbsp;&nbsp;&nbsp;
            <a href="javascript:history.go(-1)" class="btn back">back</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
