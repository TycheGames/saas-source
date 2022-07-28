<?php

use backend\components\widgets\LinkPager;
use common\models\user\UserRegisterInfo;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
$this->showsubmenu('每日注册转化 Daily Register Conversion');
/**
 * @var backend\components\View $this
 */
?>


<title>每日注册转化（大盘+导流）</title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:'默认全部appMarket'});
        $('.media-source-select').SumoSelect({ placeholder:'默认全部mediaSource'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats-full-platform/daily-register-conver']), 'options' => ['style' => 'margin-top:5px;']]); ?>
日期(date)：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('add_start')) ? date("Y-m-d", time() - 7 * 86400) : Yii::$app->request->get('add_start')); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
至(to)：  <input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('add_end')) ? date("Y-m-d") : Yii::$app->request->get('add_end')); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
appMarket：<?php  echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),
    UserRegisterInfo::getChannelSearchList(),['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
mediaSource：<?php  echo Html::dropDownList('media_source', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('media_source', [])),
    UserRegisterInfo::getMediaSourceSearchList(),['class' => 'form-control media-source-select', 'multiple' => 'multiple', 'id' => 'media_source']); ?>&nbsp;
sourceApp：<?= Html::dropDownList('source_id', Html::encode(Yii::$app->getRequest()->get('source_id', '')), array_flip($sourceMap),['prompt' => '--all--']);?>&nbsp;
数据类型(Data type)：<?= Html::dropDownList('type', Html::encode(Yii::$app->getRequest()->get('type', '0')), [0=>'累计(Accumulative)', 1=>'当日(Today)', 2=>'3日内(Three days)', 3=>'7日内(Seven days)', 4=>'10日内', 5=>'30日内(Thirty days)']);?>&nbsp;
<input type="submit" name="search_submit" value="过滤" class="btn">
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('export_direct');return true;" class="btn" />
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th>日期Date</th>
            <th>sourceApp</th>
            <th>app_market</th>
            <th>media_source</th>
            <th>注册数</br>Register No</th>
            <th>基础认证</br>Basic info</th>
            <th>KYC认证</br>KYC documents</th>
            <th>地址证明</br>Address proof</th>
            <th>紧急联系人</br>Contact info</th>
            <th style="color: red">注册到认证</br>Contact info to Register No</th>
            <th>申请</br>Apply</th>
            <th style="color: red">认证到申请</br>Apply to Verify</th>
            <th style="color: red">注册到申请</br>Apply to Register</th>
            <th>过件</br>Pass by Risk Control</th>
            <th style="color: red">过件率</br>Risk Control Approval to Apply</th>
            <th>提现</br>Withdraw</th>
            <th>放款</br>Disburse</th>
            <th style="color: red">注册到放款</br>Disburse to Register</th>
        </tr>
        </thead>
        <thead class="total">
        <?php foreach ($totalData as $value): ?>
        <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo '-'; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo '-'; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo '-'; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['reg_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['basic_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['kyc_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['address_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['contact_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['contact_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['apply_num']); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['contact_num']) ? sprintf("%0.2f",$value['apply_num']/$value['contact_num']*100) .'%': '-') ; ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['apply_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['audit_pass_num']);?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['apply_num']) ? sprintf("%0.2f",$value['audit_pass_num']/$value['apply_num']*100) .'%': '-'); ?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['withdraw_num']);?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:blue;'";?>><?php echo Html::encode($value['loan_num']);?></td>
            <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['loan_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
        </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
                <td><?php echo Html::encode(array_flip($sourceMap)[$value['source_id']] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['app_market'] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['media_source'] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['reg_num']); ?></td>
                <td><?php echo Html::encode($value['basic_num']); ?></td>
                <td><?php echo Html::encode($value['kyc_num']); ?></td>
                <td><?php echo Html::encode($value['address_num']); ?></td>
                <td><?php echo Html::encode($value['contact_num']); ?></td>
                <td style="color: red"><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['contact_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
                <td><?php echo Html::encode($value['apply_num']); ?></td>
                <td style="color: red"><?php echo Html::encode(!empty($value['contact_num']) ? sprintf("%0.2f",$value['apply_num']/$value['contact_num']*100) .'%': '-' ); ?></td>
                <td style="color: red"><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['apply_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
                <td><?php echo Html::encode($value['audit_pass_num']);?></td>
                <td style="color: red"><?php echo Html::encode(!empty($value['apply_num']) ? sprintf("%0.2f",$value['audit_pass_num']/$value['apply_num']*100) .'%': '-'); ?></td>
                <td><?php echo Html::encode($value['withdraw_num']);?></td>
                <td><?php echo Html::encode($value['loan_num']);?></td>
                <td style="color: red"><?php echo Html::encode(!empty($value['reg_num']) ? sprintf("%0.2f",$value['loan_num']/$value['reg_num']*100) .'%': '-' ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">暂无记录</div>
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