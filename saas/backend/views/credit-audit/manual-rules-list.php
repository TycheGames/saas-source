<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\models\manual_credit\ManualCreditRules;
/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_rules_list');
$this->showsubmenu(Yii::T('common', 'Rules List'), array(
    array(Yii::T('common', 'Show rules'), Url::toRoute('credit-audit/manual-rules-list'), 1),
    array(Yii::T('common', 'Add rules'), Url::toRoute('credit-audit/manual-rules-add'), 0),
));
?>


<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'Back Code') ?>/<?php echo Yii::T('common', 'Rule Name') ?></th>
        <th><?php echo Yii::T('common', 'Type ID') ?>/<?php echo Yii::T('common', 'Type Name') ?></th>
        <th><?php echo Yii::T('common', 'Module ID') ?>/<?php echo Yii::T('common', 'Head Code') ?></th>
        <th><?php echo Yii::T('common', 'Rule type') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td width="400px"><?= Html::encode($value['back_code']); ?>/<?= Html::encode($value['rule_name']); ?></td>
            <th><?= Html::encode($value['type_id']); ?>/<?= Html::encode($value['type_name']); ?></th>
            <td><?= Html::encode($value['module_id']); ?>/<?= Html::encode($value['head_code']); ?></td>
            <td><?= Html::encode(ManualCreditRules::$type_list[$value['type']]); ?></td>
            <td><?= Html::encode(ManualCreditRules::$status_list[$value['status']]); ?></td>
            <td><a href="<?php echo Url::toRoute(['credit-audit/manual-rules-edit', 'id' => $value['id']]); ?>"><?php echo Yii::T('common', 'edit') ?></a></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>
