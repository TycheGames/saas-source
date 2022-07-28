<?php

use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collection_statistics_order');
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $this->baseUrl ?>/css/daterangepicker.css" />

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>


<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $this->baseUrl ?>/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseUrl ?>/js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseUrl ?>/js/daterangepicker.js"></script>

<style type="text/css">
    .first .panel-body{
        text-align: center;
    }
    .select{
             color: rgb(0, 0, 0);
             cursor: pointer;
             vertical-align: middle;
             margin: 3px 0px;
             padding: 2px 5px;
             border-width: 1px;
             border-style: solid;
             border-image: initial;
             border-color: rgb(221, 221, 221) rgb(102, 102, 102) rgb(102, 102, 102) rgb(221, 221, 221);
             background: rgb(221, 221, 221);
         }
</style>
<div class="panel panel-default first">
    <div class="panel-heading">
        <?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= empty(Yii::$app->request->get('time')) ? date("Y-m-d", time()) : Yii::$app->request->get('time'); ?>" id="time" name="time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;
        <input type="button" id="select" value="<?php echo Yii::T('common', 'filter') ?>" class="select">
    </div>
    <div class="panel-body">
        <div style="float: left;"><span style="font-size: 18px"><?php echo Yii::T('common', 'Order overview') ?>:</span></div>
        <div class="row col-sm-4" style="margin-top: 50px">
            <h3><?php echo Yii::T('common', 'Collecting') ?></h3>

            <p><?php echo Yii::T('common', 'number of order') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['amount']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['amount']) : '--');?></span></p>
            <p>&nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Principal') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['principal']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['principal']/100,2) : '--');?></span></p>
            <p><?php echo Yii::T('common', 'Late fee') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['true_late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['true_late_fee']/100,2) : '--');?><span style="color: black;">/</span><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['late_fee']/100, 2) : '--');?></span></p>
            <p><span style="font-size: 0.5em;">(<?php echo Yii::T('common', 'update time') ?>：<?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['create_at'])?date('Y-m-d H:i:s', $yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]['create_at']):'--');?>)</span></p>
        </div>
        <div class="row col-sm-4" style="margin-top: 50px">
            <h3><?php echo Yii::T('common', 'Commitment to repayment') ?></h3>
            <p><?php echo Yii::T('common', 'number of order') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['amount']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['amount']) : '--');?></span></p>
            <p>&nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Principal') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['principal']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['principal']/100,2) : '--');?></span></p>
            <p><?php echo Yii::T('common', 'Late fee') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['true_late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['true_late_fee']/100,2) : '--');?><span style="color: black;">/</span><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['late_fee']/100, 2) : '--');?></span></p>
            <p><span style="font-size: 0.5em;">(<?php echo Yii::T('common', 'update time') ?>：<?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['create_at'])?date('Y-m-d H:i:s', $yesterday[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]['create_at']):'--');?>)</span></p>
        </div>
        <div class="row col-sm-4" style="margin-top: 50px">
            <h3><?php echo Yii::T('common', 'Collection success') ?></h3>
            <p><?php echo Yii::T('common', 'number of order') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['amount']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['amount']) : '--');?></span></p>
            <p>&nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Principal') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['principal']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['principal']/100,2) : '--');?></span></p>
            <p><?php echo Yii::T('common', 'Late fee') ?>：<span style="color:red"><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['true_late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['true_late_fee']/100,2) : '--');?><span style="color: black;">/</span><?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['late_fee']) ? number_format($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['late_fee']/100,2) : '--');?></span></p>
            <p><span style="font-size: 0.5em;">(<?php echo Yii::T('common', 'update time') ?>：<?php echo Html::encode(isset($yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['create_at'])?date('Y-m-d H:i:s', $yesterday[LoanCollectionOrder::STATUS_COLLECTION_FINISH]['create_at']):'--');?>)</span></p>
        </div>
    </div>
    <hr style="width: 95%;">
    <div class="panel-body">
        <div style="float: left;"><span style="font-size: 18px"><?php echo Yii::T('common', 'Order distribution') ?>:</span></div>
        <div class="btn-group" data-toggle="buttons" style="margin-bottom: 10px;margin-top: 50px">
            <label class="btn btn-default active" style="float: left">
                <input type="radio" name="options" value="amount" id="option1" autocomplete="off" checked ><?php echo Yii::T('common', 'Order distribution') ?>
            </label>
            <label class="btn btn-default" style="float: left">
                <input type="radio" name="options" value="money" id="option3" autocomplete="off" ><?php echo Yii::T('common', 'Principal distribution') ?>
            </label>
        </div>
        <script type="text/javascript">
            $(function(){

                $("input:radio[name='options']").change(function(){
                    // console.log($(this).val());
                    if($(this).val() == 'money'){
                        $("table[name=money]").css('display', 'block');
                        $("table[name=amount]").css('display', 'none');
                    }else{
                        $("table[name=money]").css('display', 'none');
                        $("table[name=amount]").css('display', 'block');
                    }
                });
            });
        </script>
        <table name="amount" style="text-align: center; vertical-align: middle;" class="table table-striped table-bordered">
            <thead style="background-color:  rgb(245, 245, 245);">
            <tr class="row">
                <td rowspan="2" class="col-sm-3"><span style="display: block;height: 44px;line-height: 44px;"><?php echo Yii::T('common', 'Collection Groups') ?></span></td>
                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Collecting') ?> </td>

                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Commitment to repayment') ?></td>

                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Collection success') ?></td>
            </tr>
            <tr class="row">
                <td class="col-sm-2"><?php echo Yii::T('common', 'number of order') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

                <td class="col-sm-2"><?php echo Yii::T('common', 'number of order') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

                <td class="col-sm-2"><?php echo Yii::T('common', 'number of order') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($groupTotalData as $group => $value):?>
                <tr>
                    <td colspan="2"><?=LoanCollectionOrder::$level[$group]; ?></td>
                    <?php foreach ($value as $status => $val): ?>
                        <?php if (is_array($val)): ?>
                            <td><?=Html::encode(number_format($val['amount'])); ?></td>
                            <td><?=Html::encode(empty($value['totalAmount']) ? 0 : round($val['amount']/$value['totalAmount'], 4) * 100);?>%</td>
                        <?php endif; ?>
                    <?php endforeach;?>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>

        <table name="money" style="text-align: center; vertical-align: middle;display: none;" class="table table-striped table-bordered">
            <thead style="background-color:  rgb(245, 245, 245);">
            <tr class="row">
                <td rowspan="2" class="col-sm-3"><span style="display: block;height: 44px;line-height: 44px;"><?php echo Yii::T('common', 'Collection Groups') ?></span></td>
                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Collecting') ?></td>
                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Commitment to repayment') ?></td>
                <td colspan="2" class="col-sm-3"><?php echo Yii::T('common', 'Collection success') ?></td>
            </tr>
            <tr class="row">
                <td class="col-sm-2"><?php echo Yii::T('common', 'Principal') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

                <td class="col-sm-2"><?php echo Yii::T('common', 'Principal') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

                <td class="col-sm-2"><?php echo Yii::T('common', 'Principal') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'proportion') ?></td>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($groupTotalData2 as $group => $value):?>
                <tr>
                    <td colspan="2"><?=LoanCollectionOrder::$level[$group]; ?></td>
                    <?php foreach ($value as $status => $val): ?>
                        <?php if (is_array($val)): ?>
                            <td><?php echo Html::encode($val['principal']? number_format($val['principal']/100, 2) : 0); ?></td>
                            <td><?=Html::encode(empty($value['totalPrincipal']) ? 0 : round($val['principal']/$value['totalPrincipal'], 4) * 100);?>%</td>
                        <?php endif; ?>
                    <?php endforeach;?>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading demo" >
        <input type="text" id="demo" class="form-control" style="width: 200px;">
<!--        <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>-->
    </div>
    <div class="panel-body">
        <div class="bs-callout bs-callout-info" style="font-size: 18px;margin-bottom: 20px"><?php echo Yii::T('common', 'Daily collection statistics') ?>:</div>
        <table class="table table-bordered table-striped" style="text-align: center;">
            <thead style="background-color: rgb(245, 245, 245);">
                <td class="col-sm-2"><?php echo Yii::T('common', 'date') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'Add the number of collection orders') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'Newly added to collect principal') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'New repayment orders') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'New repayment principal') ?></td>
                <td class="col-sm-2"><?php echo Yii::T('common', 'New late payment fee') ?></td>
            </thead>
            <tbody>
                <?php foreach ($daily as $key => $record):?>
                    
                    <tr><td><?=date('Y-m-d', $record['create_at'])?></td><td><?=Html::encode(number_format($record['new_amount']))?></td><td><?=Html::encode(number_format($record['new_principal']/100, 2))?></td><td><?=Html::encode(number_format($record['repay_amount']))?></td><td><?=Html::encode(number_format($record['repay_principal']/100, 2))?></td><td><?=Html::encode(number_format($record['repay_late_fee']/100, 2))?></td></tr>
                <?php endforeach;?>
            </tbody>
        </table>

   
        
    </div>
</div>

 <style type="text/css">
      .demo { position: relative; }
      .demo i {
        position: absolute; bottom: 20px; left: 200px; top: auto; cursor: pointer;
      }
</style>
<script type="text/javascript">
    $(function(){
        var options={};
         options.ranges = {
              '今天': [moment(), moment()],
              '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              '最近7天': [moment().subtract(6, 'days'), moment()],
              '最近30天': [moment().subtract(29, 'days'), moment()],
              '本月': [moment().startOf('month'), moment().endOf('month')],
              '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            };
             options.locale = {
              direction: $('#rtl').is(':checked') ? 'rtl' : 'ltr',
              format: 'YYYY-MM-DD',
              separator: ' - ',
              applyLabel: '确定',
              cancelLabel: '取消',
              fromLabel: '起始时间',
              toLabel: '结束时间',
              customRangeLabel: '自定义',
              daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
              monthNames: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
              firstDay: 1
            };

        $('#demo').daterangepicker(options, function(start, end, label) {
            var time = $('#time').val();
            var sub_order_type = $('select[name=sub_order_type]').val();
            var product_type = $('select[name=product_type]').val();
          console.log("New date range selected: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + " (predefined range: ' + label + ')");
          window.location = "<?=Url::toRoute('collection-statistics/order-statistics')?>"+"&start="+start+"&end="+end+"&sub_order_type="+sub_order_type+"&product_type="+product_type;
        });
    });
    $('#select').click(function(){
        var time = $('#time').val();
        var sub_order_type = $('select[name=sub_order_type]').val();
        var product_type = $('select[name=product_type]').val();
        window.location = "<?=Url::toRoute('collection-statistics/order-status-and-group')?>"+"&time="+time+"&sub_order_type="+sub_order_type+"&product_type="+product_type;
    })
</script>