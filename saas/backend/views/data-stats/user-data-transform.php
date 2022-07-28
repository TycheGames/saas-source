<?php

use yii\widgets\ActiveForm;
use backend\components\widgets\LinkPager;
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\stats\UserOperationData;


$this->showsubmenu(Yii::T('common', 'Daily user data conversion'));
/**
 * @var backend\components\View $this
 */
?>
<style>
    .header tr th{width: 100px;}
    .total tr td{width: 100px;}
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:<?php echo Yii::T('common', 'Default all channels') ?>});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form', 'method' => 'get', 'action' => Url::to(['data-stats/user-data-transform']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($addStart); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($addEnd); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('app_market', \yii\helpers\ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<input type="submit" name="search_submit" id="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<!--更新时间：--><?//= $update_time; ?><!--   (每小时更新一次)-->
<?php ActiveForm::end(); ?>
<?php if (!empty($message)): ?>
    <div class="no-result"><?php echo $message;?></div>
<?php endif; ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead class="head">
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'channel') ?></th>

            <th><?php echo Yii::T('common', 'Registration number') ?></th>
            <th><?php echo Yii::T('common', 'Basic information entry') ?>uv/pv</th>
            <th><?php echo Yii::T('common', 'Basic information submission') ?>uv/pv</th>
            <th><?php echo Yii::T('common', 'Basic information submitted successfully') ?>uv/pv</th>
            <th><?php echo Yii::T('common', 'Basic information submission failed') ?>uv/pv</th>
            <th><?php echo Yii::T('common', 'Conversion rate to registration') ?></th>

            <th>KYC <?php echo Yii::T('common', 'enter') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication successful') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication failed') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th>KYC <?php echo Yii::T('common', 'Failure rate') ?></th>

            <th><?php echo Yii::T('common', 'Contact entry') ?>uv/pv</th>
            <th><?php echo Yii::T('common', 'Contact save') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Contact saved successful') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Contact saved failed') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Conversion rate to registration') ?></th>
            <th><?php echo Yii::T('common', 'Number of successful three-factor authentication') ?></th>
            <th><?php echo Yii::T('common', 'Conversion rate to registration') ?></th>

            <th><?php echo Yii::T('common', 'Bind card entry') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Bind card submission') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Bind card submitted successfully') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Bind card submitted failed') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Bind card submitted failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th><?php echo Yii::T('common', 'Card binding verification (small money)') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Success to bind the card for verification (success to match the name of small money)') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Failed to bind the card for verification (failed to match the name of small money)') ?>uv/pv</th>

<!--            <th>活体检查提交uv/pv</th>-->
<!--            <th>活体检查提交成功uv/pv</th>-->
<!--            <th>活体检查提交失败uv/pv</th>-->
<!--            <th>活体检测提交失败uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->
<!---->
<!--            <th>人脸对比提交uv/pv</th>-->
<!--            <th>人脸对比提交通过uv/pv</th>-->
<!--            <th>人脸对比提交不通过uv/pv</th>-->
<!--            <th>人脸对比提交不通过uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->
<!---->
<!--            <th>pan ocr提交uv/pv</th>-->
<!--            <th>pan ocr提交成功uv/pv</th>-->
<!--            <th>pan ocr提交失败uv/pv</th>-->
<!--            <th>pan ocr提交失败uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->
<!---->
<!--            <th>pan验真提交uv/pv</th>-->
<!--            <th>pan验真提交通过uv/pv</th>-->
<!--            <th>pan验真提交不通过uv/pv</th>-->
<!--            <th>pan验真提交不通过uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->
<!---->
<!--            <th>aadhaar ocr提交uv/pv</th>-->
<!--            <th>aadhaar ocr提交成功uv/pv</th>-->
<!--            <th>aadhaar ocr提交失败uv/pv</th>-->
<!--            <th>aadhaar ocr提交失败uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->
<!---->
<!--            <th>aadhaar验真提交uv/pv</th>-->
<!--            <th>aadhaar验真提交通过uv/pv</th>-->
<!--            <th>aadhaar验真提交不通过uv/pv</th>-->
<!--            <th>aadhaar验真提交不通过uv（去重成功）</th>-->
<!--            <th><?php echo Yii::T('common', 'Failure rate') ?></th>-->

            <th><?php echo Yii::T('common', 'Number of orders applied by new users') ?></th>
            <th><?php echo Yii::T('common', 'Conversion rate of applications registered to new users') ?></th>
            <th><?php echo Yii::T('common', 'Number of new user applicants') ?></th>
            <th><?php echo Yii::T('common', 'Conversion rate of number of new user applicants registered') ?></th>
            <th><?php echo Yii::T('common', 'Number of orders passed by risk control for new users') ?></th>
            <th><?php echo Yii::T('common', 'New user pass rate') ?></th>
            <th><?php echo Yii::T('common', 'Registration to risk control conversion rate') ?></th>

            <th><?php echo Yii::T('common', 'Number of risk passes (machine audit passes)') ?></th>
            <th><?php echo Yii::T('common', 'Number of risk passes (Pass the manual information review)') ?></th>
            <th><?php echo Yii::T('common', 'The number of orders approved by the labor card certification on that day') ?></th>
            <th><?php echo Yii::T('common', 'Number of orders approved by card certification') ?></th>
            <th><?php echo Yii::T('common', 'Number of orders rejected by card certification') ?></th>
            <th><?php echo Yii::T('common', 'Pass rate of card certification') ?></th>
            <th><?php echo Yii::T('common', 'Conversion rate of registration to card binding (card binding succeeded + card certification passed)') ?></th>
            <th><?php echo Yii::T('common', 'Number of loan orders generated') ?></th>
            <th><?php echo Yii::T('common', 'Number of successful loan orders') ?></th>
            <th><?php echo Yii::T('common', 'Number of successful loan orders of new users') ?></th>
            <th><?php echo Yii::T('common', 'Conversion rate of loans registered to new users') ?></th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalDataList as $date => $totalData): ?>
            <tr <?= ($date != '') ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$date == '' ? Yii::T('common', 'summary') : $date ?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Yii::T('common', 'summary') ?></td>

                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_REGISTER] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_GET_BASIC_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_GET_BASIC_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BASIC_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_UV] ?? 0) / $totalData[UserOperationData::TYPE_USER_REGISTER] * 100); ?>%</td>

                <!-- KYC -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_GET_KYC_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_GET_KYC_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)) / $totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] * 100)?>%</td>

                <!--联系人-->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_GET_CONTACT_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_GET_CONTACT_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_CONTACT_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_UV] ?? 0 )/ $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>

                <!-- 三要素认证 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0?>/<?=$totalData[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0 )/ $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
                <!-- 绑卡 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_GET_BANK_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_GET_BANK_INFO_PV] ?? 0?></td>
                <!-- 绑卡提交 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_BANK_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_BANK_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_BANK_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_BANK_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_BANK_UV] * 100)?>%</td>
                <!-- 绑卡校验 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_BANK_VERIFY_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_BANK_VERIFY_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_BANK_VERIFY_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_BANK_VERIFY_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_BANK_VERIFY_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_BANK_VERIFY_FAIL_PV] ?? 0?></td>


                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] ?? 0) / $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_NEW_USER_APPLY_BY_PERSON] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_NEW_USER_APPLY_BY_PERSON] ?? 0) / $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_NEW_USER_APPLY_ORDER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0) / $totalData[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] * 100)?>%</td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0) / $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>

                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_RISK_CREDIT_PASS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_MANUAL_CREDIT_PASS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty(($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0)
                        + ($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0)) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0) / (($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0)
                                + ($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0)) * 100)?>%</td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>
                    <?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f", (($totalData[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0) + ($totalData[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0)) /$totalData[UserOperationData::TYPE_USER_REGISTER]  * 100);
                    ?>%</td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_LOAN_ORDER] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_LOAN_ORDER_SUCCESS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_NEW_LOAN_ORDER_SUCCESS] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_NEW_LOAN_ORDER_SUCCESS] ?? 0) / $totalData[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>

            </tr>
        <?php endforeach;?>
        </thead>
        <tbody>
        <?php foreach ($dateData as $date => $value):

                foreach ($value as $appMarket => $val):
            ?>

            <tr class="hover">
                <td><?php echo $date; ?></td>
                <td><?php echo $appMarket; ?></td>

                <td><?php echo $val[UserOperationData::TYPE_USER_REGISTER] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_GET_BASIC_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_GET_BASIC_INFO_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BASIC_FAIL_PV] ?? 0  ?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_BASIC_SUCCESS_UV] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100); ?>%</td>

                <!--KYC-->
                <td><?php echo $val[UserOperationData::TYPE_GET_KYC_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_GET_KYC_INFO_PV] ?? 0  ?></td>

                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)?></td>
                <td><?= empty($val[UserOperationData::TYPE_SUBMIT_KYC_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_KYC_UV] - ($val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)) / $val[UserOperationData::TYPE_SUBMIT_KYC_UV] * 100)?>%</td>

                <!--联系人 -->
                <td><?php echo $val[UserOperationData::TYPE_GET_CONTACT_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_GET_CONTACT_INFO_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_CONTACT_FAIL_PV] ?? 0  ?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_CONTACT_SUCCESS_UV] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100); ?>%</td>

                <!-- 三要素认证 -->
                <td><?=$val[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0?>/<?=$val[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_THREE_ELEMENT_VERIFY] ?? 0 )/ $val[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>

                <!-- 绑卡 -->
                <td><?php echo $val[UserOperationData::TYPE_GET_BANK_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_GET_BANK_INFO_PV] ?? 0  ?></td>
                <!-- 绑卡提交 -->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_BANK_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_BANK_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_BANK_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_BANK_UV] - ($val[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_BANK_UV] * 100)?>%</td>
                <!-- 绑卡校验 -->
                <td><?php echo $val[UserOperationData::TYPE_BANK_VERIFY_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_BANK_VERIFY_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_BANK_VERIFY_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_BANK_VERIFY_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_BANK_VERIFY_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_BANK_VERIFY_FAIL_PV] ?? 0  ?></td>

                <td><?php echo $val[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] ?? 0  ?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
                <td><?php echo $val[UserOperationData::TYPE_NEW_USER_APPLY_BY_PERSON] ?? 0  ?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_NEW_USER_APPLY_BY_PERSON] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
                <td><?=$val[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0?></td>
                <td><?= empty($val[UserOperationData::TYPE_NEW_USER_APPLY_ORDER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0) / $val[UserOperationData::TYPE_NEW_USER_APPLY_ORDER] * 100)?>%</td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_NEW_USER_RISK_PASS] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>

                <td><?php echo $val[UserOperationData::TYPE_USER_RISK_CREDIT_PASS] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_USER_MANUAL_CREDIT_PASS] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0  ?></td>
                <td><?= empty(($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0)
                        + ($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0)) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0) / (($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0)
                                + ($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] ?? 0)) * 100)?>%</td>
                <td>
                    <?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f", (($val[UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] ?? 0) + ($val[UserOperationData::TYPE_SUBMIT_BANK_SUCCESS_UV] ?? 0)) /$val[UserOperationData::TYPE_USER_REGISTER]  * 100);
                    ?>%</td>
                <td><?php echo $val[UserOperationData::TYPE_LOAN_ORDER] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_LOAN_ORDER_SUCCESS] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_NEW_LOAN_ORDER_SUCCESS] ?? 0  ?></td>
                <td><?= empty($val[UserOperationData::TYPE_USER_REGISTER]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_NEW_LOAN_ORDER_SUCCESS] ?? 0) / $val[UserOperationData::TYPE_USER_REGISTER] * 100)?>%</td>
            </tr>
        <?php
                endforeach;
             endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($dateData)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</form>


<script type="text/javascript">
    function showDateData() {
        if ($(".date-data").is(":hidden")) {
            $(".date-data").show();

        } else {
            $(".date-data").hide();
        }
    }
</script>