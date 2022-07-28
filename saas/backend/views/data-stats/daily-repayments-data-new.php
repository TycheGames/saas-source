<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\models\order\UserLoanOrder;
use backend\models\Merchant;
/**
 * @var backend\components\View $this
 */
$this->showsubmenu(Yii::T('common', 'Daily repayment amount data'), array(
));
?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<style>
    table th{text-align: center}
    table td{text-align: center}
</style>
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<title><?php echo Yii::T('common', 'Daily repayment amount data') ?></title>
<script src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=> $isAllPlatform ? Url::to(['data-stats/day-data-repayment-statistics-full-platform']) : Url::to(['data-stats/day-data-repayment-statistics']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?=Yii::T('common', 'Repayment due date') ?>&nbsp;
<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('begin_created_at')) ? date("Y-m-d", time()-7*86400) : Yii::$app->request->get('begin_created_at')); ?>"  name="begin_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('end_created_at')) ? date("Y-m-d", time()+7*86400) : Yii::$app->request->get('end_created_at')); ?>"  name="end_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;

<?php echo Yii::T('common', 'management') ?>：<?php echo Html::dropDownList('fund_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('fund_id', [])), $fundList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'fund_id']); ?>&nbsp;
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
        Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<?php echo Yii::T('common', 'appMarket') ?>：<?php  echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $appMarketList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<?php echo Yii::T('common', 'mediaSource') ?>：<?php  echo Html::dropDownList('media_source', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('media_source', [])),
    $mediaSourceList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'media_source']); ?>&nbsp;
<?php echo Yii::T('common', 'packageName') ?>：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
    $packageNameList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
&nbsp;&nbsp;<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportmoney');return true;" class="btn">
&nbsp;&nbsp;(<?php echo Yii::T('common', 'Update every 5 minutes') ?>)
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan information') ?></th>
            <th colspan="9" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
            <?php if($isAllPlatform):?>
                <th colspan="7" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All platform new users') ?></th>
                <th colspan="7" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'All platform old users') ?></th>
            <?php else:?>
                <th colspan="7" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'New users') ?></th>
                <th colspan="7" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Old users') ?></th>
            <?php endif;?>
        </tr>
        <tr class="header">
            <!-- 借款信息 -->
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Repayment due date') ?></th>

            <!-- 所有用户 -->
            <th><?php echo Yii::T('common', 'Expire number') ?></th>
            <th><?php echo Yii::T('common', 'Normal repayment') ?></th>
            <th><?php echo Yii::T('common', 'Repayment completed') ?></th>
            <th><?php echo Yii::T('common', 'First overdue') ?></th>
            <th><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th><?php echo Yii::T('common', 'Overdue rate') ?></th>
            <th><?php echo Yii::T('common', 'Extension number') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Extension rate') ?></th>

            <!-- 新用户 -->
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Overdue rate') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Extension number') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Extension rate') ?></th>

            <!-- 老用户 -->
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire number') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Overdue rate') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Extension number') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Extension rate') ?></th>
        </tr>
        <tr class="hover">
            <!-- 借款信息 -->
            <td style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Summary information') ?></td>
            <!-- 所有用户 -->
            <td class="td25"><?php echo isset($total_info['expire_money_0'])?number_format(floor($total_info['expire_money_0'])/100):0; ?></td>
            <td class="td25"><?php echo isset($total_info['repay_zc_money_0']) ? number_format($total_info['repay_zc_money_0']/100) : 0; ?></a></td>
            <td class="td25"><?php echo isset($total_info['repay_money_0'])?number_format(floor($total_info['repay_money_0'])/100):0; ?></td>
            <td class="td25"><?php echo (!empty($total_info['t_expire_money_0'])) ? sprintf("%0.2f",($total_info['t_expire_money_0']-$total_info['t_repay_zc_money_0'])/($total_info['t_expire_money_0'])*100)."%" : '-'; ?></td>
            <td class="td25"><?php echo (!empty($total_info['expire_money_0'])) ? sprintf("%0.2f",($total_info['repay_money_0']/$total_info['expire_money_0'])*100)."%" : '-'; ?></td>
            <td class="td25"><?php echo (isset($total_info['t_expire_money_0']) && isset($total_info['t_repay_money_0'])) ? number_format(($total_info['t_expire_money_0']-$total_info['t_repay_money_0'])/100) : '-'; ?></td>
            <td class="td25"><?php echo empty($total_info['t_expire_money_0']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_money_0'] - $total_info['t_repay_money_0']) / $total_info['t_expire_money_0']) * 100) . "%"; ?></td>
            <td class="td25"><?php echo empty($total_info['t_extend_money_0']) ? '-' : number_format($total_info['t_extend_money_0']/100); ?></td>
            <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo empty($total_info['t_expire_money_0']) ? '-' : sprintf("%0.2f", ($total_info['t_extend_money_0'] / ($total_info['t_expire_money_0'])) * 100) . "%"; ?></td>

            <!-- 新用户 -->
            <td class="td25" style="text-align:center;color:blue"><?php echo isset($total_info['expire_money_1']) ? number_format(floor($total_info['expire_money_1'])/100):0; ?></td>
            <td class="td25" style="text-align:center;color:blue"><?php echo (!empty($total_info['t_expire_money_1'])) ? sprintf("%0.2f",($total_info['t_expire_money_1']-$total_info['t_repay_zc_money_1'])/($total_info['t_expire_money_1'])*100)."%" : '-'; ?></td>
            <td class="td25" style="text-align:center;color:blue"><?php echo (!empty($total_info['expire_money_1']))? sprintf("%0.2f",(($total_info['repay_money_1'])/$total_info['expire_money_1'])*100)."%" : '-'; ?></td>
            <td class="td25" style="text-align:center;color:blue"><?php echo (isset($total_info['t_expire_money_1']) && isset($total_info['t_repay_money_1'])) ? number_format(($total_info['t_expire_money_1']-$total_info['t_repay_money_1'])/100) : '-'; ?></td>
            <td class="td25" style="text-align:center;color:blue"><?php echo empty($total_info['t_expire_money_1']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_money_1'] - $total_info['t_repay_money_1']) / $total_info['t_expire_money_1']) * 100) . "%"; ?></td>
            <td class="td25" style="text-align:center;color:blue"><?php echo empty($total_info['t_extend_money_1']) ? '-' : number_format($total_info['t_extend_money_1']/100); ?></td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($total_info['t_expire_money_1']) ? '-' : sprintf("%0.2f", ($total_info['t_extend_money_1'] / ($total_info['t_expire_money_1'])) * 100) . "%"; ?></td>

            <!-- 老用户 -->
            <td class="td25" style="text-align:center;color:red"><?php echo isset($total_info['expire_money_2']) ? number_format(floor($total_info['expire_money_2'])/100):0; ?></td>
            <td class="td25" style="text-align:center;color:red"><?php echo (!empty($total_info['t_expire_money_2'])) ? sprintf("%0.2f",($total_info['t_expire_money_2']-$total_info['t_repay_zc_money_2'])/($total_info['t_expire_money_2'])*100)."%" : '-'; ?></td>
            <td class="td25" style="text-align:center;color:red"><?php echo (!empty($total_info['expire_money_2']))? sprintf("%0.2f",(($total_info['repay_money_2'])/$total_info['expire_money_2'])*100)."%" : '-'; ?></td>
            <td class="td25" style="text-align:center;color:red"><?php echo (isset($total_info['t_expire_money_2']) && isset($total_info['t_repay_money_2'])) ? number_format(($total_info['t_expire_money_2']-$total_info['t_repay_money_2'])/100) : '-'; ?></td>
            <td class="td25" style="text-align:center;color:red"><?php echo empty($total_info['t_expire_money_2']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_money_2'] - $total_info['t_repay_money_2']) / $total_info['t_expire_money_2']) * 100) . "%"; ?></td>
            <td class="td25" style="text-align:center;color:red"><?php echo empty($total_info['t_extend_money_2']) ? '-' : number_format($total_info['t_extend_money_2']/100); ?></td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($total_info['t_expire_money_2']) ? '-' : sprintf("%0.2f", ($total_info['t_extend_money_2'] / ($total_info['t_expire_money_2'])) * 100) . "%"; ?></td>
        </tr>
        <?php foreach ($info as $key=> $value): ?>
            <tr class="hover" style="<?php echo date('w', $value['unix_time_key']) == 0 || date('w', $value['unix_time_key']) == 6 ?'background:#F5F9FD':'';?>">
                <!-- 借款信息 -->
                <?php $now_date_time = strtotime(date('Y-m-d', time()));?>
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo date('n-j',strtotime($key)); ?></td>
                <!-- 所有用户 -->
                <td class="td25">
                    <?php $param_arr = ['loan-order/list','plan_repayment_time'=>$value['time_key'],'page_type'=>'3'] ?>
                    <a href="<?php echo Url::to($param_arr); ?>" target="_blank">
                        <?php echo isset($value['expire_money_0'])?number_format(floor($value['expire_money_0'])/100):0; ?>
                    </a>
                </td>
                <td class="td25"><?php echo number_format($value['repay_zc_money_0']/100); ?></a></td>
                <td class="td25">
                    <?php $param_arr = ['loan-order/list','plan_repayment_time'=>$value['time_key'],'status' => UserLoanOrder::STATUS_PAYMENT_COMPLETE,'page_type'=>'3'] ?>
                    <a href="<?php echo Url::to($param_arr); ?>" target="_blank">
                        <?php echo isset($value['repay_money_0'])?number_format(floor($value['repay_money_0'])/100):0; ?>
                    </a>
                </td>
                <td class="td25"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_0'])?'-':sprintf("%0.2f",($value['expire_money_0']-$value['repay_zc_money_0'])/($value['expire_money_0'])*100)."%"); ?></td>
                <td class="td25"><?php echo empty($value['expire_money_0'])?'-':sprintf("%0.2f",(($value['repay_money_0'])/($value['expire_money_0']))*100)."%"; ?></td>
                <td class="td25">
                    <?php $param_arr = ['loan-order/list','plan_repayment_time'=>$value['time_key'],'overdue_day'=>1,'page_type'=>'3'] ?>
                    <a href="<?php echo Url::to($param_arr); ?>" target="_blank">
                        <?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : number_format(($value['expire_money_0']-$value['repay_money_0'])/100); ?>
                    </a>
                </td>
                <td class="td25"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_0']) ? '-' : sprintf("%0.2f", (($value['expire_money_0'] - $value['repay_money_0']) / $value['expire_money_0']) * 100) . "%"; ?></td>
                <td class="td25"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['extend_money_0']) ? '-' : number_format($value['extend_money_0']/100); ?></td>
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_0']) ? '-' : sprintf("%0.2f",$value['extend_money_0'] / ($value['expire_money_0']) * 100)."%"; ?></td>

                <!-- 新用户 -->
                <td class="td25" style="text-align:center;color:blue"><?php echo isset($value['expire_money_1'])?number_format(floor($value['expire_money_1'])/100):0; ?></td>
                <td class="td25" style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_1'])?'0%':sprintf("%0.2f",($value['expire_money_1']-$value['repay_zc_money_1'])/($value['expire_money_1'])*100)."%"); ?></td>
                <td class="td25" style="text-align:center;color:blue"><?php echo empty($value['expire_money_1'])?'-':sprintf("%0.2f",(($value['repay_money_1'])/($value['expire_money_1']))*100)."%"; ?></td>
                <td class="td25" style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_money_1']) && isset($value['repay_money_1'])) ? number_format(($value['expire_money_1']-$value['repay_money_1'])/100) : 0) ; ?></td>
                <td class="td25" style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_1']) ? '-' : sprintf("%0.2f", (($value['expire_money_1'] - $value['repay_money_1']) / $value['expire_money_1']) * 100) . "%"; ?></td>
                <td class="td25" style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['extend_money_1']) ? '-' : number_format($value['extend_money_1']/100); ?></td>
                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_1']) ? '-' : sprintf("%0.2f",$value['extend_money_1'] / ($value['expire_money_1']) * 100)."%"; ?></td>

                <!-- 老用户 -->
                <td class="td25" style="text-align:center;color:red"><?php echo isset($value['expire_money_2'])?number_format(floor($value['expire_money_2'])/100):0; ?></td>
                <td class="td25" style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_2'])?'0%':sprintf("%0.2f",($value['expire_money_2']-$value['repay_zc_money_2'])/($value['expire_money_2'])*100)."%"); ?></td>
                <td class="td25" style="text-align:center;color:red"><?php echo empty($value['expire_money_2'])?'-':sprintf("%0.2f",($value['repay_money_2']/$value['expire_money_2'])*100)."%"; ?></td>
                <td class="td25" style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_money_2']) && isset($value['repay_money_2'])) ? number_format(($value['expire_money_2']-$value['repay_money_2'])/100) : 0) ; ?></td>
                <td class="td25" style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_2']) ? '-' : sprintf("%0.2f", (($value['expire_money_2'] - $value['repay_money_2']) / $value['expire_money_2']) * 100) . "%"; ?></td>
                <td class="td25" style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['extend_money_2']) ? '-' : number_format($value['extend_money_2']/100); ?></td>
                <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_2']) ? '-' : sprintf("%0.2f",$value['extend_money_2'] / ($value['expire_money_2']) * 100)."%"; ?></td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($info)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>
<br>
<p><?php echo Yii::T('common', 'Overdue amount (empty on the current day): loan principal of all users who have not repaid + remaining outstanding principal of users who have partially repaid') ?></p>
<p><?php echo Yii::T('common', 'Overdue rate (empty on the day): (Amount due-Amount repaid)/Amount due') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: repaid amount / due amount') ?></p>
<p><?php echo Yii::T('common', 'First over (empty on the day): (Amount due-normal repayment amount)/Amount due') ?></p>
<br/>
<p><?php echo Yii::T('common', 'Data from the same day to 14 days will be updated every 5 minutes') ?></p>
<p><?php echo Yii::T('common', 'Update every 20 minutes 7 days ago') ?></p>
<p><?php echo Yii::T('common', 'Data before 7-240 days is updated once a day (every day at 3 a.m.)') ?></p>
<p><?php echo Yii::T('common', '"First overdue", "overdue number" and "overdue rate" are only statistics less than today\'s data') ?></p>
