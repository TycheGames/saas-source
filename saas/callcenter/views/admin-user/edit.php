<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu('管理员管理', array(
	array('列表', Url::toRoute('admin-user/list'), 1),
	// array('添加管理员', Url::toRoute('admin-user/add'), 0),
));

?>

<?php echo $this->render('_form', [
	'model' => $model,
	'roles' => $roles,
    'current_roles_arr' => $current_roles_arr,
    'current_user_groups_arr' => $current_user_groups_arr,
    'is_super_admin' => $is_super_admin,
	'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'companys' => $companys,
    'defaultCompanys' => $defaultCompanys,
    'strategyOperating' => $strategyOperating,
]); ?>