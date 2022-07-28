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
$this->showsubmenu(Yii::T('common', 'User structure source export money'), array(
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
        $('.package-name-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<title><?php echo Yii::T('common', 'Daily repayment order data') ?></title>
<script language="javascript" type="text/javascript"
        src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form', 'method' => 'get', 'action' =>  Url::to(['data-stats/user-structure-source-export-money']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php
echo Html::dropDownList('search_date', Yii::$app->getRequest()->get('search_date', '2'), array(1 => Yii::T('common', 'Loan date'), 2 => Yii::T('common', 'Repayment date')));
?>

<input type="text"
       value="<?= Html::encode(empty(Yii::$app->request->get('begin_created_at')) ? date("Y-m-d", time() - 7 * 86400) : Yii::$app->request->get('begin_created_at')); ?>"
       name="begin_created_at"
       onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text"
                                            value="<?= Html::encode(empty(Yii::$app->request->get('end_created_at')) ? date("Y-m-d", time() + 86400) : Yii::$app->request->get('end_created_at')); ?>"
                                            name="end_created_at"
                                            onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
&nbsp;

<?php echo Yii::T('common', 'Order package name') ?>：<?php echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])), $packageNameList,['class' => 'form-control package-name-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<?php echo Yii::T('common', 'merchant') ?>：<?php  echo Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
    Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;

<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
&nbsp;&nbsp;(<?php echo Yii::T('common', 'Update every 5 minutes') ?>)
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th colspan="2" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Loan information') ?></th>
            <th colspan="3" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
            <th colspan="3" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Source new loan new') ?></th>
            <th colspan="3" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Source new loan old') ?></th>
            <th colspan="3" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Source old loan new') ?></th>
            <th colspan="3" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Source old loan old') ?></th>
        </tr>
        <tr class="header">
            <!-- 借款信息 -->
            <th><?php echo Yii::T('common', 'Loan date') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Repayment date') ?></th>

            <!-- 所有用户 -->
            <th><?php echo Yii::T('common', 'Expire money') ?></th>
            <th><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'First overdue rate') ?></th>

            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'First overdue rate') ?></th>

            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'First overdue rate') ?></th>

            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'First overdue rate') ?></th>

            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'First overdue') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'First overdue rate') ?></th>

        </tr>
        <tr class="hover">
            <!-- 借款信息 -->
            <td><?php echo Yii::T('common', 'Summary information') ?></td>
            <td style="border-right:1px solid #A9A9A9;"></td>

            <!-- 所有用户 -->
            <td class="td25"><?php echo isset($total_info['expire_money_0']) ? $total_info['expire_money_0'] : 0; ?></td>
            <td class="td25"><?php echo isset($total_info['first_over_money_0']) ? $total_info['first_over_money_0'] : 0; ?></td>
            <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo empty($total_info['expire_money_0']) ? '-' : sprintf("%0.2f", ($total_info['first_over_money_0'] / ($total_info['expire_money_0'])) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['expire_money_1']) ? $total_info['expire_money_1'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['first_over_money_1']) ? $total_info['first_over_money_1'] : 0; ?></td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($total_info['expire_money_1']) ? '-' : sprintf("%0.2f", ($total_info['first_over_money_1'] / ($total_info['expire_money_1'])) * 100) . "%"; ?></td>


            <td class="td25"
                style="text-align:center;color:red"><?php echo isset($total_info['expire_money_2']) ? $total_info['expire_money_2'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:red"><?php echo isset($total_info['first_over_money_2']) ? $total_info['expire_money_2'] : 0; ?></td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($total_info['expire_money_2']) ? '-' : sprintf("%0.2f", ($total_info['first_over_money_2'] / ($total_info['expire_money_2'])) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['expire_money_3']) ? $total_info['expire_money_3'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:blue"><?php echo isset($total_info['first_over_money_3']) ? $total_info['expire_money_3'] : 0; ?></td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($total_info['expire_money_3']) ? '-' : sprintf("%0.2f", ($total_info['first_over_money_3'] / ($total_info['expire_money_3'])) * 100) . "%"; ?></td>

            <td class="td25"
                style="text-align:center;color:red"><?php echo isset($total_info['expire_money_4']) ? $total_info['expire_money_4'] : 0; ?></td>
            <td class="td25"
                style="text-align:center;color:red"><?php echo isset($total_info['first_over_money_4']) ? $total_info['expire_money_4'] : 0; ?></td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($total_info['expire_money_4']) ? '-' : sprintf("%0.2f", ($total_info['first_over_money_4'] / ($total_info['expire_money_4'])) * 100) . "%"; ?></td>
        </tr>
        <?php foreach ($info as $key => $value): ?>
            <tr class="hover"
                style="<?php echo date('w', $value['unix_time_key']) == 0 || date('w', $value['unix_time_key']) == 6 ? 'background:#deeffa' : ''; ?>">
                <!-- 借款信息 -->
                <?php $now_date_time = strtotime(date('Y-m-d', time()));?>
                <td class="td25"><?php echo date('m-d', strtotime($key) - $loan_term*86400); ?></td>
                <td class="td25"
                    style="border-right:1px solid #A9A9A9;"><?php echo date('n-j', strtotime($key)); ?></td>

                <!-- 所有用户 -->
                <td class="td25">
                    <?php echo isset($value['expire_money_0']) ? $value['expire_money_0'] : 0; ?>
                </td>
                <td class="td25">
                    <?php echo isset($value['first_over_money_0']) ? $value['first_over_money_0'] : 0; ?>
                </td>
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo empty($value['expire_money_0']) ? '-' : sprintf("%0.2f", ($value['first_over_money_0'] / $value['expire_money_0']) * 100) . "%"; ?></td>


                <!-- 全新、本新 -->
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['expire_money_1']) ? $value['expire_money_1'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['first_over_money_1']) ? $value['first_over_money_1'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($value['expire_money_1']) ? '-' : sprintf("%0.2f", ($value['first_over_money_1'] / $value['expire_money_1']) * 100) . "%"; ?></td>

                <!-- 全老、本新 -->
                <td class="td25"
                    style="text-align:center;color:red"><?php echo isset($value['expire_money_2']) ? $value['expire_money_2'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:red"><?php echo isset($value['first_over_money_2']) ? $value['first_over_money_2'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($value['expire_money_2']) ? '-' : sprintf("%0.2f", ($value['first_over_money_2'] / $value['expire_money_2']) * 100) . "%"; ?></td>

                <!-- 全老、本老 -->
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['expire_money_3']) ? $value['expire_money_3'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue"><?php echo isset($value['first_over_money_3']) ? $value['first_over_money_3'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo empty($value['expire_money_3']) ? '-' : sprintf("%0.2f", ($value['first_over_money_3'] / $value['expire_money_3']) * 100) . "%"; ?></td>

                <td class="td25"
                    style="text-align:center;color:red"><?php echo isset($value['expire_money_4']) ? $value['expire_money_4'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:red"><?php echo isset($value['first_over_money_4']) ? $value['first_over_money_4'] : 0; ?></td>
                <td class="td25"
                    style="text-align:center;color:red;border-right:1px solid red;"><?php echo empty($value['expire_money_4']) ? '-' : sprintf("%0.2f", ($value['first_over_money_4'] / $value['expire_money_4']) * 100) . "%"; ?></td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($info)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>


