<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use backend\models\Merchant;

/**
 * @var backend\components\View $this
 */
$rate = 1;
$search_date = Yii::$app->getRequest()->get('search_date', '1');
?>
<title><?php echo Yii::T('common', 'Daily borrowing data (loans)') ?></title>
    <style>
        table th{text-align: center}
        table td{text-align: center}
    </style>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?= Yii::T('common', 'Default all');?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get','action'=> Url::to(['data-stats/daily-data2-full-platform']), 'options' => ['style' => 'margin-top:5px;']]); ?>
    <?php
    echo Html::dropDownList('search_date', $search_date, array(1=>Yii::T('common', 'Loan date')));
    ?>
   <input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('add_start')) ? date("Y-m-d", time()-30*86400) : Yii::$app->request->get('add_start')); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
    <?php echo Yii::T('common', 'to') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('add_end')) ? date("Y-m-d", time()) : Yii::$app->request->get('add_end')); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;
    <?php echo Yii::T('common', 'management') ?>：<?php echo Html::dropDownList('fund_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('fund_id', [])), $fundList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'fund_id']); ?>&nbsp;
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
            Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
    <?php echo Yii::T('common', 'appMarket') ?>：<?php  echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
        $appMarketList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
    <?php echo Yii::T('common', 'mediaSource') ?>：<?php  echo Html::dropDownList('media_source', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('media_source', [])),
        $mediaSourceList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'media_source']); ?>&nbsp;
    <?php echo Yii::T('common', 'packageName') ?>：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
        $packageNameList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
    <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
    <input type="hidden" name="from_st" value="<?=Yii::$app->request->get('from_st','0')?>">
    &nbsp;&nbsp;<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportcsv');return true;" class="btn">
&nbsp;&nbsp;<?php echo Yii::T('common', 'Last updated') ?>：<?php echo date("Y-m-d H:i:s", $last_update_at);?>&nbsp;(<?php echo Yii::T('common', 'Update every 8 minutes') ?>)
<?php ActiveForm::end(); ?>
    <form name="listform" method="post">
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th colspan="3" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan information') ?></th>
                <th colspan="4" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
                <th colspan="3" style="color:blue;text-align: center;border-right:1px solid blue;"><?php echo Yii::T('common', 'All platform new users') ?></th>
                <th colspan="3" style="color:red;text-align: center;border-right:1px solid red;"><?php echo Yii::T('common', 'All platform old users') ?></th>
            </tr>
            <tr class="header">
                <th><?php echo Yii::T('common', 'Loan date') ?></th>
                <th><?php echo Yii::T('common', 'Repayment date') ?></th>
                <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan term') ?></th>
                <th><?php echo Yii::T('common', 'Loan number') ?></th>
                <th><?php echo Yii::T('common', 'Total loan amount') ?></th>
                <th><?php echo Yii::T('common', 'Loan pieces') ?></th>
                <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Ratio of new and old users') ?></th>
                <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Loan number') ?></th>
                <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Total loan amount') ?></th>
                <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Loan pieces') ?></th>
                <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Loan number') ?></th>
                <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Total loan amount') ?></th>
                <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Loan pieces') ?></th>
            </tr>
            <tr>
                <?php
                $total_all = $total_loan_num_new + $total_loan_num_old;
                $total_new_pre = (!empty($total_all)) ? round(($total_loan_num_new/$total_all)*100) : 0;
                $total_old_pre = 100 - $total_new_pre;
                ?>
                <th><?php echo Yii::T('common', 'Summary information') ?></th>
                <th></th>
                <th style="border-right:1px solid #A9A9A9;"></th>
                <th><?php echo $total_loan_num; ?></th>
                <th><?php echo number_format(sprintf("%0.2f",$total_loan_money/100)); ?></th>
                <th><?php echo ($total_loan_num > 0) ? (round(($total_loan_money/100)/$total_loan_num, 2)) : 0; ?></th>
                <th style="border-right:1px solid #A9A9A9;"><?php echo "<span style='color:blue'>".$total_new_pre."</span>" . " : " . "<span style='color:red'>".$total_old_pre."</span>"; ?></th>
                <th style="text-align:center;color:blue"><?php echo $total_loan_num_new ?></th>
                <th style="text-align:center;color:blue"><?php echo number_format($total_loan_money_new/100) ?></th>
                <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo ($total_loan_num_new>0)?sprintf("%0.2f",($total_loan_money_new/100)/$total_loan_num_new):0; ?></th>
                <th style="text-align:center;color:red"><?php echo $total_loan_num_old ?></th>
                <th style="text-align:center;color:red"><?php echo number_format($total_loan_money_old/100) ?></th>
                <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo ($total_loan_num_old>0)?sprintf("%0.2f",($total_loan_money_old/100)/$total_loan_num_old):0; ?></th>
            </tr>
            <?php foreach ($data as $value): ?>
                <tr class="hover" style="<?php echo date('w', $value['date_time']) == 0 || date('w', $value['date_time']) == 6?'background:#deeffa':'';?>">
                    <?php
                        $loan_num_new = $rate*$value['loan_num_new'];
                        $loan_num_old = $rate*$value['loan_num_old'];
                        $total = $loan_num_new + $loan_num_old;
                        $new_pre = (!empty($total)) ? round(($loan_num_new/$total)*100) : 0;
                        $old_pre = 100 - $new_pre;

                        $loan_num = $rate*$value['loan_num'];
                        $loan_money = $rate*$value['loan_money']/100;
                        $loan_money_new = $value['loan_money_new']/100;
                        $loan_money_old = $value['loan_money_old']/100;
                    ?>
                    <!-- 借款信息 -->
                    <td class="td25"><?php echo date("n-j",$value['date_time']); ?></td>
                    <td class="td25"><?php echo date("n-j",$value['date_time']+$value['loan_term']*86400); ?></td>
                    <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo $value['loan_term']; ?></td>
                    <!-- 所有用户 -->
                    <td class="td25"><a href="<?php echo Url::to(['loan-order/list','time'=>date("Y-m-d",$value['date_time']),'page_type'=>'2']); ?>"target="_blank"><?php echo $loan_num; ?></a></td>
                    <td class="td25"><?php echo number_format(sprintf("%0.2f",$loan_money)); ?></td>
                    <td class="td25"><?php echo ($loan_num>0)?sprintf("%0.2f",$loan_money/$loan_num):0; ?></td>
                    <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo "<span style='color:blue'>".$new_pre."</span>" . " : " . "<span style='color:red'>".$old_pre."</span>"; ?></td>

                    <!-- 新用户 -->
                    <td class="td25" style="text-align:center;color:blue"><a href="<?php echo Url::to(['loan-order/list','time'=>date("Y-m-d",$value['date_time']),'old_user'=>'-1','page_type'=>'2']); ?>"target="_blank" style="color: blue"><?php echo $loan_num_new; ?></a></td>
                    <td class="td25" style="text-align:center;color:blue"><?php echo number_format(sprintf("%0.2f",$loan_money_new)); ?></td>
                    <td class="td25" style="text-align:center;border-right:1px solid blue;color:blue"><?php echo ($loan_num_new>0)?sprintf("%0.2f",$loan_money_new/$loan_num_new):0; ?></td>

                    <!-- 老用户 -->
                    <td class="td25" style="text-align:center;color:red;"><a href="<?php echo Url::to(['loan-order/list','time'=>date("Y-m-d",$value['date_time']),'old_user'=>'1','page_type'=>'2']); ?>"target="_blank" style="color: red"><?php echo $loan_num_old; ?></a></td>
                    <td class="td25" style="text-align:center;color:red;"><?php echo number_format(sprintf("%0.2f",$loan_money_old)); ?></td>
                    <td class="td25" style="text-align:center;border-right:1px solid red;color:red;"><?php echo ($loan_num_old>0)?sprintf("%0.2f",$loan_money_old/$loan_num_old):0; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($data)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
    </form>

<?php //echo LinkPager::widget(['pagination' => $pages]); ?>

    <table frame="above" align="right">
        <tr>
            <td align="center" style="color: red;"><?php echo Yii::T('common', 'Total of loan slips') ?>：<?php echo floor($rate*$total_loan_num) ?></td>
        </tr>
        <tr>
            <td align="center" style="color: red;"><?php echo Yii::T('common', 'Total borrowings') ?>：<?php echo sprintf("%.2f",$rate*$total_loan_money / 100) ?></td>
        </tr>
    </table>
