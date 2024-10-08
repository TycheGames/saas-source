<?php

use yii\helpers\Url;
use yii\helpers\Html;
use callcenter\components\widgets\ActiveForm;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu('管理员管理', array(
	array('列表', Url::toRoute('admin-user/list'), 1),
	// array('添加管理员', Url::toRoute('admin-user/add'), 0),
));

?>

<?php $form = ActiveForm::begin(['id' => 'admin-form']); ?>
<table class="tb tb2">
	<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'username'); ?></td></tr>
	<tr class="noborder">
		<td colspan="2"><?php echo Html::encode($model->username); ?></td>
	</tr>
	<tr><td class="td27" colspan="2"><font color="red">*</font>新密码：</td></tr>
	<tr class="noborder">
		<td class="vtop rowform"><?php echo $form->field($model, 'password')->textInput(['value'=>$password,'placeholder'=>'新密码']); ?></td>
		<td class="vtop tips2">密码为6-16位字符或数字</td>
	</tr>
	<tr>
		<td colspan="15">
			<input type="submit" value="提交" name="submit_btn" class="btn">
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
		</td>
	</tr>
</table>
<?php ActiveForm::end(); ?>