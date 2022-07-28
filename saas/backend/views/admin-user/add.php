<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu(Yii::T('common', 'Admin management'), array(
	array('List', Url::toRoute('admin-user/list'), 0),
	array('Add admin', Url::toRoute('admin-user/add'), 1),
));
?>

<?php echo $this->render('_form', [
	'model' => $model,
	'roles' => $roles,
    'current_roles_arr' => $current_roles_arr,
    'current_user_groups_arr' => $current_user_groups_arr,
	'is_super_admin' => $is_super_admin,
	'arrMerchantIds' => $arrMerchantIds,
	'isNotMerchantAdmin' => $isNotMerchantAdmin,
	'strategyOperating' => $strategyOperating,
]); ?>