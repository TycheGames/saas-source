<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\models\manual_credit\ManualCreditModule;
/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_module');
$this->showsubmenu(Yii::T('common', 'Audit'), array(
    array(Yii::T('common', 'Show module'), Url::toRoute('credit-audit/manual-module-list'), 1),
    array(Yii::T('common', 'Add module'), Url::toRoute('credit-audit/manual-module-add'), 0),
));
?>


<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'Head Code') ?></th>
        <th><?php echo Yii::T('common', 'Head Name') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'update time') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>

    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode($value['head_code']); ?></td>
            <td><?= Html::encode($value['head_name']); ?></td>
            <td><?= Html::encode(ManualCreditModule::$status_list[$value['status']]); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s',$value['created_at'])); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s',$value['updated_at'])); ?></td>
            <td><a href="<?php echo Url::toRoute(['credit-audit/manual-module-edit', 'id' => $value['id']]); ?>"><?php echo Yii::T('common', 'edit') ?></a></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>
