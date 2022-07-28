<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Remind daily data') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['customer/remind-day-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($add_start); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($add_end); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'Reminder Group') ?>：<?=Html::dropDownList('remind_group',Html::encode(Yii::$app->getRequest()->get('remind_group', '')),$group,array('prompt' => '-all-'));?>&nbsp;
<?php echo Yii::T('common', 'Reminder') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('remind_name', '')); ?>" name="remind_name" class="txt" style="width:120px;">&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">&nbsp;<?php echo Yii::T('common', 'updated every 15 minutes') ?>
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'Reminder name') ?></th>
            <th><?php echo Yii::T('common', 'Reminder Group') ?></th>
            <th><?php echo Yii::T('common', 'Dispatch count') ?></th>
            <th><?php echo Yii::T('common', 'Repayment orders') ?></th>
            <th><?php echo Yii::T('common', 'Repayment rate') ?></th>
            <th><?php echo Yii::T('common', 'Reminder order number') ?></th>
            <th><?php echo Yii::T('common', 'Remind rate') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <?php foreach ($totalData as $value): ?>
            <tr <?= ($value['Type'] == 2) ?'class="hover date-data" hidden' : 'class="hover" onclick= "showDateData()"';?>>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['date']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['admin_user_id'] ?? '--'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['remind_group'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['today_dispatch_num'] ?? '-'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['today_repay_num']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['today_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['today_repay_num']/$value['today_dispatch_num']*100).'%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['today_dispatch_remind_num']); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>><?php echo Html::encode($value['today_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['today_dispatch_remind_num']/$value['today_dispatch_num']*100).'%'); ?></td>
                <td <?= ($value['Type'] == 1) ?"style='color:blue;font-weight:bold'" : "style='color:red;'";?>>--</td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
                <td><?php echo Html::encode($adminNames[$value['admin_user_id']] ?? '-'); ?></td>
                <td><?php echo Html::encode($group[$value['remind_group']] ?? '-'); ?></td>
                <td><?php echo Html::encode($value['today_dispatch_num']); ?></td>
                <td><?php echo Html::encode($value['today_repay_num']); ?></td>
                <td><?php echo Html::encode($value['today_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['today_repay_num']/$value['today_dispatch_num']*100).'%'); ?></td>
                <td><?php echo Html::encode($value['today_dispatch_remind_num']); ?></td>
                <td><?php echo Html::encode($value['today_dispatch_num'] == 0 ? '--' : sprintf("%.2f", $value['today_dispatch_remind_num']/$value['today_dispatch_num']*100).'%'); ?></td>
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