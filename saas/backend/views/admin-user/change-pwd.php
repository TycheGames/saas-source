<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu(Yii::T('common', 'Admin management'), array(
	array('List', Url::toRoute('admin-user/list'), 1),
	array('Add admin', Url::toRoute('admin-user/add'), 0),
));

?>

<?php $form = ActiveForm::begin(['id' => 'admin-form']); ?>
<table class="tb tb2">
	<tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'username'); ?></td></tr>
	<tr class="noborder">
		<td colspan="2"><?php echo Html::encode($model->username); ?></td>
	</tr>
	<tr><td class="td27" colspan="2"><font color="red">*</font><?php echo Yii::T('common', 'new password') ?>：</td></tr>
	<tr class="noborder">
		<td class="vtop rowform"><?php echo $form->field($model, 'password')->passwordInput(['autocomplete' => 'off']); ?></td>
		<td class="vtop tips2"><?php echo Yii::T('common', 'Password 6-16 bits character or number') ?></td>
	</tr>
	<tr>
		<td colspan="15">
			<input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
		</td>
	</tr>
</table>
<?php ActiveForm::end(); ?>