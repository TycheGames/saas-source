<?php
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use \common\models\order\FinancialLoanRecord;
use yii\helpers\Html;
use common\helpers\CommonHelper;

$this->shownav('financial', 'menu_loan_list');
$this->showsubmenu(Yii::T('common', 'transferList'));
?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.modal.min.js"></script>
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/css/jquery.modal.min.css" type="text/css" media="screen" />
<style>
    input.txt {width:120px;}
    body > .modal { display: none;}
</style>

<?php $form = ActiveForm::begin(['id' => 'searchform', 'method' => "get",'action'=>['financial/loan-list'],'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'paymentId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('rid', '')); ?>" name="rid" class="txt">&nbsp;
支付订单号：<input type="text" value="<?= Html::encode(\yii::$app->request->get('order_uuid', '')); ?>" name="order_uuid" class="txt" style="width:200px;">&nbsp;
<?php echo Yii::T('common', 'Third party order number') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('trade_no', '')); ?>" name="trade_no" class="txt" style="width:200px;">&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('order_id', '')); ?>" name="order_id" class="txt">&nbsp;
<br />
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('user_id', '')); ?>" name="user_id" class="txt">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('phone', '')); ?>" name="phone" class="txt">&nbsp;
<?php echo Yii::T('common', 'status') ?>：<?= Html::dropDownList('status', Html::encode(Yii::$app->request->get('status', '')), \common\helpers\CommonHelper::getListT(FinancialLoanRecord::$ump_pay_status), ['prompt' => Yii::T('common', 'All status')]); ?>&nbsp;
<?php if (!empty($isNotMerchantAdmin)): ?>
    <?php echo Yii::T('common', 'belongsToMerchants') ?>：<?= Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId() ,['prompt' => 'all']); ?>&nbsp;
<?php endif; ?>
    <br /><br />
<?php echo Yii::T('common', 'application time') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('begintime', '')); ?>" name="begintime" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(\yii::$app->request->get('endtime', '')); ?>" name="endtime" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'Success time') ?>：<input type="text" value="<?= Html::encode(\yii::$app->request->get('updated_at_begin', '')); ?>" name="updated_at_begin" onfocus="WdatePicker({startDate:'%y-%M-%d 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(\yii::$app->request->get('updated_at_end', '')); ?>" name="updated_at_end" onfocus="WdatePicker({startDate:'%y-%M-%d 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})">
<input type="checkbox" name="is_summary" value="1"  <?php if(Html::encode(Yii::$app->request->get('is_summary', '0')==1)):?> checked <?php endif; ?> > <?php echo Yii::T('common', 'Show summary (after checking, the query will slow down)') ?>&nbsp;&nbsp;&nbsp;
    <input type="submit" name="search_submit"value="<?php echo Yii::T('common', 'search') ?>" class="btn" />&nbsp;
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>打款ID</th>
            <th>支付账号</th>
            <th>支付通道</th>
            <th>支付订单号</th>
            <th>第三方订单号</th>
            <th>订单ID</th>
            <th>用户ID</th>
            <th>手机号</th>
            <th style="width: 50px;">姓名</th>
            <th>打款金额</th>
            <th style="width: 50px;">绑卡银行</th>
            <th style="width: 50px;">打款状态</th>
            <th>下单时间</th>
            <th>成功时间</th>
            <th>操作</th>
        </tr>
        <?php foreach ($withdraws as $value): ?>
        <tr class="hover">
            <td><?= Html::encode(CommonHelper::idEncryption($value['rid'], 'financial')); ?></td>
            <td><?= Html::encode(\common\models\pay\PayoutAccountInfo::getListMap()[$value['payout_account_id']] ?? '-');?></td>
            <td><?= Html::encode(FinancialLoanRecord::$service_type_map[$value['service_type']]);?></td>
            <td><?= Html::encode($value['order_id'] ?? ''); ?></td>
            <td><?= Html::encode($value['trade_no'] ?? ''); ?></td>
            <td><?= Html::encode(CommonHelper::idEncryption($value['business_id'], 'order')); ?></td>
            <td><?= Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
            <td><div style="width: 120px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['phone']); ?></span></div></td>
            <td><?= Html::encode($value['name']); ?></td>
            <td>
                <?= sprintf('%.2f',  ($value['money']) / 100); ?>
            </td>
            <td><?= Html::encode($value['bank_name']);?></td>
            <td><?= Html::encode(FinancialLoanRecord::$ump_pay_status[$value['status']] ?? '-' );?></td>
            <td><?= Html::encode($value['order_time'] ? date('Y-m-d H:i', $value['order_time']) : '-'); ?></td>
            <td><?= Html::encode($value['success_time'] ? date('Y-m-d H:i:s', $value['success_time']) : '-'); ?></td>
            <td>
                <a href="<?= Url::toRoute(['financial/view', 'id' =>  CommonHelper::idEncryption($value['id'], 'financial')]);?>">查看</a>
                <?php if(
                        in_array($value['status'],[FinancialLoanRecord::UMP_PAY_HANDLE_FAILED, FinancialLoanRecord::UMP_PAY_WAITING])
                || (FinancialLoanRecord::UMP_CMB_PAYING == $value['status'] && (time() - $value['created_at']) > 86400)):?>
                    <a href="javascript:;" onclick="rejectOrder(<?= '\'' . CommonHelper::idEncryption($value['id'], 'financial') . '\'';?>)"><?php echo Yii::T('common', 'reject') ?></a>
                    <a href="javascript:;" onclick="resetOrder(<?= '\'' . CommonHelper::idEncryption($value['id'], 'financial') . '\'';?>)"><?php echo Yii::T('common', 'Lend again') ?></a>
                <?php endif;?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
    <?php if (empty($withdraws)):?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif;?>
</form>
<script>
    function rejectOrder(id)
    {
        if(!confirmMsg("<?php echo Yii::T('common', 'Whether to reject') ?>")){
            return;
        }
        $.post('<?= Url::toRoute('financial/loan-order-reject');?>',{
            id : id,
            _csrf : '<?= Yii::$app->request->getCsrfToken();?>'
        },function (data){
            alert(data.msg);
            if(data.code == 0)
            {
                window.location.reload();
            }
        });
    }

    function resetOrder(id)
    {
        if(!confirmMsg("<?php echo Yii::T('common', 'Whether to reset') ?>")){
            return;
        }
        $.post('<?= Url::toRoute('financial/loan-order-reset');?>',{
            id : id,
            _csrf : '<?= Yii::$app->request->getCsrfToken();?>'
        },function (data){
            alert(data.msg);
            if(data.code == 0)
            {
                window.location.reload();
            }
        });
    }
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>