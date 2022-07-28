<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_collection_begin','menu_team_leader_list');
$this->showsubmenu('', array(
    array('team leader list ', Url::toRoute('user-collection/team-leader-list'), 0),
    array('add team leader', Url::toRoute(['user-collection/team-leader-add']),1),
    array('team', Url::toRoute('user-collection/team'),0),
));
?>

<?php echo $this->render('_teamform', [
    'model' => $model,
    'is_self' => $is_self,
    'companyList' => $companyList,
    'defaultCompanyList' => $defaultCompanyList,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'password' => $password,
    'strategyOperating' => $strategyOperating,
]); ?>