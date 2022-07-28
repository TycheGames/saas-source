<?php
/**
 * Created by PhpStorm.
 * User: aidong
 * Date: 2019-03-11
 * Time: 15:00
 */
use backend\components\widgets\ActiveForm;
use common\models\product\ProductPeriodSetting;

/**
 * @var bool $isNotMerchantAdmin
 * @var array $packageSetting
 */
?>
<?php $form = ActiveForm::begin(["id"=>"goods-edit"]); ?>
<table class="tb tb2">
    <?php if ($isNotMerchantAdmin): ?>
        <tr class="noborder">
            <td class="td27" colspan="2">Is internal：</td>
        </tr>
        <tr class="noborder">
            <td>
                <?php echo $form->field($model, 'is_internal')->dropDownList(ProductPeriodSetting::$isInternal); ?>
            </td>
        </tr>

        <tr class="noborder">
            <td class="td27" colspan="2"><span class="highlight">*</span>merchant</td>
        </tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'merchant_id')->dropDownList(\backend\models\Merchant::getMerchantId(false));?></td>
        </tr>
    <?php endif; ?>
    <tr class="noborder">
        <td class="td27" colspan="2"><?php echo Yii::T('common', 'packName') ?>：</td>
    </tr>
    <tr>
        <td >
            <?php echo $form->field($model, 'package_name')->dropDownList($packageSetting); ?>
        </td>
    </tr>

    <tr class="noborder">
        <td class="td27" colspan="2"><span class="highlight">*</span><?php echo Yii::T('common', 'loan method') ?></td>
    </tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'loan_method')->dropDownList(ProductPeriodSetting::$loan_method_map, []);?></td>
    </tr>
    <tr class="noborder">
        <td class="td27" colspan="2"><span class="highlight">*</span><?php echo Yii::T('common', 'Loan term') ?></td>
    </tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'loan_term')->textInput();?></td>
    </tr>
    <tr class="noborder">
        <td class="td27" colspan="2"><span class="highlight">*</span><?php echo Yii::T('common', 'status') ?></td>
    </tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'status')->dropDownList(ProductPeriodSetting::$statusMap);?></td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
