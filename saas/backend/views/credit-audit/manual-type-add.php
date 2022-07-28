<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_type');
$this->showsubmenu(Yii::T('common', 'Audit'), array(
    array(Yii::T('common', 'Show type'), Url::toRoute('credit-audit/manual-type-list'), 1),
));
?>

<?php echo $this->render('_manual-type-form', [
    'model' => $model,
    'moduleIds' => $moduleIds
]); ?>