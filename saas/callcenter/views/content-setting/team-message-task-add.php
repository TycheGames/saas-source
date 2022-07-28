<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_team_message_task');
$this->showsubmenu('模板设置', array(
    array('列表', Url::toRoute('content-setting/team-message-task'), 1),
));

echo $this->render('_team-message-task-form', [
	'model' => $model,
    'taskTypes' => $taskTypes,
    'companys' => $companys
]);