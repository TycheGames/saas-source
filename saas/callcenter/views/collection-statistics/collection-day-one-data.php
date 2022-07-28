<?php

use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\CollectorCallData;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collector_day_one_data');
$this->showsubmenu('催收员D1数据', array());
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/collector-day-one-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
催收机构：<?=Html::dropDownList('outside',Html::encode(Yii::$app->getRequest()->get('outside')),UserCompany::allOutsideRealName($merchant_id),array('prompt' => '-所有机构-','onchange' => 'onOutsideChange($(this).val())'));?>&nbsp;
小组分组：<span id="team">
    <?php  echo \yii\helpers\Html::dropDownList('group_game', \common\helpers\CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('group_game', [])),
        $teamList,['class' => 'form-control team-select', 'multiple' => 'multiple']); ?>&nbsp;
</span>
催收员分组：<?=Html::dropDownList('group',Html::encode(Yii::$app->getRequest()->get('group', 0)),LoanCollectionOrder::$level,array('prompt' => '-所有分组-'));?>&nbsp;
催收员：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
真实名：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('real_name', '')); ?>" name="real_name" class="txt" style="width:120px;">&nbsp;
<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="导出csv" onclick="$(this).val('exportcsv');return true;" class="btn">
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th >催收员</th>
            <th >真实名</th>
            <th >机构</th>
            <th >订单组</th>
            <th >小组分组</th>
            <th >D1派单数</th>
            <th >D1还款单数</th>
            <th >D1还款率</th>

        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode($value['real_name']); ?></td>
                <td ><?php echo Html::encode($value['outside_name']); ?></td>
                <td ><?php echo Html::encode(isset(LoanCollectionOrder::$level[$value['group']]) ?? '--'); ?></td>
                <td ><?php echo Html::encode($teamList[$value['group_game']] ?? '-'); ?></td>
                <td ><?php echo Html::encode($value['total_num']); ?></td>
                <td ><?php echo Html::encode($value['complete_num']); ?></td>
                <td ><?php echo Html::encode(sprintf("%01.2f", $value['complete_num']/$value['total_num'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
</form>
<script>
    function onOutsideChange(outside){
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                $.each(res,function(i,val){
                    $(".team-select option").eq(i-1).html(val);
                    $(".sumo_group_game .options label").eq(i-1).html(val);
                });
            }
        });
    }
    function showDateData() {
        if ($(".date-data").is(":hidden")){
            $(".date-data").show();

        }else {
            $(".date-data").hide();
        }
    }
</script>
