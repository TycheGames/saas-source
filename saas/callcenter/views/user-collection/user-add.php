<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_list');
$this->showsubmenu('', array(
    array('collector list ', Url::toRoute('user-collection/user-list'), 0),
    array('add collector', Url::toRoute(['user-collection/user-add']),1),
    array('team', Url::toRoute('user-collection/team'),0),
));
?>

<?php echo $this->render('_collectionform', [
    'model' => $model,
    'defaultCompanys' => $defaultCompanys,
    'companys' => $companys,
    'is_self' => $is_self,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'password' => $password,
    'strategyOperating' => $strategyOperating,
]); ?>