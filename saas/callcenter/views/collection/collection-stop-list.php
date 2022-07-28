<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_collection_stop_list');
$this->showsubmenu('停催列表', array(
    array('停催列表', Url::toRoute('collection/collection-stop-list'), 1),
    array('添加停催订单', Url::toRoute('collection/collection-stop-add'), 0)
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection/collection-stop-list']), 'options' => ['style' => 'margin-top:5px;']]); ?>
Loan Order Id：<input type="text" value="<?= Html::encode(Yii::$app->request->get('order_id', '')); ?>"  name="order_id">
Phone：<input type="text" value="<?= Html::encode(Yii::$app->request->get('phone', '')); ?>"  name="phone">
更新时间： <input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('start_time', '')); ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('end_time', '')); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
&nbsp;<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th >催收Id</th>
            <th >订单Id</th>
            <th >还款Id</th>
            <th >用户名</th>
            <th >手机号</th>
            <th >更新时间</th>
            <th >重新入催时间</th>
            <th >操作</th>
        </tr>
        <?php foreach ($loanCollectionOrder as $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['id']); ?></td>
                <td ><?php echo Html::encode($value['user_loan_order_id']); ?></td>
                <td ><?php echo Html::encode($value['user_loan_order_repayment_id']); ?></td>
                <td ><?php echo Html::encode($value['name']); ?></td>
                <td ><?php echo Html::encode($value['phone']); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['updated_at'])); ?></td>
                <td ><?php echo Html::encode(!empty($value['next_input_time']) ? date('Y-m-d H:i:s',$value['next_input_time']) : '--'); ?></td>
                <td >
                    <a onclick="javascript:if(!confirmMsg('are you sure recovery this order!')) return false;" href="<?php echo Html::encode(Url::to(['collection/collection-stop-recovery','id'=>$value['id']])); ?>">继续入催</a>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($loanCollectionOrder)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
