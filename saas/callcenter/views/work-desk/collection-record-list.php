<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use common\helpers\CommonHelper;
$this->shownav('workbench', 'menu_admin_record_list');
?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'action'=>Url::to(['work-desk/admin-record-list']),'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:90px;">
OrderID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:90px;">&nbsp;
<?php if($from):?>
Borrower：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collection_name', '')); ?>" name="collection_name" class="txt" style="width:90px;" placeholder="名字可能重复">&nbsp;
<?php endif;?>
Status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', 0)), LoanCollectionOrder::$status_list,array('prompt' => '-all status-')); ?>&nbsp;
Operation type：<?php echo Html::dropDownList('operate_tp', Html::encode(Yii::$app->getRequest()->get('operate_tp', 0)), LoanCollectionRecord::$label_operate_type,array('prompt' => '-all operate type-')); ?>&nbsp;
Connect status：<?php echo Html::dropDownList('is_connect', Html::encode(Yii::$app->getRequest()->get('is_connect', 0)), LoanCollectionRecord::$is_connect,array('prompt' => '-all connect status-')); ?>&nbsp;
Collection result：<?php echo Html::dropDownList('risk_control', Html::encode(Yii::$app->getRequest()->get('risk_control', 0)), LoanCollectionRecord::$risk_controls,array('prompt' => '-all result-')); ?>&nbsp;
<input type="submit" name="search_submit" value="submit" class="btn">
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>ID</th>
        <th>Order ID</th>
        <th>Borrower</th>
        <th>Contacts</th>
        <th>Relation</th>
        <th>Phone</th>
        <th>Operation type</th>
        <th>Promise repay time</th>
        <th>Connect status</th>
        <th>Reminder result</th>
        <th>Remarks(for reminder)</th>
        <th>Status</th>
        <th>Operation time</th>
        <th>Operator</th>
        <th>Operator Company</th>
    </tr>
    <?php foreach ($recordInfo['record'] as $value): ?>
        <?php  $content = $value['send_note'] ==0 ? '(send fail)' : '';?>
        <tr class="hover">
            <td><?php echo Html::encode($value['id']); ?></td>
            <td><?php echo Html::encode($value['order_id']); ?></td>
            <th><?php echo Html::encode($value['loan_name']); ?></th>
            <th><?php echo Html::encode($value['contact_name']); ?></th>
            <th><?php echo Html::encode($value['relation']); ?></th>
            <td><div style="width: 100px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['contact_phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['contact_phone']); ?></span></div></td>
            <th><?php echo Html::encode(isset(LoanCollectionRecord::$label_operate_type[$value['operate_type']])?LoanCollectionRecord::$label_operate_type[$value['operate_type']]:"--");  ?></th>
            <th><?php echo Html::encode(empty($value['promise_repayment_time']) ? '--' : date('Y-m-d H:i:s',$value['promise_repayment_time'])); ?></th>
            <th><?php echo Html::encode(!empty($value['is_connect'])?LoanCollectionRecord::$is_connect[$value['is_connect']]:"--");  ?></th>
            <th><?php echo Html::encode(!empty($value['risk_control']) && isset(LoanCollectionRecord::$risk_controls[$value['risk_control']]) ?LoanCollectionRecord::$risk_controls[$value['risk_control']]:"--");  ?></th>
            <th>
                <?php if(!empty($value['remark'])):?>
                    <?php echo Html::encode($value['remark']); ?>
                <?php else:?>
                    --
                <?php endif?>
            </th>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$value['order_state']])?LoanCollectionOrder::$status_list[$value['order_state']]:"--");  ?></th>
            <th><?php echo Html::encode(!empty($value['operate_at'])?date("Y-m-d H:i:s",$value['operate_at']):"--"); ?></th>
            <th><?php echo  Html::encode($value['username'] ?? "--");?></th>
            <th><?php echo  Html::encode($value['real_title'] ?? "--");?></th>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($recordInfo['record'])): ?>
    <div class="no-result">no record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $recordInfo['pages'],]); ?>
<script type="text/javascript">
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#w0').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#w0').append('<input type="hidden" name="search_submit" value="search">');
        $('#w0').submit();
    });
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>
