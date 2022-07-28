<?php

use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminMessageTask;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_team_message_task');
$this->showsubmenu('组长任务消息配置', array(
	array('列表', Url::toRoute('content-setting/team-message-task'), 1),
	array('添加', Url::toRoute('content-setting/team-message-task-add'), 0),
));

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
	status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), AdminMessageTask::$status_map, ['prompt' => '--all--']); ?>
    company：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', '')), ArrayHelper::htmlEncode($companyList), ['prompt' => '--all--']); ?>
    group：<?php echo Html::dropDownList('group', Html::encode(Yii::$app->getRequest()->get('group', '')), LoanCollectionOrder::$current_level, ['prompt' => '--all--']); ?>
    <input type="submit" name="search_submit" value="过滤" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>ID</th>
		<th>company</th>
        <th>group</th>
        <th>task type</th>
        <th>task value</th>
        <th>status</th>
		<th>update time</th>
		<th>操作</th>
	</tr>
	<?php foreach ($list as $value): ?>
	<tr class="hover">
		<td class="td25"><?php echo Html::encode($value['id']); ?></td>
		<td><?php echo Html::encode($value['title']); ?></td>
        <td><?php echo LoanCollectionOrder::$current_level[$value['group']] ?? '-'; ?></td>
        <td><?php echo $value['task_type']; ?></td>
        <td><?php echo $value['task_value']; ?></td>
        <td><?php echo AdminMessageTask::$status_map[$value['status']] ?? '-'; ?></td>
		<td><?php echo date('Y-m-d H:i:s',$value['updated_at']); ?></td>
		<td class="td24">
			<a href="<?php echo Html::encode(Url::to(['content-setting/team-message-task-edit', 'id' => $value['id']])); ?>">编辑</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>