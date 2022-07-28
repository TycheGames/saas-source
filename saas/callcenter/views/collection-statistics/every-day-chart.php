<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/3/7
 * Time: 11:10
 */

use callcenter\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use common\models\User;
use common\models\loan\LoanCollection;
use common\models\loan\LoanCollectionOrder;
use yii\helpers\Html;
use yii\helpers\Url;

$this->shownav('manage', 'menu_collection_every_day_chart');
$this->showsubmenu(Yii::T('common', 'Daily recall rate'), array(
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form1','method'=>'get', 'action' => Url::toRoute(['every-day-chart']),'options' => ['style' => 'margin-top:5px;']]); ?>
<span class="s_item"><?php echo Yii::T('common', 'Add collection') ?><?php echo Yii::T('common', 'date') ?>：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time',date('Y-m-d',strtotime('-10 day')))); ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:100px;">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time',date('Y-m-d',time()))); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:100px;">
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
<input type="submit" name="search_submit" value=<?php echo Yii::T('common', 'filter') ?> class="btn" id="month_total">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

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
                    data: <?= json_encode($legend_total); ?>,
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
                        type: 'category',
                        boundaryGap: false,
                        data: <?= json_encode($days_total) ?>
                    }
                ],
                yAxis: [{
                    type: 'value'
                }],
                series: <?= json_encode($series_total) ?>
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
<!--<style type="text/css">-->
<!--    .panel:{-->
<!--        width: 1000px;-->
<!--        height: 500px;-->
<!--    }-->
<!--</style>-->
<?php if(!empty($total)):?>
<div class="panel panel-body" style="padding-top: 0;width: 1000px;
        height: 500px;">
    <div class="panel panel-body" style="font-size: 18px;margin-bottom: 20px"><?php echo Yii::T('common', 'Total recall rate') ?>:</div>
    <table class="tb tb2 fixpadding" id="info_table">
        <tr class="header">
            <th class="hidden-xs hidden-sm"><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'Receiving amount') ?></th>
            <th><?php echo Yii::T('common', 'Recall amount') ?></th>
            <th><?php echo Yii::T('common', 'Recall rate') ?></th>
        </tr>
        <?php foreach ($total as $k=> $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['time']);?></td>
                <td><?php echo Html::encode($value['all_money'].'元');?></td>
                <td><?php echo Html::encode($value['finish_money'].'元');?></td>
                <td><?php echo Html::encode($value['rate'] . '%'); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif;?>