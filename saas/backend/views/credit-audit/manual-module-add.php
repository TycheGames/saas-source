<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_module');
$this->showsubmenu(Yii::T('common', 'Add module'), array(
    array(Yii::T('common', 'Show module'), Url::toRoute('credit-audit/manual-module-list'), 1),
    array(Yii::T('common', 'Add module'), Url::toRoute('credit-audit/manual-module-add'), 0),
));
?>

<?php echo $this->render('_manual-module-form', [
	'model' => $model,
]); ?>