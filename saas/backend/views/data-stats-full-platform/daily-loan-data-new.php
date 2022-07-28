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
$this->showsubmenu(Yii::T('common', 'Daily repayment order data(user structure)'), array(
    ));
?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<style>
    table th {
        text-align: center
    }

    table td {
        text-align: center
    }
</style>
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<title><?php echo Yii::T('common', 'Daily repayment order data') ?></title>
<script language="javascript" type="text/javascript"
        src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form', 'method' => 'get', 'action' => Url::to(['data-stats-full-platform/day-data-repayment-num-statistics-user-structure']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?=Yii::T('common', 'Repayment due date') ?>:&nbsp;
<input type="text"
       value="<?= Html::encode(empty(Yii::$app->request->get('begin_created_at')) ? date("Y-m-d", time() - 7 * 86400) : Yii::$app->request->get('begin_created_at')); ?>"
       name="begin_created_at"
       onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text"
         value="<?= Html::encode(empty(Yii::$app->request->get('end_created_at')) ? date("Y-m-d", time() + 7 * 86400) : Yii::$app->request->get('end_created_at')); ?>"
         name="end_created_at"
         onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
&nbsp;

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
&nbsp;&nbsp;<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportnum');return true;" class="btn">
&nbsp;&nbsp;(<?php echo Yii::T('common', 'Update every 5 minutes') ?>)
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan information') ?></th>
            <th colspan="7" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
            <th colspan="5" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All new self new') ?></th>
            <th colspan="5" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'All old self new') ?></th>
            <th colspan="5" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All old self old') ?></th>
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
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Overdue rate') ?></th>

            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Overdue rate') ?></th>

            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire number') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Overdue rate') ?></th>

            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Overdue number') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Overdue rate') ?></th>
        </tr>
        <tr class="hover">
            <!-- 借款信息 -->
            <td style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Summary information') ?></td>

            <!-- 所有用户 -->
            <td class="td25"><?php echo isset($total_info['expire_num_0']) ? $total_info['expire_num_0'] : 0; ?></td>
            <td class="td25"><?php echo $total_info['repay_zc_num_0'] ?? 0; ?></a></td>
            <td class="td25"><?php echo isset($total_info['repay_num_0']) ? $total_info['repay_num_0'] : 0; ?></td>
            <td class="td25"><?php echo empty($total_info['t_expire_num_0']) ? '-' : sprintf("%0.2f", ($total_info['t_expire_num_0'] - $total_info['t_repay_zc_num_0']) / $total_info['t_expire_num_0'] * 100) . "%"; ?></td>
            <td class="td25"><?php echo empty($total_info['expire_num_0']) ? '-' : sprintf("%0.2f", ($total_info['repay_num_0'] / $total_info['expire_num_0']) * 100) . "%"; ?></td>
            <td class="td25"><?php echo (isset($total_info['t_expire_num_0']) && isset($total_info['t_repay_num_0'])) ? $total_info['t_expire_num_0'] - $total_info['t_repay_num_0'] : '-'; ?></td>
            <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo empty($total_info['t_expire_num_0']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_num_0'] - $total_info['t_repay_num_0']) / $total_info['t_expire_num_0']) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['expire_num_1']) ? $total_info['expire_num_1'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo empty($total_info['t_expire_num_1']) ? '0%' : sprintf("%0.2f", ($total_info['t_expire_num_1'] - $total_info['t_repay_zc_num_1']) / $total_info['t_expire_num_1'] * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo empty($total_info['expire_num_1']) ? '-' : sprintf("%0.2f", ($total_info['repay_num_1'] / $total_info['expire_num_1']) * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo((isset($total_info['t_expire_num_1']) && isset($total_info['t_repay_num_1'])) ? $total_info['t_expire_num_1'] - $total_info['t_repay_num_1'] : '-'); ?></td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($total_info['t_expire_num_1']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_num_1'] - $total_info['t_repay_num_1']) / $total_info['t_expire_num_1']) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:red"><?php echo isset($total_info['expire_num_2']) ? $total_info['expire_num_2'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:red"><?php echo empty($total_info['t_expire_num_2']) ? '0%' : sprintf("%0.2f", ($total_info['t_expire_num_2'] - $total_info['t_repay_zc_num_2']) / $total_info['t_expire_num_2'] * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:red"><?php echo empty($total_info['expire_num_2']) ? '-' : sprintf("%0.2f", ($total_info['repay_num_2'] / $total_info['expire_num_2']) * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:red"><?php echo((isset($total_info['t_expire_num_2']) && isset($total_info['t_repay_num_2'])) ? $total_info['t_expire_num_2'] - $total_info['t_repay_num_2'] : '-'); ?></td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($total_info['t_expire_num_2']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_num_2'] - $total_info['t_repay_num_2']) / $total_info['t_expire_num_2']) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['expire_num_3']) ? $total_info['expire_num_3'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo empty($total_info['t_expire_num_3']) ? '0%' : sprintf("%0.2f", ($total_info['t_expire_num_3'] - $total_info['t_repay_zc_num_3']) / $total_info['t_expire_num_3'] * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo empty($total_info['expire_num_3']) ? '-' : sprintf("%0.2f", ($total_info['repay_num_3'] / $total_info['expire_num_3']) * 100) . "%"; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo((isset($total_info['t_expire_num_3']) && isset($total_info['t_repay_num_3'])) ? $total_info['t_expire_num_3'] - $total_info['t_repay_num_3'] : '-'); ?></td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($total_info['t_expire_num_3']) ? '-' : sprintf("%0.2f", (($total_info['t_expire_num_3'] - $total_info['t_repay_num_3']) / $total_info['t_expire_num_3']) * 100) . "%"; ?></td>
        </tr>
        <?php foreach ($info as $key => $value): ?>
            <tr class="hover"
                style="<?php echo date('w', $value['unix_time_key']) == 0 || date('w', $value['unix_time_key']) == 6 ? 'background:#deeffa' : ''; ?>">
                <!-- 借款信息 -->
                <?php $now_date_time = strtotime(date('Y-m-d', time()));?>
                <td class="td25"
                    style="border-right:1px solid #A9A9A9;"><?php echo date('n-j', strtotime($key)); ?></td>

                <!-- 所有用户 -->
                <td class="td25">
                    <?php $param_arr = ['loan-order/list', 'plan_repayment_time' => $value['time_key'], 'page_type' => '3'] ?>
                    <a href="<?php echo Url::to($param_arr); ?>"target="_blank">
                        <?php echo isset($value['expire_num_0']) ? $value['expire_num_0'] : 0; ?>
                    </a>
                </td>
                <td class="td25"><?php echo $value['repay_zc_num_0']; ?></a></td>
                <td class="td25">
                    <?php $param_arr = ['loan-order/list', 'plan_repayment_time' => $value['time_key'], 'status' => UserLoanOrder::STATUS_PAYMENT_COMPLETE, 'page_type' => '3']; ?>
                    <a href="<?php echo Url::to($param_arr); ?>" target="_blank">
                        <?php echo isset($value['repay_num_0']) ? $value['repay_num_0'] : 0; ?>
                    </a>
                </td>
                <td class="td25"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", ($value['expire_num_0'] - $value['repay_zc_num_0']) / $value['expire_num_0'] * 100) . "%"); ?></td>
                <td class="td25"><?php echo empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", ($value['repay_num_0'] / $value['expire_num_0']) * 100) . "%"; ?></td>
                <td class="td25">
                    <?php $param_arr = ['loan-order/list', 'plan_repayment_time' => $value['time_key'], 'overdue_day' => 1, 'page_type' => '3']; ?>
                    <a href="<?php echo Url::to($param_arr); ?>" target="_blank">
                        <?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : $value['expire_num_0'] - $value['repay_num_0']; ?>
                    </a>
                </td>
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", (($value['expire_num_0'] - $value['repay_num_0']) / $value['expire_num_0']) * 100) . "%"; ?></td>


                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['expire_num_1']) ? $value['expire_num_1'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_1']) ? '0%' : sprintf("%0.2f", ($value['expire_num_1'] - $value['repay_zc_num_1']) / $value['expire_num_1'] * 100) . "%"); ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo empty($value['expire_num_1']) ? '-' : sprintf("%0.2f", ($value['repay_num_1'] / $value['expire_num_1']) * 100) . "%"; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_num_1']) && isset($value['repay_num_1'])) ? $value['expire_num_1'] - $value['repay_num_1'] : '-'); ?></td>
                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_1']) ? '-' : sprintf("%0.2f", (($value['expire_num_1'] - $value['repay_num_1']) / $value['expire_num_1']) * 100) . "%"; ?></td>

                <td class="td25"
                    style="text-align:center;color:red"><?php echo isset($value['expire_num_2']) ? $value['expire_num_2'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_2']) ? '0%' : sprintf("%0.2f", ($value['expire_num_2'] - $value['repay_zc_num_2']) / $value['expire_num_2'] * 100) . "%"); ?></td>
                <td class="td25"
                    style="text-align:center;color:red"><?php echo empty($value['expire_num_2']) ? '-' : sprintf("%0.2f", ($value['repay_num_2'] / $value['expire_num_2']) * 100) . "%"; ?></td>
                <td class="td25"
                    style="text-align:center;color:red"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_num_2']) && isset($value['repay_num_2'])) ? $value['expire_num_2'] - $value['repay_num_2'] : '-'); ?></td>
                <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_2']) ? '-' : sprintf("%0.2f", (($value['expire_num_2'] - $value['repay_num_2']) / $value['expire_num_2']) * 100) . "%"; ?></td>

                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['expire_num_3']) ? $value['expire_num_3'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_3']) ? '0%' : sprintf("%0.2f", ($value['expire_num_3'] - $value['repay_zc_num_3']) / $value['expire_num_3'] * 100) . "%"); ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo empty($value['expire_num_3']) ? '-' : sprintf("%0.2f", ($value['repay_num_3'] / $value['expire_num_3']) * 100) . "%"; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_num_3']) && isset($value['repay_num_3'])) ? $value['expire_num_3'] - $value['repay_num_3'] : '-'); ?></td>
                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_3']) ? '-' : sprintf("%0.2f", (($value['expire_num_3'] - $value['repay_num_3']) / $value['expire_num_3']) * 100) . "%"; ?></td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($info)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>
<br>
<p><?php echo Yii::T('common', 'The data displayed by default is the data of expiration orders in the past 7 days') ?></p>
<p><?php echo Yii::T('common', 'Overdue bills (the day is empty): the outstanding bills in the due bills of the day') ?></p>
<p><?php echo Yii::T('common', 'Overdue rate (empty on the day): (Odds due-Odds repaid)/Odds due') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: the number of bills that have been repaid in the number of bills due on that day / the number of bills due on that day') ?></p>
<p><?php echo Yii::T('common', 'First overdue (day is empty): (number of maturity documents - number of normal repayment documents) / number of maturity documents') ?></p>
<br/>
<p><?php echo Yii::T('common', 'Data from the same day to 14 days will be updated every 5 minutes') ?></p>
<p><?php echo Yii::T('common', 'Update every 20 minutes 7 days ago') ?></p>
<p><?php echo Yii::T('common', 'Data before 7-240 days is updated once a day (every day at 3 a.m.)') ?></p>
<p><?php echo Yii::T('common', '"First overdue", "overdue number" and "overdue rate" are only statistics less than today\'s data') ?></p>



