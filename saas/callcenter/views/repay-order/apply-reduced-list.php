<?php

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use callcenter\models\CollectionReduceApply;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_reduce_apply_list');
$this->showsubmenu('减免申请列表', array(
    array('列表', Url::toRoute('repay-order/apply-reduced-list'), 1)
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['repay-order/apply-reduced-list']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php if($isNotMerchantAdmin): ?>
    Merchant： <?= Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $merchantList,array('prompt' => '-all-')); ?>&nbsp;&nbsp;
<?php endif;?>
催收订单id：<input type="text" value="<?= Html::encode(Yii::$app->request->get('loan_collection_order_id', '')); ?>"  name="loan_collection_order_id">&nbsp;&nbsp;
申请人ID：<input type="text" value="<?= Html::encode(Yii::$app->request->get('admin_user_id', '')); ?>"  name="admin_user_id">
申请人：<input type="text" value="<?= Html::encode(Yii::$app->request->get('username', '')); ?>"  name="username">&nbsp;&nbsp;
申请时间： <input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('apply_start_time', '')); ?>" name="apply_start_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(Yii::$app->getRequest()->get('apply_end_time', '')); ?>"  name="apply_end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
状态： <?= Html::dropDownList('apply_status', Html::encode(Yii::$app->getRequest()->get('apply_status', '')), CollectionReduceApply::$apply_status_list,array('prompt' => '-all-')); ?>
&nbsp;<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th >申请人id</th>
            <th >申请人</th>
            <th >催收订单id</th>
            <?php if($isNotMerchantAdmin): ?>
                <th style="color: red">Merchant</th>
            <?php endif;?>
            <th >申请状态</th>
            <th >申请备注</th>
            <th >创建时间</th>
            <th >更新时间</th>
            <th >操作</th>
        </tr>
        <?php foreach ($collectionReduceApply as $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['admin_user_id']); ?></td>
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode($value['loan_collection_order_id']); ?></td>
                <?php if($isNotMerchantAdmin): ?>
                    <td style="color: red"><?php echo Html::encode($merchantList[$value['merchant_id']] ?? '-'); ?></td>
                <?php endif;?>
                <td ><?php
                    if($value['apply_status'] == CollectionReduceApply::STATUS_APPLY_REJECT){
                        echo "<font color='red'>";
                    }else if($value['apply_status'] == CollectionReduceApply::STATUS_APPLY_PASS){
                        echo "<font color='green'>";
                    }else{
                        echo "<font color='blue'>";
                    }
                    echo Html::encode(CollectionReduceApply::$apply_status_list[$value['apply_status']]);
                    echo "</font>";
                    ?></td>
                <td ><?php echo Html::encode($value['apply_remark']); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['created_at'])); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['updated_at'])); ?></td>
                <td >
                <?php  if($value['apply_status'] == CollectionReduceApply::STATUS_WAIT_APPLY):?>
                    <a href="<?php echo Url::to(['repay-order/reduced-audit','id'=>$value['id']]); ?>">审核</a>
                <?php endif;?>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($collectionReduceApply)): ?>
        <div class="no-result">暂无记录</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
