<?php

use yii\widgets\ActiveForm;
use backend\components\widgets\LinkPager;
use yii\helpers\Url;
use common\models\stats\UserOperationData;


$this->showsubmenu(Yii::T('common', 'Daily user KYC conversions'));
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
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= \yii\helpers\Html::encode($addStart); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= \yii\helpers\Html::encode($addEnd); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('app_market', \yii\helpers\ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<input type="submit" name="search_submit" id="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<!--更新时间：--><?//= $update_time; ?><!--   (每小时更新一次)-->
<?php ActiveForm::end(); ?>
<?php if (!empty($message)): ?>
    <div class="no-result"><?php echo \yii\helpers\Html::encode($message);?></div>
<?php endif; ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead class="head">
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'channel') ?></th>

            <th>KYC <?php echo Yii::T('common', 'enter') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication successful') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication failed') ?> uv/pv</th>
            <th>KYC <?php echo Yii::T('common', 'Authentication failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th>KYC <?php echo Yii::T('common', 'Failure rate') ?></th>

            <th><?php echo Yii::T('common', 'Biopsy submission') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Biopsy submission successful') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Biopsy submission failed') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Biopsy submission failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th>pan ocr <?php echo Yii::T('common', 'Submission') ?> uv/pv</th>
            <th>pan ocr <?php echo Yii::T('common', 'Submission successful') ?> uv/pv</th>
            <th>pan ocr <?php echo Yii::T('common', 'Submission failed') ?> uv/pv</th>
            <th>pan ocr <?php echo Yii::T('common', 'Submission failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th>aadhaar ocr <?php echo Yii::T('common', 'Submission') ?> uv/pv</th>
            <th>aadhaar ocr <?php echo Yii::T('common', 'Submission successful') ?> uv/pv</th>
            <th>aadhaar ocr <?php echo Yii::T('common', 'Submission failed') ?> uv/pv</th>
            <th>aadhaar ocr <?php echo Yii::T('common', 'Submission failed') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th>pan <?php echo Yii::T('common', 'Check true submitted') ?> uv/pv</th>
            <th>pan <?php echo Yii::T('common', 'Check true submitted pass') ?> uv/pv</th>
            <th>pan <?php echo Yii::T('common', 'Check true submitted no pass') ?> uv/pv</th>
            <th>pan <?php echo Yii::T('common', 'Check true submitted no pass') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th>aadhaar <?php echo Yii::T('common', 'Check true submitted') ?> uv/pv</th>
            <th>aadhaar <?php echo Yii::T('common', 'Check true submitted pass') ?> uv/pv</th>
            <th>aadhaar <?php echo Yii::T('common', 'Check true submitted no pass') ?> uv/pv</th>
            <th>aadhaar <?php echo Yii::T('common', 'Check true submitted no pass') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>

            <th><?php echo Yii::T('common', 'Face contrast submitted') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Face contrast submitted pass') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Face contrast submitted no pass') ?> uv/pv</th>
            <th><?php echo Yii::T('common', 'Face contrast submitted no pass') ?> uv（<?php echo Yii::T('common', 'Duplicate success') ?>）</th>
            <th><?php echo Yii::T('common', 'Failure rate') ?></th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalDataList as $date => $totalData): ?>
            <tr <?= ($date != '') ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$date == '' ? Yii::T('common', 'summary') : $date ?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Yii::T('common', 'summary') ?></td>

                <!-- KYC -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_GET_KYC_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_GET_KYC_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_KYC_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)) / $totalData[UserOperationData::TYPE_SUBMIT_KYC_UV] * 100)?>%</td>

                <!-- 活体检测 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] * 100)?>%</td>

                <!--pan ocr-->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] * 100)?>%</td>

                <!--aad ocr-->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] * 100)?>%</td>

                <!--pan 验真-->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] * 100)?>%</td>

                <!--aad 验真-->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] * 100)?>%</td>

                <!-- 人脸对比 -->
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_FAIL_UV] ?? 0?>/<?=$totalData[UserOperationData::TYPE_SUBMIT_FR_VER_FAIL_PV] ?? 0?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=($totalData[UserOperationData::TYPE_SUBMIT_FR_VER_UV] ?? 0) - ($totalData[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0)?></td>
                <td <?= ($date == '') ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?=empty($totalData[UserOperationData::TYPE_SUBMIT_FR_VER_UV]) ? '-' :
                        sprintf("%0.2f",($totalData[UserOperationData::TYPE_SUBMIT_FR_VER_UV] - ($totalData[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0))/ $totalData[UserOperationData::TYPE_SUBMIT_FR_VER_UV] * 100)?>%</td>
            </tr>
        <?php endforeach;?>
        </thead>
        <tbody>
        <?php foreach ($dateData as $date => $value):

                foreach ($value as $appMarket => $val):
            ?>

            <tr class="hover">
                <td><?php echo $date; ?></td>
                <td><?php echo \yii\helpers\Html::encode($appMarket); ?></td>

                <!--KYC-->
                <td><?php echo $val[UserOperationData::TYPE_GET_KYC_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_GET_KYC_INFO_PV] ?? 0  ?></td>

                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_KYC_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_KYC_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)?></td>
                <td><?= empty($val[UserOperationData::TYPE_SUBMIT_KYC_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_KYC_UV] - ($val[UserOperationData::TYPE_SUBMIT_KYC_SUCCESS_UV] ?? 0)) / $val[UserOperationData::TYPE_SUBMIT_KYC_UV] * 100)?>%</td>

                <!-- 活体检测 -->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_INFO_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_FR_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] - ($val[UserOperationData::TYPE_SUBMIT_FR_INFO_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_FR_INFO_UV] * 100)?>%</td>

                <!-- pan ocr -->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] - ($val[UserOperationData::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_PAN_INFO_UV] * 100)?>%</td>

                <!-- aad ocr-->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] - ($val[UserOperationData::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_AAD_INFO_UV] * 100)?>%</td>

                <!-- pan验真 -->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_PAN_VER_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_PAN_VER_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] - ($val[UserOperationData::TYPE_SUBMIT_PAN_VER_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_PAN_VER_UV] * 100)?>%</td>

                <!-- aad 验真-->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_AAD_VER_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_AAD_VER_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] - ($val[UserOperationData::TYPE_SUBMIT_AAD_VER_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_AAD_VER_UV] * 100)?>%</td>

                <!--人脸对比 -->
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_PV] ?? 0  ?></td>
                <td><?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_FAIL_UV] ?? 0  ?>/<?php echo $val[UserOperationData::TYPE_SUBMIT_FR_VER_FAIL_PV] ?? 0  ?></td>
                <td><?=($val[UserOperationData::TYPE_SUBMIT_FR_VER_UV] ?? 0) - ($val[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0)?></td>
                <td><?=empty($val[UserOperationData::TYPE_SUBMIT_FR_VER_UV]) ? '-' :
                        sprintf("%0.2f",($val[UserOperationData::TYPE_SUBMIT_FR_VER_UV] - ($val[UserOperationData::TYPE_SUBMIT_FR_VER_SUCCESS_UV] ?? 0))/ $val[UserOperationData::TYPE_SUBMIT_FR_VER_UV] * 100)?>%</td>
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