<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\remind\RemindAdmin;

$this->shownav('customer', 'menu_remind_group');

$this->showsubmenu('Remind group', array(
    array('List', Url::toRoute('customer/remind-group'), 1),
    array('Add', Url::toRoute(['customer/remind-group-add']), 0),
));
?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Yii::T('common', 'merchant') ?></th>
            <th>team leader id</th>
        <?php endif;?>
        <th>group name</th>
        <th>create time</th>
        <th>update time</th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php if($list):?>
        <?php foreach ($list as $value): ?>
        <tr>
            <?php if($isNotMerchantAdmin):?>
                <th><?php echo Html::encode($value['merchant_name'] ?? '无') ?></th>
                <td><?php echo Html::encode($value['team_leader_id']);?></td>
            <?php endif;?>
            <td><?php echo Html::encode($value['name']);?></td>
            <td><?php echo date('Y-m-d',$value['created_at']);?></td>
            <td><?php echo date('Y-m-d',$value['updated_at']);?></td>
            <td><a href="<?= Url::to(['customer/remind-group-edit', 'id' => $value['id']]);?>"><?php echo Yii::T('common', 'edit') ?></a></td>
        </tr>
        <?php endforeach;?>
    <?php else:?>
        <tr >
            <td colspan="10">
                <?php echo Yii::T('common', 'Sorry, there is no qualified record for the time being!') ?>
            </td>
        </tr>
    <?php endif;?>
</table>
