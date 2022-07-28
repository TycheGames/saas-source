<?php

use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use yii\helpers\Html;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\SmsTemplate;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_sms_template_list');
$this->showsubmenu('模板设置', array(
	array('列表', Url::toRoute('content-setting/sms-template-list'), 1),
	array('添加', Url::toRoute('content-setting/sms-template-add'), 0),
));
foreach( LoanCollectionOrder::$level as $k => $v){
    $lv_id = SmsTemplate::SHOW_START.$k;
    $levels[$lv_id] = $v;
}
$outsides = [];
foreach ($companys as $k => $v){
    $lv_id = SmsTemplate::SHOW_START.$k;
    $outsides[$lv_id] = $v;
}
?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
	是否启用：<?php echo Html::dropDownList('is_use', Html::encode(Yii::$app->getRequest()->get('is_use', '')), SmsTemplate::$is_use_map, ['prompt' => '--ALL--']); ?>
	<input type="submit" name="search_submit" value="过滤" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>ID</th>
		<th>模板名</th>
        <th>package name</th>
		<th>模板内容</th>
        <th>支持公司</th>
        <th>支持逾期等级</th>
		<th>是否启用</th>
		<th>创建时间</th>
		<th>更新时间</th>
		<th>操作</th>
	</tr>
	<?php foreach ($list as $value): ?>
	<tr class="hover">
		<td class="td25"><?php echo Html::encode($value->id); ?></td>
		<td><?php echo Html::encode($value->name); ?></td>
        <td><?php echo Html::encode($value->package_name); ?></td>
		<td><?php echo Html::encode($value->content); ?></td>
        <td><?php
            if($show_outside_str = $value->can_send_outside){
                $show_outside = explode(',', $show_outside_str);
                foreach($show_outside as $num => $show_p){
                    echo Html::encode($outsides[$show_p] ?? '');
                    echo '&nbsp;';
                    if(($num+1)%5 == 0){
                        echo '<br/>';
                    }
                }
            }else{
                echo '无';
            }
            ?></td>
        <td><?php
            if($show_level_str = $value->can_send_group){
                $show_level = explode(',', $show_level_str);
                foreach($show_level as $num => $show_p){
                    echo Html::encode($levels[$show_p] ?? '');
                    echo '&nbsp;';
                    if(($num+1)%5 == 0){
                        echo '<br/>';
                    }
                }
            }else{
                echo '无';
            }
            ?></td>
		<td><?php echo Html::encode(SmsTemplate::$is_use_map[$value->is_use]); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
        <td><?php echo Html::encode(date('Y-m-d', $value->updated_at)); ?></td>
		<td class="td24">
			<a href="<?php echo Url::to(['content-setting/sms-template-edit', 'id' => $value->id]); ?>">编辑</a>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>