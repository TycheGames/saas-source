<?php
use yii\helpers\Url;
use callcenter\components\widgets\ActiveForm;

$this->shownav('manage', 'menu_company_list');
$this->showsubmenu('', array(
    array('催收公司', Url::toRoute(['user-company/company-lists']),0),
    array('新增催收公司', Url::toRoute('user-company/company-add'),1),
));
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'company-add-form']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <td class="label">机构代号：</td>
        <td ><?php echo $form->field($user_company, 'title')->textInput(); ?></td>
    </tr>
    <tr>
        <td class="label">机构名称：</td>
        <td ><?php echo $form->field($user_company, 'real_title')->textInput(); ?>
        </td>
    </tr>
    <tr>
        <td class="label">自营：</td>
        <td ><?php echo $form->field($user_company, 'system')->dropDownList([0=>'否', 1=>'是']); ?></td>
    </tr>

</table>

 <input type="submit" value="提交" name="submit_btn" class="btn">
<a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
<?php ActiveForm::end(); ?>
