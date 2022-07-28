<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\models\manual_credit\ManualCreditModule;
/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_type');
$this->showsubmenu(Yii::T('common', 'Audit'), array(
    array(Yii::T('common', 'Show type'), Url::toRoute('credit-audit/manual-type-list'), 1),
    array(Yii::T('common', 'Add type'), Url::toRoute('credit-audit/manual-type-add'), 0),
));
?>


<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'Head Code') ?>/<?php echo Yii::T('common', 'Head Name') ?></th>
        <th><?php echo Yii::T('common', 'Type Name') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'update time') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode($value['head_code']); ?>/<?= Html::encode($value['head_name']); ?></td>
            <td><?= Html::encode($value['type_name']); ?></td>
            <td><?= Html::encode(ManualCreditModule::$status_list[$value['status']]); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s',$value['created_at'])); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s',$value['updated_at'])); ?></td>
            <td><a href="<?php echo Url::toRoute(['credit-audit/manual-type-edit', 'id' => $value['id']]); ?>"><?php echo Yii::T('common', '') ?>编辑</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<script>

</script>
