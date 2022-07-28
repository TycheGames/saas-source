<?php

use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collection_foresee_input');
$this->showsubmenu('', array(
    array('单期预估', Url::toRoute(['collection/foresee-input','stage_type'=>1,'sub_from'=>1]),1),
));

?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>


<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $this->baseUrl ?>/bootstrap/js/bootstrap.min.js"></script>

<style type="text/css">
    .first .panel-body{
        text-align: center;
    }
</style>

<div class="jumbotron">
  <h3>明日派单预估：</h3>
  <p>S1组共<span style="background-color: green;color: white;"><?=$s1_active?></span>人，人均<span style="background-color: green;color: white;"><?= isset($lists[0]['avg'])?$lists[0]['avg']:'--' ?></span>单</p>
  <p>S2组共<span style="background-color: green;color: white;"><?=$s2_active?></span>人，人均<span style="background-color: green;color: white;"><?= isset($s2_lists[0]['avg'])?$s2_lists[0]['avg']:'--'?></span>单</p>
  <!-- <p><a class="btn btn-primary btn-lg" href="#" role="button">Learn more</a></p> -->
</div>


<div class="panel panel-default">
    <div class="panel-heading demo" >
        <!-- 更新时间：<input type="date" id="date_change" value="<?= date('Y-m-d', $start);?>" class="form-control" style="width: 200px;"> -->
        <!-- <i class="glyphicon glyphicon-calendar fa fa-calendar"></i> -->
        未来几天入催情况:（近7天本金平均入催率：<span style="background-color: green;color: white;"><?=$rate_principal * 100?>%</span>, S1组可派单人数：<span style="background-color: green;color: white;"><?=Html::encode($s1_active)?></span>）
    </div>
    <div class="panel-body">
        <div class="bs-callout bs-callout-info"></div>
        <table class="table table-bordered table-striped" style="text-align: center;">
            <thead style="background-color: rgb(245, 245, 245);">
                <td class="col-sm-1">到期时间</td>
                <td class="col-sm-2">到期订单数</td>
                <td class="col-sm-2">到期订单本金</td>
                <td class="col-sm-1">入催时间</td>
                <td class="col-sm-2">预估入催订单数</td>
                <td class="col-sm-2">预估入催本金</td>
                <td class="col-sm-2">预估人均单量</td>
            </thead>
            <tbody>
                <?php foreach ($lists as $key => $record):?>
                    
                    <tr>
                        <?php $day = date('d', $record['deadline_time']);?>
                        <td><?=Html::encode(date('Y-m-d', $record['deadline_time']))?></td>
                        <td><?=Html::encode(number_format($record['deadline_amount']))?></td>
                        <td><?=Html::encode(number_format($record['deadline_principal']/100))?></td>

                        <td><?=Html::encode(date('Y-m-d', $record['deadline_time']+86400))?></td>
                        <td><?=Html::encode(number_format($record['pre_amount'], 2))?></td>
                        <td><?=Html::encode(number_format($record['deadline_principal']/100 * $rate_principal, 2))?></td>
                        <td><?=Html::encode($record['avg'])?></td>
                        

                    </tr>
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
        

        $('#date_change').change(function(){
            // console.log($(this).val());
            var day = $(this).val();
            window.location = "<?=Url::toRoute('collection/foresee-input')?>"+"&start="+day+"&end="+day;
        });
    });
</script>

<?php if(1==1):?>
<div class="panel panel-default">
    <div class="panel-heading demo" >
       <!--  更新时间：<input type="date" id="date_change" value="<?/*= date('Y-m-d', $start);*/?>" class="form-control" style="width: 200px;">
         <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>-->
        未来几天流转S2情况:（近7天S2平均流转率：<span style="background-color: green;color: white;"><?=$s2_rate_amount * 100?>%</span>, S2组可派单人数：<span style="background-color: green;color: white;"><?=Html::encode($s2_active)?></span>）

    </div>
    <div class="panel-body">
        <div class="bs-callout bs-callout-info"></div>
        <table class="table table-bordered table-striped" style="text-align: center;">
            <thead style="background-color: rgb(245, 245, 245);">
                <td class="col-sm-1">流转S2时间</td>
                <td class="col-sm-1">入催时间</td>
                <td class="col-sm-2">入催订单数</td>
              <!--  <td class="col-sm-2">催收中订单数</td>-->
                <td class="col-sm-2">预估S2订单数</td>
                <td class="col-sm-2">预估人均单量</td>
            </thead>
            <tbody>
                <?php foreach ($s2_lists as $key => $item):?>
                <tr>
                    <td><?php echo Html::encode(date("Y-m-d", $item['s2_time']));?></td>
                    <td><?php echo Html::encode(date("Y-m-d", $item['rucui_time']));?></td>
                    <td><?= Html::encode($item['rucui_amount'])?></td>
                <!--    <td><?/*= $item['cuishouzhong_amount']*/?></td>-->
                    <td><?= Html::encode($item['pre_amount']) ?></td>
                    <td><?= Html::encode($item['avg'])?></td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>

   
        
    </div>
</div>
<?php endif;?>
