<?php

use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\models\AdminUser;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu(Yii::T('common', 'Administrator management'), array(
	array('List', Url::toRoute('back-end-admin-user/list'), 1),
	array('Add', Url::toRoute('back-end-admin-user/add'), 0),
));

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'Username keywords') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Phone keywords') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Roles') ?>：<?php echo Html::dropDownList('role', Html::encode(Yii::$app->getRequest()->get('role', '')), \common\helpers\CommonHelper::getListT($role_lsit), ['prompt' => Yii::T('common', 'All Groups')]); ?>&nbsp;
	<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th><?php echo Yii::T('common', 'username') ?>	</th>
		<th><?php echo Yii::T('common', 'phone') ?></th>
		<th><?php echo Yii::T('common', 'Roles') ?></th>
		<th><?php echo Yii::T('common', 'founder') ?></th>
		<th><?php echo Yii::T('common', 'Creation time') ?></th>
		<th><?php echo Yii::T('common', 'Remarks') ?>/<?php echo Yii::T('common', 'name') ?></th>
		<th><?php echo Yii::T('common', 'operation') ?></th>
	</tr>
	<?php foreach ($users as $value): ?>
	<tr class="hover">
		<td><?php echo Html::encode($value->username); ?></td>
		<td><?php echo Html::encode($value->phone); ?></td>
		<td style="word-wrap: break-word; word-break: normal;word-break:break-all; "><?php echo Html::encode($value->role); ?></td>
		<td><?php echo Html::encode($value->created_user); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
		<td><?php echo Html::encode($value->mark); ?></td>
		<td class="td24">
			<a href="<?php echo Html::encode(Url::to(['back-end-admin-user/change-pwd', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'change Password') ?></a>
			<?php if ($value->username != AdminUser::SUPER_USERNAME): ?>
			<a href="<?php echo Html::encode(Url::to(['back-end-admin-user/edit', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'edit') ?></a>
			<a onclick="return confirmMsg('Are you sure you want to delete it ?');" href="<?php echo Html::encode(Url::to(['back-end-admin-user/delete', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'del') ?></a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>