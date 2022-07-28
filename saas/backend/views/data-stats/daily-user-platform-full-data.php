<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use backend\models\Merchant;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
$this->showsubmenu(Yii::T('common', 'User daily report (full volume -all platform)'));
/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'User daily report (full volume -all platform)') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
        $('.media-source-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all mediaSource') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/daily-user-platform-full-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($add_start); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($add_end); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
appMarket：<?php  echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    $searchList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
mediaSource：<?php  echo Html::dropDownList('media_source', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('media_source', [])),
    $mediaSourceList,['class' => 'form-control media-source-select', 'multiple' => 'multiple', 'id' => 'media_source']); ?>&nbsp;
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
        Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<?php echo Yii::T('common', 'packageName') ?>：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
    $packageNameList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('export_direct');return true;" class="btn" />
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th>app_market</th>
            <th>media_source</th>
            <th>package name</th>
            <th><?php echo Yii::T('common', 'Registration number') ?></th>
            <th><?php echo Yii::T('common', 'Number of basic certifications') ?></th>
            <th><?php echo Yii::T('common', 'Number of identity auth') ?></th>
            <th><?php echo Yii::T('common', 'Number of contact auth') ?></th>
            <th><?php echo Yii::T('common', 'Order application (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Number of platform new user applicants') ?></th>
            <th><?php echo Yii::T('common', 'Register to Platform New Guest Application') ?></th>
            <th><?php echo Yii::T('common', 'Platform New user applies for order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Platform Old user applies for order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Approved order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Number of platform new users approved') ?></th>
            <th><?php echo Yii::T('common', 'Registered to Platform New Guest Risk Control Passed') ?></th>
            <th><?php echo Yii::T('common', 'Platform new customer passing rate') ?></th>
            <th><?php echo Yii::T('common', 'Platform new user approved (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Platform old user approved (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Approve and bind the card to pass the order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Number of platform new users approved and passed card binding') ?></th>
            <th><?php echo Yii::T('common', 'Register to Platform New Guest Tied Card') ?></th>
            <th><?php echo Yii::T('common', 'Platform new user approves and binds the card to pass the order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Platform old user approves and binds the card to pass the order (number/amount)') ?></th>
            <?php if($isShow):?>
            <th><?php echo Yii::T('common', 'Platform new customer withdrawals') ?></th>
            <th><?php echo Yii::T('common', 'Register to Platform New Guest Withdrawal') ?></th>
            <th><?php echo Yii::T('common', 'Order number of platform regular customers') ?></th>
            <?php endif;?>
            <th><?php echo Yii::T('common', 'Loan order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Platform new user loan order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'Register to Platform New Guest Loan') ?></th>
            <th><?php echo Yii::T('common', 'Platform old user loan order (number/amount)') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalData as $value): ?>
        <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['media_source'] ?? '-'); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['package_name'] ?? '-'); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['reg_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['basic_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['identity_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['contact_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['order_num'].'/'.number_format($value['order_amount']/100); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_order_user_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_order_num'].'/'.number_format($value['platform_new_order_amount']/100); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_old_order_num'].'/'.number_format($value['platform_old_order_amount']/100); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['audit_pass_order_num'].'/'.number_format($value['audit_pass_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_audit_pass_order_user_num'];  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_audit_pass_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['platform_new_order_user_num']) ? sprintf("%0.2f",$value['platform_new_audit_pass_order_user_num']/$value['platform_new_order_user_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_audit_pass_order_num'].'/'.number_format($value['platform_new_audit_pass_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_old_audit_pass_order_num'].'/'.number_format($value['platform_old_audit_pass_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['bind_card_pass_order_num'].'/'.number_format($value['bind_card_pass_order_amount']/100); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_bind_card_pass_order_user_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_bind_card_pass_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_bind_card_pass_order_num'].'/'.number_format($value['platform_new_bind_card_pass_order_amount']/100); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_old_bind_card_pass_order_num'].'/'.number_format($value['platform_old_bind_card_pass_order_amount']/100); ?></td>
            <?php if($isShow):?>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_withdraw_success_order_user_num']; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_withdraw_success_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_old_withdraw_success_order_num']; ?></td>
            <?php endif;?>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['loan_success_order_num'].'/'.number_format($value['loan_success_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_new_loan_success_order_num'].'/'.number_format($value['platform_new_loan_success_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_loan_success_order_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['platform_old_loan_success_order_num'].'/'.number_format($value['platform_old_loan_success_order_amount']/100)  ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>--</td>
        </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
                <td><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['media_source'] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['package_name'] ?? '-'); ?></td>
                <td><?php echo $value['reg_num']; ?></td>
                <td><?php echo $value['basic_num']; ?></td>
                <td><?php echo $value['identity_num']; ?></td>
                <td><?php echo $value['contact_num']; ?></td>
                <td><?php echo $value['order_num'].'/'.number_format($value['order_amount']/100); ?></td>
                <td><?php echo $value['platform_new_order_user_num']; ?></td>
                <td><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td><!--注册到新客申请-->
                <td><?php echo $value['platform_new_order_num'].'/'.number_format($value['new_order_amount']/100); ?></td>
                <td><?php echo $value['platform_old_order_num'].'/'.number_format($value['platform_old_order_amount']/100); ?></td>
                <td><?php echo $value['audit_pass_order_num'].'/'.number_format($value['audit_pass_order_amount']/100)  ?></td>
                <td><?php echo $value['platform_new_audit_pass_order_user_num']; ?></td>
                <td><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_audit_pass_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td><!--注册到新客风控通过-->
                <td><?php echo !empty($value['platform_new_order_user_num']) ? sprintf("%0.2f",$value['platform_new_audit_pass_order_user_num']/$value['platform_new_order_user_num']*100).'%' : '-' ; ?></td><!--新客过件率-->
                <td><?php echo $value['platform_new_audit_pass_order_num'].'/'.number_format($value['platform_new_audit_pass_order_amount']/100)  ?></td>
                <td><?php echo $value['platform_old_audit_pass_order_num'].'/'.number_format($value['platform_old_audit_pass_order_amount']/100)  ?></td>
                <td><?php echo $value['bind_card_pass_order_num'].'/'.number_format($value['bind_card_pass_order_amount']/100); ?></td>
                <td><?php echo $value['platform_new_bind_card_pass_order_user_num']; ?></td>
                <td><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_bind_card_pass_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td><!--注册到新客绑卡-->
                <td><?php echo $value['platform_new_bind_card_pass_order_num'].'/'.number_format($value['platform_new_bind_card_pass_order_amount']/100); ?></td>
                <td><?php echo $value['platform_old_bind_card_pass_order_num'].'/'.number_format($value['platform_old_bind_card_pass_order_amount']/100); ?></td>
                <?php if($isShow):?>
                <td><?php echo $value['platform_new_withdraw_success_order_user_num']; ?></td>
                <td><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_withdraw_success_order_user_num']/$value['reg_num']*100) .'%': '-' ; ?></td>
                <td><?php echo $value['platform_old_withdraw_success_order_num']; ?></td>
                <?php endif;?>
                <td><?php echo $value['loan_success_order_num'].'/'.number_format($value['loan_success_order_amount']/100)  ?></td>
                <td><?php echo $value['platform_new_loan_success_order_num'].'/'.number_format($value['platform_new_loan_success_order_amount']/100)  ?></td>
                <td><?php echo !empty($value['reg_num']) ? sprintf("%0.2f",$value['platform_new_loan_success_order_num']/$value['reg_num']*100) .'%' : '-' ; ?></td><!--注册到新客放款-->
                <td><?php echo $value['platform_old_loan_success_order_num'].'/'.number_format($value['platform_old_loan_success_order_amount']/100)  ?></td>
                <td><?php echo isset($value['updated_at']) ? date('Y-m-d H:i',$value['updated_at']) : '-'?></td>
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