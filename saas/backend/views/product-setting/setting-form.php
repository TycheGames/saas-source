<?php

use backend\components\widgets\ActiveForm;
use backend\models\Merchant;
use common\models\product\ProductSetting;

$this->shownav('system', 'menu_product_setting');
/**
 * @var bool $isNotMerchantAdmin
 * @var array $packageSetting
 * @var array $periodList
 */
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
<?php $form = ActiveForm::begin(['id' => 'product-add', 'options' => ['enctype' => 'multipart/form-data']]); ?>
<table class="tb tb2 fixpadding">
    <?php if ($isNotMerchantAdmin): ?>
        <tr>
            <td class="label">merchant：</td>
            <td >
                <?php echo $form->field($model, 'merchant_id')->dropDownList(Merchant::getMerchantId(false)); ?>
            </td>
        </tr>
        <tr>
            <td class="label">Is internal：</td>
            <td>
                <?php echo $form->field($model, 'is_internal')->dropDownList(ProductSetting::$isInternal); ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <td class="label"><?php echo Yii::T('common', 'packName') ?>：</td>
        <td >

        <?php echo $form->field($model, 'package_name')->dropDownList($packageSetting); ?>
        </td>
    </tr>
    <tr>
        <td class="label">period type：</td>
        <td>
            <?php echo $form->field($model, 'period_id')->dropDownList($periodList); ?>
        </td>
    </tr>
    <tr>
        <td class="label">product name：</td>
        <td>
            <?php echo $form->field($model, 'product_name')->textInput(); ?>
        </td>

    </tr>

    <tr>
        <td class="label">day rate：</td>
        <td>
            <?php echo $form->field($model, 'day_rate')->textInput(); ?>
        </td>
    </tr>

    <tr>
        <td class="label">cost rate：</td>
        <td>
            <?php echo $form->field($model, 'cost_rate')->textInput(); ?>
        </td>
    </tr>

    <tr>
        <td class="label">overdue rate：</td>
        <td>
            <?php echo $form->field($model, 'overdue_rate')->textInput(); ?>
        </td>
    </tr>
    <tr class="label">
        <td class="label"><span class="highlight">*</span>全新本新默认额度（卢比）</td>
        <td><?php echo $form->field($model, 'default_credit_limit')->textInput(); ?></td>
        <td>说明：全新本新默认额度，单位为卢比</td>
    </tr>
    <tr class="label">
        <td class="label"><span class="highlight">*</span>全老本新默认额度（卢比）</td>
        <td><?php echo $form->field($model, 'default_credit_limit_2')->textInput(); ?></td>
        <td>说明：全老本新默认额度，单位为卢比</td>
    </tr>
    <?php if ($isNotMerchantAdmin): ?>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期付款开关</td>
            <td><?php echo $form->field($model, 'delay_status')->dropDownList([0 => '停用', 1 => '启用']); ?></td>
            <td>说明：外部导流的延期开关是在大盘中设置</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期支付比例（%）</td>
            <td><?php echo $form->field($model, 'delay_ratio')->textInput(); ?></td>
            <td>说明：整数，范围1-99</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期偏移天数</td>
            <td><?php echo $form->field($model, 'delay_day')->textInput(); ?></td>
            <td>说明：整数，基于订单到期日，到期日之前为负数，到期日之后为正数</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期并减免开关</td>
            <td><?php echo $form->field($model, 'delay_deduction_status')->dropDownList([0 => '停用', 1 => '启用']); ?></td>
            <td>说明：外部导流的延期开关是在大盘中设置</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期并减免比例（%）</td>
            <td><?php echo $form->field($model, 'delay_deduction_ratio')->textInput(); ?></td>
            <td>说明：整数，范围1-99</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>延期并减免偏移天数</td>
            <td><?php echo $form->field($model, 'delay_deduction_day')->textInput(); ?></td>
            <td>说明：整数，基于订单到期日，到期日之前为负数，到期日之后为正数</td>
        </tr>
        <tr>
            <td class="label"><span class="highlight">*</span>展期开关</td>
            <td><?php echo $form->field($model, 'extend_status')->dropDownList([0 => '停用', 1 => '启用']); ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>展期比例（%）</td>
            <td><?php echo $form->field($model, 'extend_ratio')->textInput(); ?></td>
            <td>说明：整数，范围1-99</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>展期开启天数范围</td>
            <td><?php echo $form->field($model, 'extend_day')->textInput(); ?></td>
            <td>说明：整数，使用英文逗号分隔，如1,30 (逾期1天包含到逾期30天包含)</td>
        </tr>
        <tr class="label">
            <td class="label"><span class="highlight">*</span>是否展示借款天数</td>
            <td><?php echo $form->field($model, 'show_days')->dropDownList(ProductSetting::$show_days_map); ?></td>

            <td>是否展示借款天数</td>
        </tr>
    <?php endif; ?>
    <tr class="submit" style="text-align: left">
        <td colspan="15" >
            <input type="submit" value="submit" name="submit_btn" class="btn" id="submit_btn">&nbsp;&nbsp;&nbsp;
            <a href="javascript:history.go(-1)" class="btn back">back</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
