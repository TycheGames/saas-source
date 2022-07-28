<?php

use callcenter\components\widgets\ActiveForm;
use callcenter\models\SmsTemplate;
use callcenter\models\loan_collection\LoanCollectionOrder;
foreach( LoanCollectionOrder::$level as $k => $v){
    $lv_id = SmsTemplate::SHOW_START.$k;
    $levels[$lv_id] = $v;
}
$outsides = [];
foreach ($companys as $k => $v){
    $lv_id = SmsTemplate::SHOW_START.$k;
    $outsides[$lv_id] = $v;
}
?>
<style type="text/css">
    .show{color: green;border: 1px solid green;padding: 2px;}
    .bold{font-weight: bold;}
    .aisle_type select option{padding: 20px;}
</style>
<?php $this->showtips('提示', [
    '<span class="bold">文案自动替换：</span><span class="show">#username# - 用户名</span> &nbsp;，&nbsp; <span class="show">#scheduled_payment_money# - 剩余应还金额</span> &nbsp;，&nbsp; <span class="show">#should_repay_date# - 应还日期 格式：dd/mm/yyyy</span> &nbsp;，&nbsp; <span class="show">#overdue_day# - 逾期天数</span>',
    '普通借款文案例如：尊敬的#username#，您已逾期#overdue_day#天,目前还有剩余#scheduled_payment_money#金额未还，请打开APP进行还款。如已还款，请忽略。',
]); ?>
<br/>
<?php $form = ActiveForm::begin(["id" => "sms-template-form"]); ?>
<table class="tb tb2 fixpadding">
    <?php if(isset($model->id)): ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>template id</td>
        <td><?php echo $model->id; ?></td>
    </tr>
    <?php endif; ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>can send outside</td>
        <td><?php echo $form->field($model, 'can_send_outside')->checkboxList($outsides); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>can send group</td>
        <td><?php echo $form->field($model, 'can_send_group')->checkboxList($levels); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>package name</td>
        <td><?php echo $form->field($model, 'package_name')->dropDownList($arrPackage); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>template name</td>
        <td><?php echo $form->field($model, 'name')->textInput(['style' => 'width:300px']); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>template content</td>
        <td><?php echo $form->field($model, 'content')->textarea(['style' => 'width:500px;height:100px']); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>is_use</td>
        <td><?php echo $form->field($model, 'is_use')->dropDownList(SmsTemplate::$is_use_map, []); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>