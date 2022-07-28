<?php

use yii\helpers\Url;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use yii\helpers\Html;
use callcenter\components\widgets\ActiveForm;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_adminuser_role_list');
$this->showsubmenu('角色管理', array(
	array('列表', Url::toRoute('admin-user/role-list'), 1),
	array('添加角色', Url::toRoute('admin-user/role-add'), 0),
));

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'Subgroup') ?>：<?php echo Html::dropDownList('group_id', Html::encode(Yii::$app->getRequest()->get('group_id', '')), \common\helpers\CommonHelper::getListT(AdminUserRole::$groups_map), ['prompt' => Yii::T('common', 'All Groups')]); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>ID</th>
		<th>标识</th>
		<th>所属组</th>
		<th>名称</th>
		<th>描述</th>
		<th>创建人</th>
		<th>创建时间</th>
		<th>操作</th>
	</tr>
	<?php foreach ($roles as $value): ?>
	<tr class="hover">
		<td class="td25"><?php echo Html::encode($value->id); ?></td>
		<td><?php echo Html::encode($value->name); ?></td>
		<td><?php echo $value->groups?Html::encode(AdminUserRole::$groups_map[$value->groups]):'暂无分组'; ?></td>
		<td><?php echo Html::encode($value->title); ?></td>
		<td><?php echo Html::encode($value->desc); ?></td>
		<td><?php echo Html::encode($value->created_user); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
		<td class="td23">
			<?php if ($value->name != AdminUser::SUPER_ROLE): ?>
			<a href="<?php echo Url::to(['admin-user/role-edit', 'id' => $value->id]); ?>">编辑</a>
			<a onclick="return confirmMsg('确定要删除吗？\n删除后该角色对应的管理员将失去权限');" href="<?php echo Url::to(['admin-user/role-delete', 'id' => $value->id]); ?>">删除</a>
			<?php else: ?>-<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
