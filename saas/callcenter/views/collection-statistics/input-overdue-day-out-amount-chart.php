<?php

use yii\widgets\ActiveForm;
use callcenter\models\InputOverdayOut;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

$this->shownav('manage', 'menu_input_overdue_day_out_amount');
$this->showsubmenu(Yii::T('common', 'Overdue days out of the reminder rate (by amount)'), array(
    array('list', Url::toRoute('collection-statistics/input-overdue-day-out-amount'), 0),
    array('chart', Url::toRoute('collection-statistics/input-overdue-day-out-amount-chart'), 1),
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?= Yii::T('common', 'Default all');?>'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['collection-statistics/input-overdue-day-out-amount-chart']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Add collection') ?><?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($startDate); ?>"  name="start_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
to <input type="text" value="<?=Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'New and old types (order)') ?>：<?php echo Html::dropDownList('user_type', Html::encode(Yii::$app->getRequest()->get('user_type', 0)), InputOverdayOut::$user_type_map); ?>&nbsp;
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
packageName：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
    ArrayHelper::htmlEncode($packageNameList),['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="search" class="btn" id="month_total">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Start every hour, update every 15 minutes') ?>
<?php ActiveForm::end(); ?>
<br/>
<script src="<?= $this->baseUrl . "/js/echarts.js"; ?>"></script>
<div id="main_total" style="height:500px; width: 1600px;"></div>
<script type="text/javascript">
    // 路径配置
    require.config({
        paths: {
            echarts: '<?= $this->baseUrl . "/js"; ?>'
        }
    });
    // 使用
    require([
            'echarts',
            'echarts/theme/macarons',
            'echarts/chart/line',
            'echarts/chart/bar',
        ],
        function (ec, theme) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById('main_total'), theme);

            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: <?= json_encode($legendTotal); ?>,
                    selected: {}
                },
                toolbox: {
                    show: true,
                    feature: {
                        mark: {show: true},
                        dataView: {show: true, readOnly: false},
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                calculable: true,
                xAxis: [
                    {
                        name:'入催日期',
                        type: 'category',
                        boundaryGap: false,
                        data: <?= json_encode($xDate) ?>
                    }
                ],
                yAxis: [{
                    name:'百分比%(按金额)',
                    type: 'value'
                }],
                series: <?= json_encode($seriesTotal) ?>
            };
            for (var key in option.legend.data) {
                if (key == 0 || key==1 || key ==2 ) {
                    continue;
                }
                option.legend.selected[ option.legend.data[key] ] = false;
            }
            myChart.setOption(option);
        });
</script>
<br>
<p><?php echo Yii::T('common', 'The first overdue and overdue rate is the due date one day before the date of the reminder') ?></p>
<br>