<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_list');
$this->showsubmenu('', array(
    array('collector list ', Url::toRoute('user-collection/user-list'), 0),
    array('edit collector', Url::toRoute(['user-collection/edit']),1),
));
?>

<?php echo $this->render('_collectionform', [
    'model' => $model,
    'defaultCompanys' => $defaultCompanys,
    'companys' => $companys,
    'is_self' => $is_self,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'strategyOperating' => $strategyOperating,
]); ?>