<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\order\UserLoanOrder;
use common\helpers\CommonHelper;

$this->shownav('loanOrder', 'menu_repay_order_list');
$this->showsubmenu(Yii::T('common', 'repaymentList'), array(
    array('list', Url::toRoute('repay-order/list'),1),
));

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('user_id', '')); ?>" name="user_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" name="name" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'status') ?>：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), UserLoanOrderRepayment::$repayment_status_map, array('prompt' => Yii::T('common', 'All status'))); ?>&nbsp;
<?php echo Yii::T('common', 'New users') ?>：<?php echo Html::dropDownList('customer_type', Html::encode(Yii::$app->getRequest()->get('customer_type', '')), LoanPerson::$customer_type_list, array('prompt' => '-all-')); ?>&nbsp;
<?php echo Yii::T('common', 'Is it overdue') ?>：<?php echo Html::dropDownList('is_overdue', Html::encode(Yii::$app->getRequest()->get('is_overdue', '')), UserLoanOrderRepayment::$overdue_status_map, array('prompt' => Yii::T('common', 'All status'))); ?>&nbsp;
<?php echo Yii::T('common', 'is delay') ?>：<?php echo Html::dropDownList('is_delay_repayment', Html::encode(Yii::$app->getRequest()->get('is_delay_repayment', '')), UserLoanOrderRepayment::$delay_repayment_map, array('prompt' => '-all-')); ?>&nbsp;
<?php echo Yii::T('common', 'Delay repayment time') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('d_begintime', '')); ?>" name="d_begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('d_endtime', '')); ?>"  name="d_endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<br/>
<?php if (!empty($isNotMerchantAdmin)): ?>
    <?php echo Yii::T('common', 'belongsToMerchants') ?>：<?= Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId() ,['prompt' => 'all']); ?>&nbsp;
<?php endif; ?>
<?php echo Yii::T('common', 'Overdue days') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('overdue_day', '')); ?>" name="overdue_day" class="txt" style="width:100px;">&nbsp;
<?php echo Yii::T('common', 'Is the first order') ?>：<?php echo Html::dropDownList('is_first', Html::encode(Yii::$app->getRequest()->get('is_first', '')), UserLoanOrder::$first_loan_map, array('prompt' => '-all-')); ?>&nbsp;
<?php echo Yii::T('common', 'Due date') ?>：
<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('begintime', '')); ?>" name="begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('endtime', '')); ?>"  name="endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'clear date') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('r_begintime', '')); ?>" name="r_begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('r_endtime', '')); ?>"  name="r_endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<br/><br/>
<?php echo Yii::T('common', 'sourceId') ?>：<?= Html::dropDownList('source_id', Html::encode(\yii::$app->request->get('source_id')), $package_setting ,['prompt' => 'all']); ?>&nbsp;
<?php echo Yii::T('common', 'Order APP') ?>：<?= Html::dropDownList('order_app', Html::encode(\yii::$app->request->get('order_app')), $package_name_list ,['prompt' => 'all']); ?>&nbsp;
<?php echo Yii::T('common', 'Repayment date') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('u_begintime', '')); ?>" name="u_begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('u_endtime', '')); ?>"  name="u_endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<input type="checkbox" name="is_summary" value="1"  <?php if(Html::encode(Yii::$app->request->get('is_summary', '0')==1)):?> checked <?php endif; ?> > <?php echo Yii::T('common', 'Show summary (after checking, the query will slow down)') ?>&nbsp;&nbsp;&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php if($isShow):?>
    <input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportData');return true;" class="btn" />
<?php endif;?>
<?php if($isShowFinancial):?>
    <input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'Finance export') ?>csv" onclick="$(this).val('exportFinancialData');return true;" class="btn" />
<?php endif;?>
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th><?php echo Yii::T('common', 'Repayment order ID') ?></th>
                <th><?php echo Yii::T('common', 'pay_account_id') ?></th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'sourceId') ?></th>
                <th><?php echo Yii::T('common', 'Order APP') ?></th>
                <th><?php echo Yii::T('common', 'Is the first order') ?></th>
                <th><?php echo Yii::T('common', 'New users') ?></th>
                <th><?php echo Yii::T('common', 'Principal') ?></th>
                <th><?php echo Yii::T('common', 'Interest') ?></th>
                <th><?php echo Yii::T('common', 'Late fee') ?></th>
                <th><?php echo Yii::T('common', 'Service Charge') ?></th>
                <th><?php echo Yii::T('common', 'Amount paid') ?></th>
                <th><?php echo Yii::T('common', 'Lending Date') ?></th>
                <th><?php echo Yii::T('common', 'Due date') ?></th>
                <th><?php echo Yii::T('common', 'clear date') ?></th>
                <th><?php echo Yii::T('common', 'Is it overdue') ?></th>
                <th><?php echo Yii::T('common', 'Overdue days') ?></th>
                <th><?php echo Yii::T('common', 'Is extend') ?></th>
                <th><?php echo Yii::T('common', 'extend end date') ?></th>
                <th><?php echo Yii::T('common', 'Is delay') ?></th>
                <th><?php echo Yii::T('common', 'delay end date') ?></th>
                <th><?php echo Yii::T('common', 'Repayment times') ?></th>
                <th><?php echo Yii::T('common', 'status') ?></th>
                <th><?php echo Yii::T('common', 'operation') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['id'], 'repayment')); ?></td>
                    <td><?php echo Html::encode($value['pay_account_id']); ?></td>
                    <td>
                        <?= Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?>
                    </td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['user_id'], 'user')); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><div style="width: 120px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['phone']); ?></span></div></td>
                    <th><?= Html::encode($package_setting[$value['source_id']] ?? '--'); ?></th>
                    <td><?php echo Html::encode($value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name']); ?></td>
                    <td><?php echo Html::encode(UserLoanOrder::$first_loan_map[$value['is_first']] ?? '-'); ?></td>
                    <td><?php echo Html::encode(LoanPerson::$customer_type_list[$value['customer_type']] ?? '-'); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['principal']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['interests']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['overdue_fee']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['cost_fee']/100)); ?></td>
                    <td><?php echo Html::encode(sprintf("%0.2f",$value['true_total_money']/100)); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s',$value['loan_time'])); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d',$value['plan_repayment_time'])); ?></td>
                    <td><?php echo Html::encode($value['closing_time'] ? date('Y-m-d H:i:s',$value['closing_time']) : '-'); ?></td>
                    <td><?php echo Html::encode($value['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_YES ? "是" : "否"); ?></td>
                    <td><?php echo Html::encode($value['overdue_day']); ?></td>
                    <td><?php echo Html::encode($value['is_extend']); ?></td>
                    <td><?php echo Html::encode($value['is_extend'] == UserLoanOrderRepayment::IS_EXTEND_YES ? $value['extend_end_date'] : '-'); ?></td>
                    <td><?php echo Html::encode(UserLoanOrderRepayment::$delay_repayment_map[$value['is_delay_repayment']]); ?></td>
                    <td><?php echo Html::encode($value['is_delay_repayment'] == UserLoanOrderRepayment::IS_DELAY_YES ? date('Y-m-d', $value['delay_repayment_time']) : '-'); ?></td>
                    <td><?php echo Html::encode($repayCount[$value['user_id']]['count'] ?? 0); ?></td>
                    <td><?php echo Html::encode(isset(UserLoanOrderRepayment::$repayment_status_map[$value['status']])?UserLoanOrderRepayment::$repayment_status_map[$value['status']]:""); ?></td>
                    <td>
                        <a href="<?php echo Url::to(['detail', 'id' => CommonHelper::idEncryption($value['id'], 'repayment')]);?>"><?php echo Yii::T('common', 'view') ?></a>
                        <?php if($value['status'] != UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){?>
                            <a href="<?php echo Url::to(['finish-debit', 'id' => CommonHelper::idEncryption($value['id'], 'repayment')]);?>"><?php echo Yii::T('common', 'Manual repayment') ?></a>
                            <a href="<?php echo Url::to(['finish-debit-new', 'id' => CommonHelper::idEncryption($value['id'], 'repayment')]);?>"><?php echo Yii::T('common', 'PayU repayment') ?></a>
                        <?php }?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script type="text/javascript">
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>