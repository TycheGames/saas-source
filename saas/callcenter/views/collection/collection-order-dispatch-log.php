<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\CollectionOrderDispatchLog;

$this->shownav('manage', 'menu_admin_status_suggest_log_list');

$this->showsubmenu('订单分派日志', array(
    array('列表', Url::toRoute('collection/collection-order-dispatch-log'), 1)
));

?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
催收ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collection_order_id', '')); ?>" name="collection_order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Collector') ?>:<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
公司：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', '')), UserCompany::outsideRealName($merchant_id), array('prompt' => Yii::T('common', 'All agency'))); ?>&nbsp;
分派类型：<?php echo Html::dropDownList('type', Html::encode(Yii::$app->getRequest()->get('type', '')), CollectionOrderDispatchLog::$type_map, array('prompt' => '-所有类型-')); ?>&nbsp;
分派时订单逾期天数：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:120px;">&nbsp;
<span class="s_item">分派时间：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('s_dispatch_time', '')); ?>" name="s_dispatch_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('e_dispatch_time', '')); ?>"  name="e_dispatch_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value="过滤" class="btn">
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>日志ID</th>
        <th>催收订单ID</th>
        <th>催收订单当前状态</th>
        <th>分派类型</th>
        <th>公司</th>
        <th>催收员</th>
        <th>分派时订单逾期天数</th>
        <th>操作人id</th>
        <th>分派时间</th>
    </tr>
    <?php foreach ($collectionOrderDispatchLog as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['id']); ?></td>
            <td><?php echo Html::encode($value['collection_order_id']); ?></td>
            <td><?php echo LoanCollectionOrder::$status_list[$value['status']]; ?></td>
            <td><?php echo CollectionOrderDispatchLog::$type_map[$value['type']]; ?></td>
            <td><?php echo Html::encode($value['real_title']); ?></td>
            <td><?php echo Html::encode($value['username'] ?? '--'); ?></td>
            <td><?php echo Html::encode($value['overdue_day']); ?></td>
            <td><?php echo Html::encode($value['operator_id']); ?></td>
            <td><?php echo Date('Y-m-d H:i:s',$value['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($collectionOrderDispatchLog)): ?>
    <div class="no-result">暂无记录</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>