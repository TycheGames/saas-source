<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_collection_begin','menu_big_team_leader_list');
$this->showsubmenu('', array(
    array('big team leader list ', Url::toRoute('user-collection/big-team-leader-list'), 0),
    array('add big team leader', Url::toRoute(['user-collection/big-team-leader-add']),1),
));
?>

<?php echo $this->render('_bigteamform', [
    'model' => $model,
    'managerRelationModel' => $managerRelationModel,
    'is_self' => $is_self,
    'companyList' => $companyList,
    'defaultCompanyList' => $defaultCompanyList,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'password' => $password,
    'strategyOperating' => $strategyOperating,
    'labelArr' => $labelArr
]); ?>