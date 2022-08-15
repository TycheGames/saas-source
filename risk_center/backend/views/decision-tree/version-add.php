<?php

use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('risk', 'menu_rule_version_list');
$this->showsubmenu(Yii::T('common', 'Decision tree version'), array(
	array('List', Url::toRoute('decision-tree/version-list'), 0),
	array('Add', Url::toRoute('decision-tree/version-add'), 1),
));
?>

<?php echo $this->render('_form', [
	'model' => $model,
]); ?>