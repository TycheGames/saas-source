<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\Merchant;

/**
 * @var backend\components\View $this
 */
$this->showsubmenu(Yii::T('common', 'Order data transform(structure)'), array(
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<style>
    table th{text-align: center}
    table td{text-align: center}
</style>
<script language="JavaScript">
    $(function () {
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
        $('.package-name-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all package name') ?>'});
    });
</script>
<title>每日还款金额数据(结构)</title>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=> Url::to(['data-stats-full-platform/user-structure-order-transform']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text"
       value="<?= Html::encode(empty(Yii::$app->request->get('begin_created_at')) ? date("Y-m-d", time() - 15 * 86400) : Yii::$app->request->get('begin_created_at')); ?>"
       name="begin_created_at"
       onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text"
         value="<?= Html::encode(empty(Yii::$app->request->get('end_created_at')) ? date("Y-m-d", time() + 86400) : Yii::$app->request->get('end_created_at')); ?>"
         name="end_created_at"
         onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
    Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;

Package Name：<?php echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])), $packageNames,['class' => 'form-control package-name-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th colspan="2" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan information') ?></th>
            <th colspan="6" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
            <th colspan="6" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All new self new') ?></th>
            <th colspan="6" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'All old self new') ?></th>
            <th colspan="6" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All old self old') ?></th>
        </tr>
        <tr class="header">
            <!-- 借款信息 -->
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th style="border-right:1px solid #A9A9A9;">Package Name</th>
            <!-- 所有用户 -->
            <th><?php echo Yii::T('common', 'Apply(person/order/money)') ?></th>
            <th><?php echo Yii::T('common', 'Audit pass(person/order/money)') ?></th>
            <th><?php echo Yii::T('common', 'Pass rate(person/order)') ?></th>
            <th><?php echo Yii::T('common', 'Withdraw(person/order/money)') ?></th>
            <th><?php echo Yii::T('common', 'Withdraw rate(person/order)') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan(person/order/money)') ?></th>

            <!-- 新用户 -->
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Apply(person/order/money)') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Audit pass(person/order/money)') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Pass rate(person/order)') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Withdraw(person/order/money)') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Withdraw rate(person/order)') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Loan(person/order/money)') ?></th>

            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Apply(person/order/money)') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Audit pass(person/order/money)') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Pass rate(person/order)') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Withdraw(person/order/money)') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Withdraw rate(person/order)') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Loan(person/order/money)') ?></th>

            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Apply(person/order/money)') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Audit pass(person/order/money)') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Pass rate(person/order)') ?></th>
            <th style="text-align:center;color:blue;"><?php echo Yii::T('common', 'Withdraw(person/order/money)') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Withdraw rate(person/order)') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Loan(person/order/money)') ?></th>
        </tr>
        <tr class="hover" onclick= "showDateData()">
            <!-- 借款信息 -->
            <td><?php echo Yii::T('common', 'Summary information') ?></td>
            <td style="border-right:1px solid #A9A9A9;"></td>
            <!-- 所有用户 -->
            <td class="td25">
                <?php echo isset($total_info['apply_person_num_0'])?$total_info['apply_person_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_num_0'])?$total_info['apply_order_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_money_0'])?number_format(floor($total_info['apply_order_money_0'])/100):0; ?>
            </td>
            <td class="td25">
                <?php echo isset($total_info['audit_pass_person_num_0'])?$total_info['audit_pass_person_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_num_0'])?$total_info['audit_pass_order_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_money_0'])?number_format(floor($total_info['audit_pass_order_money_0'])/100):0; ?>
            </td>
            <td class="td25">
                <?php echo (!empty($total_info['apply_person_num_0'])) ? sprintf("%0.2f",($total_info['audit_pass_person_num_0']/$total_info['apply_person_num_0'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['apply_order_num_0'])) ? sprintf("%0.2f",($total_info['audit_pass_order_num_0']/$total_info['apply_order_num_0'])*100)."%" : '-'; ?>
            </td>
            <td class="td25">
                <?php echo isset($total_info['withdraw_person_num_0'])?$total_info['withdraw_person_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_num_0'])?$total_info['withdraw_order_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_money_0'])?number_format(floor($total_info['withdraw_order_money_0'])/100):0; ?>
            </td>
            <td class="td25">
                <?php echo (!empty($total_info['audit_pass_person_num_0'])) ? sprintf("%0.2f",($total_info['withdraw_person_num_0']/$total_info['audit_pass_person_num_0'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['audit_pass_order_num_0'])) ? sprintf("%0.2f",($total_info['withdraw_order_num_0']/$total_info['audit_pass_order_num_0'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="border-right:1px solid #A9A9A9;">
                <?php echo isset($total_info['loan_success_person_num_0'])?$total_info['loan_success_person_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_num_0'])?$total_info['loan_success_order_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_money_0'])?number_format(floor($total_info['loan_success_order_money_0'])/100):0; ?>
            </td>

            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['apply_person_num_1'])?$total_info['apply_person_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_num_1'])?$total_info['apply_order_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_money_1'])?number_format(floor($total_info['apply_order_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['audit_pass_person_num_1'])?$total_info['audit_pass_person_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_num_1'])?$total_info['audit_pass_order_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_money_1'])?number_format(floor($total_info['audit_pass_order_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo (!empty($total_info['apply_person_num_1'])) ? sprintf("%0.2f",($total_info['audit_pass_person_num_1']/$total_info['apply_person_num_1'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['apply_order_num_1'])) ? sprintf("%0.2f",($total_info['audit_pass_order_num_1']/$total_info['apply_order_num_1'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['withdraw_person_num_1'])?$total_info['withdraw_person_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_num_1'])?$total_info['withdraw_order_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_money_1'])?number_format(floor($total_info['withdraw_order_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo (!empty($total_info['audit_pass_person_num_1'])) ? sprintf("%0.2f",($total_info['withdraw_person_num_1']/$total_info['audit_pass_person_num_1'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['audit_pass_order_num_1'])) ? sprintf("%0.2f",($total_info['withdraw_order_num_1']/$total_info['audit_pass_order_num_1'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                <?php echo isset($total_info['loan_success_person_num_1'])?$total_info['loan_success_person_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_num_1'])?$total_info['loan_success_order_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_money_1'])?number_format(floor($total_info['loan_success_order_money_1'])/100):0; ?>
            </td>

            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['apply_person_num_2'])?$total_info['apply_person_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_num_2'])?$total_info['apply_order_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_money_2'])?number_format(floor($total_info['apply_order_money_2'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['audit_pass_person_num_2'])?$total_info['audit_pass_person_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_num_2'])?$total_info['audit_pass_order_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_money_2'])?number_format(floor($total_info['audit_pass_order_money_2'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo (!empty($total_info['apply_person_num_2'])) ? sprintf("%0.2f",($total_info['audit_pass_person_num_2']/$total_info['apply_person_num_2'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['apply_order_num_2'])) ? sprintf("%0.2f",($total_info['audit_pass_order_num_2']/$total_info['apply_order_num_2'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['withdraw_person_num_2'])?$total_info['withdraw_person_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_num_2'])?$total_info['withdraw_order_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_money_2'])?number_format(floor($total_info['withdraw_order_money_2'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo (!empty($total_info['audit_pass_person_num_2'])) ? sprintf("%0.2f",($total_info['withdraw_person_num_2']/$total_info['audit_pass_person_num_2'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['audit_pass_order_num_2'])) ? sprintf("%0.2f",($total_info['withdraw_order_num_2']/$total_info['audit_pass_order_num_2'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                <?php echo isset($total_info['loan_success_person_num_2'])?$total_info['loan_success_person_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_num_2'])?$total_info['loan_success_order_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_money_2'])?number_format(floor($total_info['loan_success_order_money_2'])/100):0; ?>
            </td>

            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['apply_person_num_3'])?$total_info['apply_person_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_num_3'])?$total_info['apply_order_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['apply_order_money_3'])?number_format(floor($total_info['apply_order_money_3'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['audit_pass_person_num_3'])?$total_info['audit_pass_person_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_num_3'])?$total_info['audit_pass_order_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['audit_pass_order_money_3'])?number_format(floor($total_info['audit_pass_order_money_3'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo (!empty($total_info['apply_person_num_3'])) ? sprintf("%0.2f",($total_info['audit_pass_person_num_3']/$total_info['apply_person_num_3'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['apply_order_num_3'])) ? sprintf("%0.2f",($total_info['audit_pass_order_num_3']/$total_info['apply_order_num_3'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['withdraw_person_num_3'])?$total_info['withdraw_person_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_num_3'])?$total_info['withdraw_order_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['withdraw_order_money_3'])?number_format(floor($total_info['withdraw_order_money_3'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo (!empty($total_info['audit_pass_person_num_3'])) ? sprintf("%0.2f",($total_info['withdraw_person_num_3']/$total_info['audit_pass_person_num_3'])*100)."%" : '-'; ?><br/>
                <?php echo (!empty($total_info['audit_pass_order_num_3'])) ? sprintf("%0.2f",($total_info['withdraw_order_num_3']/$total_info['audit_pass_order_num_3'])*100)."%" : '-'; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                <?php echo isset($total_info['loan_success_person_num_3'])?$total_info['loan_success_person_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_num_3'])?$total_info['loan_success_order_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['loan_success_order_money_3'])?number_format(floor($total_info['loan_success_order_money_3'])/100):0; ?>
            </td>
        </tr>
        <?php foreach ($date_data as $key=> $value): ?>
                <tr class="hover date-data" hidden>
                    <!-- 借款信息 -->
                    <td class="td25" style='color:red;'><?php echo date('n-j',strtotime($key)); ?></td>
                    <td class="td25" style="color:red;border-right:1px solid #A9A9A9;"></td>
                    <!-- 所有用户 -->
                    <td class="td25" style='color:red;'>
                        <?php echo isset($value['apply_person_num_0'])?$value['apply_person_num_0'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_num_0'])?$value['apply_order_num_0'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_money_0'])?number_format(floor($value['apply_order_money_0'])/100):0; ?>
                    </td>
                    <td class="td25" style='color:red;'>
                        <?php echo isset($value['audit_pass_person_num_0'])?$value['audit_pass_person_num_0'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_num_0'])?$value['audit_pass_order_num_0'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_money_0'])?number_format(floor($value['audit_pass_order_money_0'])/100):0; ?>
                    </td>
                    <td class="td25" style='color:red;'>
                        <?php echo (!empty($value['apply_person_num_0'])) ? sprintf("%0.2f",($value['audit_pass_person_num_0']/$value['apply_person_num_0'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['apply_order_num_0'])) ? sprintf("%0.2f",($value['audit_pass_order_num_0']/$value['apply_order_num_0'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style='color:red;'>
                        <?php echo isset($value['withdraw_person_num_0'])?$value['withdraw_person_num_0'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_num_0'])?$value['withdraw_order_num_0'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_money_0'])?number_format(floor($value['withdraw_order_money_0'])/100):0; ?>
                    </td>
                    <td class="td25" style='color:red;'>
                        <?php echo (!empty($value['audit_pass_person_num_0'])) ? sprintf("%0.2f",($value['withdraw_person_num_0']/$value['audit_pass_person_num_0'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['audit_pass_order_num_0'])) ? sprintf("%0.2f",($value['withdraw_order_num_0']/$value['audit_pass_order_num_0'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="color:red;border-right:1px solid red;">
                        <?php echo isset($value['loan_success_person_num_0'])?$value['loan_success_person_num_0'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_num_0'])?$value['loan_success_order_num_0'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_money_0'])?number_format(floor($value['loan_success_order_money_0'])/100):0; ?>
                    </td>

                    <!-- 新用户 -->
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['apply_person_num_1'])?$value['apply_person_num_1'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_num_1'])?$value['apply_order_num_1'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_money_1'])?number_format(floor($value['apply_order_money_1'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['audit_pass_person_num_1'])?$value['audit_pass_person_num_1'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_num_1'])?$value['audit_pass_order_num_1'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_money_1'])?number_format(floor($value['audit_pass_order_money_1'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo (!empty($value['apply_person_num_1'])) ? sprintf("%0.2f",($value['audit_pass_person_num_1']/$value['apply_person_num_1'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['apply_order_num_1'])) ? sprintf("%0.2f",($value['audit_pass_order_num_1']/$value['apply_order_num_1'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['withdraw_person_num_1'])?$value['withdraw_person_num_1'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_num_1'])?$value['withdraw_order_num_1'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_money_1'])?number_format(floor($value['withdraw_order_money_1'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo (!empty($value['audit_pass_person_num_1'])) ? sprintf("%0.2f",($value['withdraw_person_num_1']/$value['audit_pass_person_num_1'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['audit_pass_order_num_1'])) ? sprintf("%0.2f",($value['withdraw_order_num_1']/$value['audit_pass_order_num_1'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                        <?php echo isset($value['loan_success_person_num_1'])?$value['loan_success_person_num_1'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_num_1'])?$value['loan_success_order_num_1'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_money_1'])?number_format(floor($value['loan_success_order_money_1'])/100):0; ?>
                    </td>

                    <!-- 老用户 -->
                    <td class="td25" style="text-align:center;color:red">
                        <?php echo isset($value['apply_person_num_2'])?$value['apply_person_num_2'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_num_2'])?$value['apply_order_num_2'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_money_2'])?number_format(floor($value['apply_order_money_2'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:red">
                        <?php echo isset($value['audit_pass_person_num_2'])?$value['audit_pass_person_num_2'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_num_2'])?$value['audit_pass_order_num_2'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_money_2'])?number_format(floor($value['audit_pass_order_money_2'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:red">
                        <?php echo (!empty($value['apply_person_num_2'])) ? sprintf("%0.2f",($value['audit_pass_person_num_2']/$value['apply_person_num_2'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['apply_order_num_2'])) ? sprintf("%0.2f",($value['audit_pass_order_num_2']/$value['apply_order_num_2'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:red">
                        <?php echo isset($value['withdraw_person_num_2'])?$value['withdraw_person_num_2'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_num_2'])?$value['withdraw_order_num_2'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_money_2'])?number_format(floor($value['withdraw_order_money_2'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:red">
                        <?php echo (!empty($value['audit_pass_person_num_2'])) ? sprintf("%0.2f",($value['withdraw_person_num_2']/$value['audit_pass_person_num_2'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['audit_pass_order_num_2'])) ? sprintf("%0.2f",($value['withdraw_order_num_2']/$value['audit_pass_order_num_2'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                        <?php echo isset($value['loan_success_person_num_2'])?$value['loan_success_person_num_2'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_num_2'])?$value['loan_success_order_num_2'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_money_2'])?number_format(floor($value['loan_success_order_money_2'])/100):0; ?>
                    </td>

                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['apply_person_num_3'])?$value['apply_person_num_3'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_num_3'])?$value['apply_order_num_3'] : 0; ?><br/>
                        <?php echo isset($value['apply_order_money_3'])?number_format(floor($value['apply_order_money_3'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['audit_pass_person_num_3'])?$value['audit_pass_person_num_3'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_num_3'])?$value['audit_pass_order_num_3'] : 0; ?><br/>
                        <?php echo isset($value['audit_pass_order_money_3'])?number_format(floor($value['audit_pass_order_money_3'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo (!empty($value['apply_person_num_3'])) ? sprintf("%0.2f",($value['audit_pass_person_num_3']/$value['apply_person_num_3'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['apply_order_num_3'])) ? sprintf("%0.2f",($value['audit_pass_order_num_3']/$value['apply_order_num_3'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo isset($value['withdraw_person_num_3'])?$value['withdraw_person_num_3'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_num_3'])?$value['withdraw_order_num_3'] : 0; ?><br/>
                        <?php echo isset($value['withdraw_order_money_3'])?number_format(floor($value['withdraw_order_money_3'])/100):0; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue">
                        <?php echo (!empty($value['audit_pass_person_num_3'])) ? sprintf("%0.2f",($value['withdraw_person_num_3']/$value['audit_pass_person_num_3'])*100)."%" : '-'; ?><br/>
                        <?php echo (!empty($value['audit_pass_order_num_3'])) ? sprintf("%0.2f",($value['withdraw_order_num_3']/$value['audit_pass_order_num_3'])*100)."%" : '-'; ?>
                    </td>
                    <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                        <?php echo isset($value['loan_success_person_num_3'])?$value['loan_success_person_num_3'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_num_3'])?$value['loan_success_order_num_3'] : 0; ?><br/>
                        <?php echo isset($value['loan_success_order_money_3'])?number_format(floor($value['loan_success_order_money_3'])/100):0; ?>
                    </td>
                </tr>
        <?php endforeach; ?>
        <?php foreach ($info as $key=> $packageNameData): ?>
        <?php foreach ($packageNameData as $packageName=> $value): ?>
            <tr class="hover" style="<?php echo date('w', $value['unix_time_key']) == 0 || date('w', $value['unix_time_key']) == 6 ?'background:#F5F9FD':'';?>">
                <!-- 借款信息 -->
                <td class="td25"><?php echo date('n-j',strtotime($key)); ?></td>
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo Html::encode($packageName); ?></td>
                <!-- 所有用户 -->
                <td class="td25">
                    <?php echo isset($value['apply_person_num_0'])?$value['apply_person_num_0'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_num_0'])?$value['apply_order_num_0'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_money_0'])?number_format(floor($value['apply_order_money_0'])/100):0; ?>
                </td>
                <td class="td25">
                    <?php echo isset($value['audit_pass_person_num_0'])?$value['audit_pass_person_num_0'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_num_0'])?$value['audit_pass_order_num_0'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_money_0'])?number_format(floor($value['audit_pass_order_money_0'])/100):0; ?>
                </td>
                <td class="td25">
                    <?php echo (!empty($value['apply_person_num_0'])) ? sprintf("%0.2f",($value['audit_pass_person_num_0']/$value['apply_person_num_0'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['apply_order_num_0'])) ? sprintf("%0.2f",($value['audit_pass_order_num_0']/$value['apply_order_num_0'])*100)."%" : '-'; ?>
                </td>
                <td class="td25">
                    <?php echo isset($value['withdraw_person_num_0'])?$value['withdraw_person_num_0'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_num_0'])?$value['withdraw_order_num_0'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_money_0'])?number_format(floor($value['withdraw_order_money_0'])/100):0; ?>
                </td>
                <td class="td25">
                    <?php echo (!empty($value['audit_pass_person_num_0'])) ? sprintf("%0.2f",($value['withdraw_person_num_0']/$value['audit_pass_person_num_0'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['audit_pass_order_num_0'])) ? sprintf("%0.2f",($value['withdraw_order_num_0']/$value['audit_pass_order_num_0'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="border-right:1px solid #A9A9A9;">
                    <?php echo isset($value['loan_success_person_num_0'])?$value['loan_success_person_num_0'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_num_0'])?$value['loan_success_order_num_0'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_money_0'])?number_format(floor($value['loan_success_order_money_0'])/100):0; ?>
                </td>

                <!-- 新用户 -->
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['apply_person_num_1'])?$value['apply_person_num_1'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_num_1'])?$value['apply_order_num_1'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_money_1'])?number_format(floor($value['apply_order_money_1'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['audit_pass_person_num_1'])?$value['audit_pass_person_num_1'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_num_1'])?$value['audit_pass_order_num_1'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_money_1'])?number_format(floor($value['audit_pass_order_money_1'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo (!empty($value['apply_person_num_1'])) ? sprintf("%0.2f",($value['audit_pass_person_num_1']/$value['apply_person_num_1'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['apply_order_num_1'])) ? sprintf("%0.2f",($value['audit_pass_order_num_1']/$value['apply_order_num_1'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['withdraw_person_num_1'])?$value['withdraw_person_num_1'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_num_1'])?$value['withdraw_order_num_1'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_money_1'])?number_format(floor($value['withdraw_order_money_1'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo (!empty($value['audit_pass_person_num_1'])) ? sprintf("%0.2f",($value['withdraw_person_num_1']/$value['audit_pass_person_num_1'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['audit_pass_order_num_1'])) ? sprintf("%0.2f",($value['withdraw_order_num_1']/$value['audit_pass_order_num_1'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                    <?php echo isset($value['loan_success_person_num_1'])?$value['loan_success_person_num_1'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_num_1'])?$value['loan_success_order_num_1'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_money_1'])?number_format(floor($value['loan_success_order_money_1'])/100):0; ?>
                </td>

                <!-- 老用户 -->
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['apply_person_num_2'])?$value['apply_person_num_2'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_num_2'])?$value['apply_order_num_2'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_money_2'])?number_format(floor($value['apply_order_money_2'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['audit_pass_person_num_2'])?$value['audit_pass_person_num_2'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_num_2'])?$value['audit_pass_order_num_2'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_money_2'])?number_format(floor($value['audit_pass_order_money_2'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo (!empty($value['apply_person_num_2'])) ? sprintf("%0.2f",($value['audit_pass_person_num_2']/$value['apply_person_num_2'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['apply_order_num_2'])) ? sprintf("%0.2f",($value['audit_pass_order_num_2']/$value['apply_order_num_2'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['withdraw_person_num_2'])?$value['withdraw_person_num_2'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_num_2'])?$value['withdraw_order_num_2'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_money_2'])?number_format(floor($value['withdraw_order_money_2'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo (!empty($value['audit_pass_person_num_2'])) ? sprintf("%0.2f",($value['withdraw_person_num_2']/$value['audit_pass_person_num_2'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['audit_pass_order_num_2'])) ? sprintf("%0.2f",($value['withdraw_order_num_2']/$value['audit_pass_order_num_2'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                    <?php echo isset($value['loan_success_person_num_2'])?$value['loan_success_person_num_2'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_num_2'])?$value['loan_success_order_num_2'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_money_2'])?number_format(floor($value['loan_success_order_money_2'])/100):0; ?>
                </td>

                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['apply_person_num_3'])?$value['apply_person_num_3'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_num_3'])?$value['apply_order_num_3'] : 0; ?><br/>
                    <?php echo isset($value['apply_order_money_3'])?number_format(floor($value['apply_order_money_3'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['audit_pass_person_num_3'])?$value['audit_pass_person_num_3'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_num_3'])?$value['audit_pass_order_num_3'] : 0; ?><br/>
                    <?php echo isset($value['audit_pass_order_money_3'])?number_format(floor($value['audit_pass_order_money_3'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo (!empty($value['apply_person_num_3'])) ? sprintf("%0.2f",($value['audit_pass_person_num_3']/$value['apply_person_num_3'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['apply_order_num_3'])) ? sprintf("%0.2f",($value['audit_pass_order_num_3']/$value['apply_order_num_3'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['withdraw_person_num_3'])?$value['withdraw_person_num_3'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_num_3'])?$value['withdraw_order_num_3'] : 0; ?><br/>
                    <?php echo isset($value['withdraw_order_money_3'])?number_format(floor($value['withdraw_order_money_3'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo (!empty($value['audit_pass_person_num_3'])) ? sprintf("%0.2f",($value['withdraw_person_num_3']/$value['audit_pass_person_num_3'])*100)."%" : '-'; ?><br/>
                    <?php echo (!empty($value['audit_pass_order_num_3'])) ? sprintf("%0.2f",($value['withdraw_order_num_3']/$value['audit_pass_order_num_3'])*100)."%" : '-'; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                    <?php echo isset($value['loan_success_person_num_3'])?$value['loan_success_person_num_3'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_num_3'])?$value['loan_success_order_num_3'] : 0; ?><br/>
                    <?php echo isset($value['loan_success_order_money_3'])?number_format(floor($value['loan_success_order_money_3'])/100):0; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
    <?php if (empty($info)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>
<script type="text/javascript">
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }
</script>