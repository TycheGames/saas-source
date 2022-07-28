<?php

use backend\components\widgets\LinkPager;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use backend\models\AdminLoginErrorLog;

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
    用户名：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
	<input type="submit" name="search_submit" value="filter" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>用户名</th>
		<th>系统</th>
        <th>ip</th>
		<th>失败原因</th>
		<th>时间</th>
	</tr>
	<?php foreach ($models as $value): ?>
	<tr class="hover">
		<td><?php echo Html::encode($value->username); ?></td>
        <th><?php echo Html::encode(AdminLoginErrorLog::$systemMap[$value->system]) ;?></th>
        <td><?php echo Html::encode($value->ip); ?></td>
        <td><?php echo Html::encode(AdminLoginErrorLog::$typeMap[$value->type]); ?></td>
		<td><?php echo Html::encode(date('Y-m-d H:i:s', $value->created_at)); ?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>