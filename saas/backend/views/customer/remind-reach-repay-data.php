<?php

use backend\components\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\stats\RemindReachRepay;
/**
 * @var backend\components\View $this
 */
?>


<title><?php echo Yii::T('common', 'Reminder to reach repayment statistics') ?></title>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['customer/remind-reach-repay-data']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'date') ?>：<input type="text" value="<?= Html::encode($add_start); ?>"  name="add_start" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：  <input type="text" value="<?= Html::encode($add_end); ?>"  name="add_end" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'New users') ?>：<?=Html::dropDownList('user_type',Html::encode(Yii::$app->getRequest()->get('user_type', 0)),RemindReachRepay::$user_type_map);?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">&nbsp;<?php echo Yii::T('common', 'updated every 15 minutes') ?>
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding" id="myTable">
        <thead>
        <tr class="header">
            <th><?php echo Yii::T('common', 'date') ?></th>
            <th><?php echo Yii::T('common', 'Reminder order number') ?></th>
            <th><?php echo Yii::T('common', 'Orders reached') ?></th>
            <th><?php echo Yii::T('common', 'Reach rate') ?></th>
            <th><?php echo Yii::T('common', 'Repayment orders') ?></th>
            <th><?php echo Yii::T('common', 'Reminder Repayment Rate') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        </thead>
        <?php foreach ($totalData as $value): ?>
            <tr>
                <td style='color:blue;font-weight:bold'><?php echo Yii::T('common', 'summary') ?></td>
                <td style='color:blue;font-weight:bold'><?php echo Html::encode($value['remind_num'] ?? '-'); ?></td>
                <td style='color:blue;font-weight:bold'><?php echo Html::encode($value['reach_num']); ?></td>
                <td style='color:blue;font-weight:bold'><?php echo Html::encode($value['remind_num'] == 0 ? '--' : sprintf("%.2f", $value['reach_num']/$value['remind_num']*100).'%'); ?></td>
                <td style='color:blue;font-weight:bold'><?php echo Html::encode($value['repay_num']); ?></td>
                <td style='color:blue;font-weight:bold'><?php echo Html::encode($value['remind_num'] == 0 ? '--' : sprintf("%.2f", $value['repay_num']/$value['remind_num']*100).'%'); ?></td>
                <td style='color:blue;font-weight:bold'>--</td>
            </tr>
        <?php endforeach; ?>
        </thead>
        <tbody>
        <?php foreach ($data as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['date'] ?? '-' ); ?></td>
                <td><?php echo Html::encode($value['remind_num']); ?></td>
                <td><?php echo Html::encode($value['reach_num']); ?></td>
                <td><?php echo Html::encode($value['remind_num'] == 0 ? '--' : sprintf("%.2f", $value['reach_num']/$value['remind_num']*100).'%'); ?></td>
                <td><?php echo Html::encode($value['repay_num']); ?></td>
                <td><?php echo Html::encode($value['remind_num'] == 0 ? '--' : sprintf("%.2f", $value['repay_num']/$value['remind_num']*100).'%'); ?></td>
                <td><?php echo Html::encode(isset($value['updated_at']) ? date('Y-m-d H:i',$value['updated_at']) : '-')?></td>
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