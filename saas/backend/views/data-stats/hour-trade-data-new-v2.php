<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\models\Merchant;

$this->showsubmenu(Yii::T('common', 'Comparison of daily borrowing and repayment data'), array(
    array(Yii::T('common', 'show'), Url::toRoute(['data-stats/daily-trade-data']),1),
));

?>
<style>
    table tr th {
        font-weight: bold;
    }
    .change_tag {
        float: right;
        margin-right: 10%;
    }
    .bz {
        text-align: left;
        font-size: 12px;
        margin-left: 10px;
        line-height: 1.5;
    }
    table th{text-align: center}
    table td{text-align: center}
</style>
<title><?php echo Yii::T('common', 'Comparison of daily borrowing and repayment data') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="css/data_table/jquery.dataTables.css">
<script src="js/data_table/jquery.dataTables.js" type="text/javascript"></script>
<script src="<?= $this->baseUrl . "/js/echarts.js"; ?>"></script>
<script language="JavaScript">
    $(function () {
        $('.market-select').SumoSelect({ placeholder:'<?=Yii::T('common', 'Default all channels');?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
        $('.package-name-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
    });
</script>
<?php
        $form = ActiveForm::begin([
            'id' => 'search_form',
            'method'=>'get',
            'action' => ['data-stats/daily-trade-data'],
            'options' => ['style' => 'margin-top:5px;'],
        ]);
?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($add_start) ?>" name="add_start"
            onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text" value="<?= Html::encode($add_end) ?>" name="add_end"
            onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
<script src="<?= $this->baseUrl . "/js/lodash.min.js"; ?>" type='text/javascript'></script>
<script src="<?= $this->baseUrl . "/js/echarts.js"; ?>"></script>

<?php echo Yii::T('common', 'Loan display') ?>：<?= Html::dropDownList('loan_type', Html::encode(Yii::$app->getRequest()->get('loan_type', '')),
    [
            '0'=> Yii::T('common', 'Total loan amount'),
            '1'=>Yii::T('common', 'Total number of applicants'),
            '2'=>Yii::T('common', 'Total applied amount'),
            '3'=>Yii::T('common', 'Total Lenders'),
            '4'=>Yii::T('common', 'Total pass rate'),
            '5'=>Yii::T('common', 'Number of old applicants'),
            '6'=>Yii::T('common', 'Old application amount'),
            '7'=>Yii::T('common', 'Number of old Lenders'),
            '8'=>Yii::T('common', 'Old loan amount'),
            '9'=>Yii::T('common', 'Old passing rate'),
            '10'=>Yii::T('common', 'Number of new applicants'),
            '11'=>Yii::T('common', 'New application amount'),
            '12'=>Yii::T('common', 'Number of new lenders'),
            '13'=>Yii::T('common', 'New loan amount'),
            '14'=>Yii::T('common', 'New passing rate'),
            '15' => Yii::T('common', 'Number of orders transferred to labor'),
            '18'=>Yii::T('common', 'Total loan amount applied for on the same day'),
            '19'=>Yii::T('common', 'Number of new risk control passing'),
            '20'=>Yii::T('common', 'Number of old risk control passing'),
            '21'=>Yii::T('common', 'Total number of people passing risk control'),
            '22'=>Yii::T('common', 'Number of manual audit orders'),
            '23'=>Yii::T('common', 'Registration number'),
            '24'=>'申请人数-全新本新',
            '25'=>'申请金额-全新本新',
            '26'=>'放款人数-全新本新',
            '27'=>'放款金额-全新本新',
            '28'=>'通过率-全新本新',
            '29'=>'风控通过人数-全新本新',

            '30'=>'申请人数-全老本新',
            '31'=>'申请金额-全老本新',
            '32'=>'放款人数-全老本新',
            '33'=>'放款金额-全老本新',
            '34'=>'通过率-全老本新',
            '35'=>'风控通过人数-全老本新',
    ]); ?>&nbsp;
<?php echo Yii::T('common', 'Repayment display') ?>：<?= Html::dropDownList('repay_type', Html::encode(Yii::$app->getRequest()->get('repay_type', '')),
    [
            '0'=>Yii::T('common', 'Total repayment rate'),
            '1'=>Yii::T('common', 'Total number of repayments'),
            '2'=>Yii::T('common', 'Total repayment amount'),
            '3'=>Yii::T('common', 'Number of old repayments'),
            '4'=>Yii::T('common', 'Old repayment amount'),
            '5'=>Yii::T('common', 'Old repayment rate'),
            '6'=>Yii::T('common', 'Number of new repayments'),
            '7'=>Yii::T('common', 'New repayment amount'),
            '8'=>Yii::T('common', 'New repayment rate'),
            '9'=>Yii::T('common', 'Total number of active repayments'),
            '10'=>Yii::T('common', 'Number of new active repayments'),
            '11'=>Yii::T('common', 'Number of old active repayments'),

            '12'=>'还款人数-全新本新',
            '13'=>'还款金额-全新本新',
            '14'=>'还款率-全新本新',
            '15'=>'主动还款人数-全新本新',

            '16'=>'还款人数-全老本新',
            '17'=>'还款金额-全老本新',
            '18'=>'还款率-全老本新',
            '19'=>'主动还款人数-全老本新',
    ]); ?>&nbsp;
<?php echo Yii::T('common', 'Statistical type') ?>：<?= Html::dropDownList('data_type', Html::encode(Yii::$app->getRequest()->get('data_type', '')), ['0'=>Yii::T('common', 'summary'),'1'=>Yii::T('common', 'Time sharing')]);?>&nbsp;
<?php echo Yii::T('common', 'Compare the way') ?>：<?= Html::dropDownList('contrast_type', Html::encode(Yii::$app->getRequest()->get('contrast_type', '0')), ['0'=>Yii::T('common', 'date'),'1'=>'appMarket']);?>&nbsp;
<?php echo Yii::T('common', 'channel') ?>：<?php  echo Html::dropDownList('app_market', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('app_market', [])),  $searchList,['class' => 'form-control market-select', 'multiple' => 'multiple', 'id' => 'app_market']); ?>&nbsp;
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
        Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<?php echo Yii::T('common', 'Order package name') ?>：<?php echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])), $packageNameList,['class' => 'form-control package-name-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">&nbsp;<?php echo Yii::T('common', 'updated time') ?>：<?= $update_time;?> (<?php echo Yii::T('common', 'updated every 15 minutes') ?>)
<br>
<?php echo Yii::T('common', 'The loan shows a line chart') ?>：
<div id="main_loan" style="height:500px; width: 1200px;"></div>
<script type="text/javascript">
//路径配置
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
    var myChart = ec.init(document.getElementById('main_loan'), theme);

    var option = {

        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?= json_encode($legend_loan); ?>,
            selected: {},
            padding:[30,100]
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
                type: 'category',
                boundaryGap: false,
                data: <?= json_encode($x) ?>
            }
        ],
        yAxis: [{
            type: 'value',
        }],
        series: <?= json_encode($series_loan) ?>
    };

    //除了0, 隐藏其他
    for (var key in option.legend.data) {
        if (key == 0||key == 1) {
            continue;
        }

        option.legend.selected[ option.legend.data[key] ] = false;
    }

    // 为echarts对象加载数据
    myChart.setOption(option);
});
</script>
<br>

    <?php echo Yii::T('common', 'Repayment shows a line chart') ?>（<?php echo Yii::T('common', '0-1 is prepayment') ?>）：
<div id="main_repay" style="height:500px; width: 1200px;"></div>
<script type="text/javascript">
//路径配置
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
    var myChart = ec.init(document.getElementById('main_repay'), theme);

    var option = {

        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?= json_encode($legend_repay); ?>,
            selected: {},
            padding:[30,100]
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
                type: 'category',
                boundaryGap: false,
                data: <?= json_encode($xs) ?>
            }
        ],
        yAxis: [{
            type: 'value',
        }],
        series: <?= json_encode($series_repay) ?>
    };

    //除了0, 隐藏其他
    for (var key in option.legend.data) {
        if (key == 0||key == 1) {
            continue;
        }

        option.legend.selected[ option.legend.data[key] ] = false;
    }

    // 为echarts对象加载数据
    myChart.setOption(option);
});
</script>
<br>

<br>

<?php echo Yii::T('common', 'Discount chart of prepayment due next day') ?>（<?php echo Yii::T('common', '0-1 is prepayment') ?>）：
<div id="main_repay_tomorrow" style="height:500px; width: 1200px;"></div>
<script type="text/javascript">
    //路径配置
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
            var myChart = ec.init(document.getElementById('main_repay_tomorrow'), theme);

            var option = {

                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: <?= json_encode($legend_repay); ?>,
                    selected: {},
                    padding:[30,100]
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
                        type: 'category',
                        boundaryGap: false,
                        data: <?= json_encode($xs) ?>
                    }
                ],
                yAxis: [{
                    type: 'value',
                }],
                series: <?= json_encode($series_repay_tomorrow) ?>
            };

            //除了0, 隐藏其他
            for (var key in option.legend.data) {
                if (key == 0||key == 1) {
                    continue;
                }

                option.legend.selected[ option.legend.data[key] ] = false;
            }

            // 为echarts对象加载数据
            myChart.setOption(option);
        });
</script>
<br>

<?php if(!empty($trade_data)): ?>
<br>
<!--<DIV  style=" OVERFLOW-X: scroll;">-->
    <form name="listform" method="post">
        <table class="tb tb2 fixpadding" style="width: 1700px;" >
            <tr class="header">
                <th colspan="2" style="text-align:center;border-right:1px solid #A9A9A9;"></th>
                <th colspan="15" style="text-align: center;border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'loan') ?></th>
                <th colspan="12" style="text-align: center;border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'repayment') ?></th>
            </tr>
            <tr class="header">
                <th></th>
                <th style="border-right:1px solid #A9A9A9"></th>
                <th colspan="5" style="text-align:center;border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'All users') ?></th>
                <th colspan="5" style="text-align:center;border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'Old users') ?></th>
                <th colspan="5" style="text-align:center;border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'New users') ?></th>
                <th colspan="4" style="text-align:center;border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'All users') ?></th>
                <th colspan="4" style="text-align:center;border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Old users') ?></th>
                <th colspan="4" style="text-align:center;border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'New users') ?></th>
            </tr>
            <tr class="header">
                <th><?php echo Yii::T('common', 'date') ?></th>
                <th style="border-right:1px solid #A9A9A9"><?php echo Yii::T('common', 'hour') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of applicants') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Application amount') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of loans') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Loan amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'Passing rate') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of applicants') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Application amount') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of loans') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Loan amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'Passing rate') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of applicants') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Application amount') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Number of loans') ?></th>
                <th style="color: blue"><?php echo Yii::T('common', 'Loan amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: blue"><?php echo Yii::T('common', 'Passing rate') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment number') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Number of active repayment') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Repayment rate') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment number') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Number of active repayment') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Repayment rate') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment number') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Number of active repayment') ?></th>
                <th style="color: red"><?php echo Yii::T('common', 'Repayment amount') ?></th>
                <th style="border-right:1px solid #A9A9A9;color: red"><?php echo Yii::T('common', 'Repayment rate') ?></th>
            </tr>

            <?php foreach ($trade_data as $date =>$item): ?>
                <?php foreach ($item as $hour =>$value): ?>
                    <tr class="hover">
                        <td><?= $date; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= $hour.":00"; ?></td>
                        <td><?= isset($value['apply_num_0'])?$value['apply_num_0']:0; ?></td>
                        <td><?= isset($value['apply_money_0'])?$value['apply_money_0']/100:0; ?></td>
                        <td><?= isset($value['loan_num_0'])?$value['loan_num_0']:0; ?></td>
                        <td><?= isset($value['loan_money_0'])?$value['loan_money_0']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['pass_rate_0'])?$value['pass_rate_0']*100:0; ?>%</td>
                        <td><?= isset($value['apply_num_2'])?$value['apply_num_2']:0; ?></td>
                        <td><?= isset($value['apply_money_2'])?$value['apply_money_2']/100:0; ?></td>
                        <td><?= isset($value['loan_num_2'])?$value['loan_num_2']:0; ?></td>
                        <td><?= isset($value['loan_money_2'])?$value['loan_money_2']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['pass_rate_2'])?$value['pass_rate_2']*100:0; ?>%</td>
                        <td><?= isset($value['apply_num_1'])?$value['apply_num_1']:0; ?></td>
                        <td><?= isset($value['apply_money_1'])?$value['apply_money_1']/100:0; ?></td>
                        <td><?= isset($value['loan_num_1'])?$value['loan_num_1']:0; ?></td>
                        <td><?= isset($value['loan_money_1'])?$value['loan_money_1']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['pass_rate_1'])?$value['pass_rate_1']*100:0; ?>%</td>
                        <td><?= isset($value['repayment_num_0'])?$value['repayment_num_0']:0; ?></td>
                        <td><?= isset($value['repayment_num_0'])?$value['active_repayment_0']:0; ?></td>
                        <td><?= isset($value['repayment_money_0'])?$value['repayment_money_0']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['repay_rate_0'])?$value['repay_rate_0']*100:0; ?>%</td>
                        <td><?= isset($value['repayment_num_2'])?$value['repayment_num_2']:0; ?></td>
                        <td><?= isset($value['repayment_num_2'])?$value['active_repayment_2']:0; ?></td>
                        <td><?= isset($value['repayment_money_2'])?$value['repayment_money_2']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['repay_rate_2'])?$value['repay_rate_2']*100:0; ?>%</td>
                        <td><?= isset($value['repayment_num_1'])?$value['repayment_num_1']:0; ?></td>
                        <td><?= isset($value['repayment_num_1'])?$value['active_repayment_1']:0; ?></td>
                        <td><?= isset($value['repayment_money_1'])?$value['repayment_money_1']/100:0; ?></td>
                        <td style="border-right:1px solid #A9A9A9"><?= isset($value['repay_rate_1'])?$value['repay_rate_1']*100:0; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
        <?php if (empty($trade_data)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
        <?= LinkPager::widget(['pagination' => $pages]); ?>
    </form>
<?php endif; ?>
<?php ActiveForm::end(); ?>

<br>
<p><?php echo Yii::T('common', 'The line chart shows the data within 24 hours by default and the prepayment is uniformly set to zero') ?></p>
<p><?php echo Yii::T('common', 'Number of applicants: record the number of applicants from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Number of lenders: record the number of lenders from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Application amount: record the application amount from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Loan amount: record the loan amount from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Pass rate: number of lenders / number of applicants') ?></p>
<p><?php echo Yii::T('common', 'Number of repayments: record the number of repayments from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Repayment amount: record the repayment amount from 0:00 to the current time every hour') ?></p>
<p><?php echo Yii::T('common', 'Repayment rate: paid amount / payable amount') ?></p>
<p><?php echo Yii::T('common', 'The statistics lending amount is the successful payment amount on the day when the bank returns') ?></p>
<br>



