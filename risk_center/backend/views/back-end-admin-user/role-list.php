<?php

use yii\helpers\Url;
use backend\models\AdminUser;
use backend\models\AdminUserRole;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_role_list');
$this->showsubmenu(Yii::T('common', 'Role management'), array(
	array('List', Url::toRoute('back-end-admin-user/role-list'), 1),
	array('Add role', Url::toRoute('back-end-admin-user/role-add'), 0),
));

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'Subgroup') ?>：<?php echo Html::dropDownList('group_id', Html::encode(Yii::$app->getRequest()->get('group_id', '')), \common\helpers\CommonHelper::getListT(AdminUserRole::$status), ['prompt' => Yii::T('common', 'All Groups')]); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
	<tr class="header">
		<th><?php echo Yii::T('common', 'Logo') ?></th>
		<th><?php echo Yii::T('common', 'Subgroup') ?></th>
		<th><?php echo Yii::T('common', 'title') ?></th>
		<th><?php echo Yii::T('common', 'description') ?></th>
		<th><?php echo Yii::T('common', 'founder') ?></th>
		<th><?php echo Yii::T('common', 'Creation time') ?></th>
		<th><?php echo Yii::T('common', 'operation') ?></th>
	</tr>
	<?php foreach ($roles as $value): ?>
	<tr class="hover">
		<td><?php echo Html::encode($value->name); ?></td>
		<td><?php echo Html::encode($value->groups?AdminUserRole::$status[$value->groups]:'No grouping'); ?></td>
		<td><?php echo Html::encode($value->title); ?></td>
		<td><?php echo Html::encode($value->desc); ?></td>
		<td><?php echo Html::encode($value->created_user); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
		<td class="td24">
            <a href="<?php echo Html::encode(Url::to(['back-end-admin-user/role-details','role'=> $value->name]))?>"><?php echo Yii::T('common', 'member') ?></a>
			<?php if ($value->name != AdminUser::SUPER_ROLE && ($value->groups != AdminUserRole::TYPE_COLLECTION) ): ?>
			<a href="<?php echo Html::encode(Url::to(['back-end-admin-user/role-edit', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'edit') ?></a>
			<a onclick="return confirmMsg('Are you sure you want to delete it? \nAfter deletion, the administrator corresponding to this role will lose permission.');" href="<?php echo Html::encode(Url::to(['back-end-admin-user/role-delete', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'del') ?></a>
			<?php else: ?>
            <?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
