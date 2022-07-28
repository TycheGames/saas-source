<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_module');
$this->showsubmenu(Yii::T('common', 'Module Edit'), array(
    array(Yii::T('common', 'Show module'), Url::toRoute('credit-audit/manual-module-list'), 1),
));
?>

<?php echo $this->render('_manual-module-form', [
	'model' => $model,
]); ?>