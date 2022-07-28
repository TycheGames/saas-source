<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use backend\models\Merchant;

/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'User Daily Report (Registration)') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all channels') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-user-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($add_start); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($add_end); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo \yii\helpers\Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<?php if($isNotMerchantAdmin): ?>
<?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
    Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('export_direct');return true;" class="btn" />
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th>app_market</th>
            <th><?php echo Yii::T('common', 'Registration number') ?></th>
            <th><?php echo Yii::T('common', 'Number of basic certifications') ?></th>
            <th><?php echo Yii::T('common', 'Number of identity auth') ?></th>
            <th><?php echo Yii::T('common', 'Number of contact auth') ?></th>
            <th><?php echo Yii::T('common', 'Order application (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Approved order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Approve and bind the card to pass the order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Loan order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['reg_num'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['basic_num']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['identity_num']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['contact_num']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['order_num'].'/'.number_format($value['order_amount']/100)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['audit_pass_order_num'].'/'.number_format($value['audit_pass_order_amount']/100)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['bind_card_pass_order_num'].'/'.number_format($value['bind_card_pass_order_amount']/100)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['loan_success_order_num'].'/'.number_format($value['loan_success_order_amount']/100)); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>--</td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
                <td><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['reg_num']); ?></td>
                <td><?php echo Html::encode($value['basic_num']); ?></td>
                <td><?php echo Html::encode($value['identity_num']); ?></td>
                <td><?php echo Html::encode($value['contact_num']); ?></td>
                <td><?php echo Html::encode($value['order_num'].'/'.number_format($value['order_amount']/100)); ?></td>
                <td><?php echo Html::encode($value['audit_pass_order_num'].'/'.number_format($value['audit_pass_order_amount']/100));  ?></td>
                <td><?php echo Html::encode($value['bind_card_pass_order_num'].'/'.number_format($value['bind_card_pass_order_amount']/100)); ?></td>
                <td><?php echo Html::encode($value['loan_success_order_num'].'/'.number_format($value['loan_success_order_amount']/100));  ?></td>
                <td><?php echo Html::encode(isset($value['updated_at']) ? date('Y-m-d H:i',$value['updated_at']) : '-');?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
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