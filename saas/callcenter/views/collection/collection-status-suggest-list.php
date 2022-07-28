<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\UserCompany;

$this->shownav('manage', 'menu_admin_status_suggest_log_list');

$this->showsubmenu('催收订单借款建议', array(
    array('列表', Url::toRoute('collection/admin-collection-status-suggest-list'), 1)
));

?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
催收ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collection_id', '')); ?>" name="collection_id" class="txt" style="width:120px;">&nbsp;
订单ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
当前建议类型：<?php echo Html::dropDownList('suggestion', Html::encode(Yii::$app->getRequest()->get('suggestion', '')), LoanCollectionOrder::$next_loan_advice, array('prompt' => '-所有类型-')); ?>&nbsp;
<?php echo Yii::T('common', 'Collection agency') ?>：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', '')), UserCompany::outsideRealName($merchant_id), array('prompt' => Yii::T('common', 'All agency'))); ?>&nbsp;
<input type="submit" name="search_submit" value="过滤" class="btn">
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>催收ID</th>
        <th>订单ID</th>
        <th>姓名</th>
        <th>手机号</th>
        <th>上次建议</th>
        <th>当前建议</th>
        <th>建议提供者</th>
        <th>催收机构</th>
        <th>创建时间</th>
        <th>建议原因</th>
    </tr>
    <?php foreach ($loan_collection_suggest_change_log as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['collection_id']); ?></td>
            <td><?php echo Html::encode($value['order_id']); ?></td>
            <td><?php echo Html::encode($value['user_name']); ?></td>
            <td><?php echo Html::encode($value['phone']); ?></td>
            <?php switch ($value['suggestion_before']) {
                case LoanCollectionOrder::RENEW_DEFAULT:
                    echo '<td style="font-weight: bold;">未给建议';
                    break;
                case LoanCollectionOrder::RENEW_PASS:
                    echo '<td style="font-weight: bold;color: green;">建议通过';
                    break;
                case LoanCollectionOrder::RENEW_REJECT:
                    echo '<td style="font-weight: bold;color: red">建议拒绝';
                    break;
                case LoanCollectionOrder::RENEW_CHECK:
                    echo '<td style="font-weight: bold;color: #ff8833">建议审核';
                    break;
                default:
                    break;
            } ?>
            </td>
            <?php switch ($value['suggestion']) {
                case LoanCollectionOrder::RENEW_DEFAULT:
                    echo '<td style="font-weight: bold;">未给建议';
                    break;
                case LoanCollectionOrder::RENEW_PASS:
                    echo '<td style="font-weight: bold;color: green;">建议通过';
                    break;
                case LoanCollectionOrder::RENEW_REJECT:
                    echo '<td style="font-weight: bold;color: red">建议拒绝';
                    break;
                case LoanCollectionOrder::RENEW_CHECK:
                    echo '<td style="font-weight: bold;color: #ff8833">建议审核';
                    break;
                default:
                    break;
            } ?>
            </td>
            <td><?php echo Html::encode(!empty($value['username']) ? Html::encode($value['username']) : '--'); ?></td>
            <td><?php echo Html::encode(!empty($value['real_title']) ? Html::encode($value['real_title']) : '--'); ?> </td>
            <td><?php echo Html::encode(Date('Y-m-d H:i:s',$value['created_at'])) ?></td>
            <td width="30%"><?php echo Html::encode($value['remark']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($loan_collection_suggest_change_log)): ?>
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
