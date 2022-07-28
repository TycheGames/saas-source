<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_list');
$this->showsubmenu('', array(
    array('monitor list ', Url::toRoute('user-collection/monitor-list'), 0),
    array('add monitor', Url::toRoute(['user-collection/monitor-add']),1),
));
?>

<?php echo $this->render('_monitorform', [
    'model' => $model,
    'outsideRealName' => $outsideRealName,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'strategyOperating' => $strategyOperating,
]); ?>