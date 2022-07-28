<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_role_list');
$this->showsubmenu(Yii::T('common', 'Role management'), array(
	array('List', Url::toRoute('back-end-admin-user/role-list'), 1),
	array('Add role', Url::toRoute('back-end-admin-user/role-add'), 0),
));

?>

<?php echo $this->render('_roleform', [
	'model' => $model,
	'permissions' => $permissions,
	'permissionChecks' => $permissionChecks,
]); ?>