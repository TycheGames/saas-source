<?php
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use backend\models\Merchant;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
/**
 * @var backend\components\View $this
 */
$rate = Yii::$app->request->get('from_st','0') ? 1.1 : 1;
$session = Yii::$app ->session;
$this->showsubmenu(Yii::T('common', 'Daily repeated borrowing'), array(
    array(Yii::T('common', 'Daily repayment and repeated borrowing'), Url::toRoute(['data-stats/day-again-repay-statistics']),  1),
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
<div id="tb_data">
    <?php $form = ActiveForm::begin(['method' => "get",'action' => ['data-stats/day-again-repay-statistics'],'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    <?php echo Yii::T('common', 'Display date') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('date_start', date("Y-m-d", time()-7*86400))); ?>"  id="date_start" name="date_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
    <?php echo Yii::T('common', 'to') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('date_end', date("Y-m-d", time()))) ?>"  name="date_end" id="date_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
    &nbsp
    appMarket：<?php echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])), $searchList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
    <?php if($isNotMerchantAdmin): ?>
        <?php echo Yii::T('common', 'merchant') ?>：<?php  echo Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
            Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
    <?php endif;?>
    <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn"  >
    &nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('exportdata');return true;" class="btn">
    <?php echo Yii::T('common', 'Last updated') ?>：<?= $update_time;?>（<?php echo Yii::T('common', 'Update every 30 minutes') ?>）
    <?php ActiveForm::end(); ?>
    <br/>

    <table class="tb tb2 fixpadding" id="tb" style="width: 3400px">
        <thead>
            <tr class="header">
                <th><?php echo Yii::T('common', 'date') ?></th>
                <th><?php echo Yii::T('common', 'channel') ?></th>
                <th><?php echo Yii::T('common', 'Number of repayments on the day') ?></th>
                <th><?php echo Yii::T('common', 'Number of repeat loan applicants') ?></th>
                <th><?php echo Yii::T('common', 'Repeated loan application rate') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful repeat borrowing') ?></th>
                <th><?php echo Yii::T('common', 'Repeated loan success amount') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan') ?></th>

                <th><?php echo Yii::T('common', 'Number of re loan applicants on that day') ?></th>
                <th><?php echo Yii::T('common', 'Application rate of repeated loan on that day') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful loan on the same day') ?></th>
                <th><?php echo Yii::T('common', 'Successful amount of repeated loan on that day') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan on that day') ?></th>

                <th><?php echo Yii::T('common', 'Number of repeated loan applicants within 7 days') ?></th>
                <th><?php echo Yii::T('common', 'Application rate of repeated loan within 7 days') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful loan within 7 days') ?></th>
                <th><?php echo Yii::T('common', 'Successful amount of repeated loan within 7 days') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan within 7 days') ?></th>

                <th><?php echo Yii::T('common', 'Number of repeated loan applicants within 10 days') ?></th>
                <th><?php echo Yii::T('common', 'Application rate of repeated loan within 10 days') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful loan within 10 days') ?></th>
                <th><?php echo Yii::T('common', 'Successful amount of repeated loan within 10 days') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan within 10 days') ?></th>

                <th><?php echo Yii::T('common', 'Number of repeated loan applicants within 30 days') ?></th>
                <th><?php echo Yii::T('common', 'Application rate of repeated loan within 30 days') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful loan within 30 days') ?></th>
                <th><?php echo Yii::T('common', 'Successful amount of repeated loan within 30 days') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan within 30 days') ?></th>

                <th><?php echo Yii::T('common', 'Number of repeated loan applicants over 31 days') ?></th>
                <th><?php echo Yii::T('common', 'Application rate of repeated loan over 31 days') ?></th>
                <th><?php echo Yii::T('common', 'Number of successful loan over 31 days') ?></th>
                <th><?php echo Yii::T('common', 'Successful amount of repeated loan over 31 days') ?></th>
                <th><?php echo Yii::T('common', 'Success rate of repeated loan over 31 days') ?></th>
            </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date'] ?? '-') ; ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['repay_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ_num']/$value['borrow_apply_num'])*100) .'%'?></td>

                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply1_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply1_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ1_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ1_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply1_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ1_num']/$value['borrow_apply1_num'])*100) .'%'?></td>

                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply7_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply7_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ7_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ7_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply7_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ7_num']/$value['borrow_apply7_num'])*100) .'%'?></td>

                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply14_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply14_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ14_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ14_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply14_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ14_num']/$value['borrow_apply14_num'])*100) .'%'?></td>

                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply30_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply30_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ30_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ30_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply30_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ30_num']/$value['borrow_apply30_num'])*100) .'%'?></td>

                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo $value['borrow_apply31_num']??0?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['repay_num'])? 0 : sprintf("%0.2f", ($value['borrow_apply31_num']/$value['repay_num'])*100) .'%'?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ31_num'];?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= $value['borrow_succ31_money']/100;?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?= empty($value['borrow_apply31_num'])? 0 : sprintf("%0.2f", ($value['borrow_succ31_num']/$value['borrow_apply31_num'])*100) .'%'?></td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <!--  显示每日流失情况数据   -->
        <?php foreach($data as $rows):?>
            <tr class="hover">
                <td class="td25"><?php echo Html::encode($rows['date'])?></td>
                <td class="td25"><?php echo Html::encode($rows['app_market'])?></td>
                <td class="td25"><?php echo $rows['repay_num']??0?></td>

                <td class="td25"><?php echo $rows['borrow_apply_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ_num']/$rows['borrow_apply_num'])*100) .'%'?></td>

                <td class="td25"><?php echo $rows['borrow_apply1_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply1_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ1_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ1_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply1_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ1_num']/$rows['borrow_apply1_num'])*100) .'%'?></td>

                <td class="td25"><?php echo $rows['borrow_apply7_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply7_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ7_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ7_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply7_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ7_num']/$rows['borrow_apply7_num'])*100) .'%'?></td>

                <td class="td25"><?php echo $rows['borrow_apply14_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply14_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ14_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ14_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply14_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ14_num']/$rows['borrow_apply14_num'])*100) .'%'?></td>

                <td class="td25"><?php echo $rows['borrow_apply30_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply30_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ30_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ30_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply30_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ30_num']/$rows['borrow_apply30_num'])*100) .'%'?></td>

                <td class="td25"><?php echo $rows['borrow_apply31_num']??0?></td>
                <td class="td25"><?= empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply31_num']/$rows['repay_num'])*100) .'%'?></td>
                <td class="td25"><?= $rows['borrow_succ31_num'];?></td>
                <td class="td25"><?= $rows['borrow_succ31_money']/100;?></td>
                <td class="td25"><?= empty($rows['borrow_apply31_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ31_num']/$rows['borrow_apply31_num'])*100) .'%'?></td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($day_lose_data)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</div>
<script type="text/javascript">
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }
</script>
<br>
<br>
<?php echo Yii::T('common', 'Remarks') ?>：
<p><?php echo Yii::T('common', 'Cross channel repeated loan is not included in statistics, cross product type repeated loan is included in Statistics') ?></p>
<p><?php echo Yii::T('common', 'Number of people who borrow repeatedly: the number of people who initiate the application for loan repeatedly in the number of people who repay on the day') ?></p>
<p><?php echo Yii::T('common', 'Application rate of repeated borrowing: number of repeated borrowing applicants / number of repayments on the same day') ?></p>
<p><?php echo Yii::T('common', 'Number of successful borrowers: the number of people who initiated the application for repeated borrowing and successfully lent in the number of repayments on that day') ?></p>
<p><?php echo Yii::T('common', 'Success rate of repeated borrowing: number of people who have succeeded in repeated borrowing / number of people who have applied for repeated borrowing') ?></p>
<p><?php echo Yii::T('common', 'Repeated borrowing application within 7 days: the number of people who initiated the application within 7 days after the repayment of the current day') ?></p>
<p><?php echo Yii::T('common', 'Successful repeated borrowing within 7 days: the number of people who initiated the application and successfully lent within 7 days after repayment (the lending time is not limited to 7 days)') ?></p>









