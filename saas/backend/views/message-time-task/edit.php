<?php
use yii\helpers\Url;

/**
 * @var backend\components\View $this
 */
$this->shownav('content', 'menu_message_time_task_list');
$this->showsubmenu(Yii::T('common', 'SMS &amp; voice timed task management'), [
    [Yii::T('common', 'task list'), Url::toRoute(['message-time-task/list', 'is_export' => $is_export]), 0],
    [Yii::T('common', 'add task'), Url::toRoute(['message-time-task/add', 'is_export' => $is_export]), 1],
    [Yii::T('common', 'edit task'), Url::toRoute(['message-time-task/edit','id' => $model->id, 'is_export' => $is_export]), 1],
]);
?>

<?php echo $this->render('_form', [
	'model' => $model,
    'is_export' => $is_export,
    'merchantId' => $merchantId,
    'package_setting' => $package_setting
]); ?>