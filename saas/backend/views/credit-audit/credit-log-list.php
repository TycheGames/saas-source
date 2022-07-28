<?php
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\manual_credit\ManualCreditLog;

$this->shownav('creditAudit', 'menu_credit_log_list');
$this->showsubmenu('credit log list', array(
));
?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['credit-audit/credit-log-list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
    <script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    Order ID：<input type="text" value="<?= Html::encode(yii::$app->request->get('order_id', '')); ?>" name="order_id" class="txt" />&nbsp;
    Operator：<input type="text" value="<?= Html::encode(yii::$app->request->get('operator', '')); ?>" name="operator" class="txt" />&nbsp;
    Is auto Operation：<?= Html::dropDownList('is_auto', Html::encode(yii::$app->request->get('is_auto', '')), ManualCreditLog::$is_auto_map,['prompt' => 'all']); ?>&nbsp;
    Credit type：<?= Html::dropDownList('action', Html::encode(yii::$app->request->get('action', '')), ManualCreditLog::$action_list,['prompt' => 'all']); ?>&nbsp;
    Credit result：<?= Html::dropDownList('type', Html::encode(yii::$app->request->get('type', '')), ManualCreditLog::$type_list,['prompt' => 'all']); ?>&nbsp;
    Credit time ：<input type="text" value="<?= Html::encode(yii::$app->request->get('begintime', '')); ?>" name="begintime" class="txt" onfocus="WdatePicker({lang:'en',startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
    to&nbsp;<input type="text" value="<?= Html::encode(yii::$app->request->get('endtime', '')); ?>" name="endtime" class="txt" onfocus="WdatePicker({lang:'en',startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />&nbsp;
    <input type="submit" name="search_submit" value="search" class="btn" />&nbsp;
<?php $form = ActiveForm::end(); ?>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>Log Id</th>
            <th>Order ID</th>
            <th>Credit type</th>
            <th>Credit result</th>
            <th>Credit time</th>
            <th>Operator</th>
            <th>Operation</th>
        </tr>

        <?php foreach ($data_list as $value): ?>
            <tr class="hover">
                <td><?= Html::encode($value['id']); ?></td>
                <th><?= Html::encode($value['order_id']); ?></th>
                <th><?= Html::encode(ManualCreditLog::$action_list[$value['action']] ?? '--'); ?></th>
                <th><?= Html::encode(ManualCreditLog::$type_list[$value['type']] ?? '--'); ?></th>
                <th><?= Html::encode(empty($value['created_at']) ? '--' : date('Y-m-d H:i:s',$value['created_at'])); ?></th>
                <th><?= Html::encode($value['username'] ?? '-'); ?></th>
                <th>
                    <a href="<?= Url::to(['credit-audit/credit-log-detail', 'id' => Html::encode($value['id'])]);?>">detail</a>
                </th>
            </tr>
        <?php endforeach; ?>
    </table>
<?php if (empty($data_list)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>