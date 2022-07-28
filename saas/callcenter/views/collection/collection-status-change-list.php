<?php

use yii\helpers\Html;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use yii\helpers\Url;

$this->shownav('manage', 'menu_order_status_change_log_list');

$this->showsubmenu('催收订单状态转换列表管理', array(
    array('列表', Url::toRoute('collection/collection-status-change-list'), 1)
));
?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
催收订单号ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:120px;">&nbsp;
操作类型：<?php echo Html::dropDownList('type', Html::encode(Yii::$app->getRequest()->get('type', 0)), LoanCollectionOrder::$type,array('prompt' => '-所有操作-')); ?>&nbsp;
<input type="checkbox" name="is_summary" value="1"  <?php if(Yii::$app->request->get('is_summary', '0')==1):?> checked <?php endif; ?> > 显示汇总(勾选后，查询变慢)&nbsp;&nbsp;&nbsp;
<input type="submit" name="search_submit" value="过滤" class="btn">
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>ID</th>
        <th>催收订单ID</th>
        <th>操作类型</th>
        <th>操作前状态</th>
        <th>操作后状态</th>
        <th>操作人</th>
        <th>创建时间</th>
        <th>操作备注</th>
    </tr>
    <?php foreach ($loan_collection_status_change_log as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['id']); ?></td>
            <td><?php echo Html::encode($value['loan_collection_order_id']); ?></td>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$type[$value['type']])?LoanCollectionOrder::$type[$value['type']]:"")  ; ?></th>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$value['before_status']])?LoanCollectionOrder::$status_list[$value['before_status']]:"")  ; ?></th>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$value['after_status']])?LoanCollectionOrder::$status_list[$value['after_status']]:"")  ; ?></th>
            <th><?php echo Html::encode($value['operator_name']); ?></th>
            <th><?php echo Html::encode(empty($value['created_at'])?"--":date("Y-m-d H:i:s",$value['created_at'])); ?></th>
            <th><?php echo Html::encode($value['remark']); ?></th>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($loan_collection_status_change_log)): ?>
    <div class="no-result">暂无记录</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<div style="color:#428bca;font-size: 14px;font-weight:bold;" >每页&nbsp;<?php echo Html::dropDownList('page_size', Html::encode(Yii::$app->getRequest()->get('page_size', 15)), LoanCollectionRecord::$page_size); ?>&nbsp;条</div>
<script type="text/javascript">
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#w0').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#w0').append('<input type="hidden" name="search_submit" value="过滤">');
        $('#w0').submit();
    });
</script>
