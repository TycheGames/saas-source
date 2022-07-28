<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_monitor_list');
$this->showsubmenu('', array(
    array('monitor list ', Url::toRoute('user-collection/monitor-list'), 0),
    array('edit monitor', Url::toRoute(['user-collection/monitor-edit']),1),
));
?>

<?php echo $this->render('_monitorform', [
    'model' => $model,
    'outsideRealName' => $outsideRealName,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
    'arrMerchantIds' => $arrMerchantIds,
    'strategyOperating' => $strategyOperating,
]); ?>