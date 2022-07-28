<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\models\remind\RemindAdmin;

$this->shownav('customer', 'menu_remind_admin_list');

$this->showsubmenu(Yii::T('common', 'Remind admin list'), array(
    array('List', Url::toRoute('customer/remind-admin-list'), 1),
    array('Add', Url::toRoute(['customer/remind-admin-add']), 0),
));
?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'username') ?></th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <?php if($isNotMerchantAdmin): ?>
            <th><?php echo Yii::T('common', 'merchant') ?></th>
        <?php endif;?>
        <th><?php echo Yii::T('common', 'Group') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php if($list):?>
        <?php foreach ($list as $value): ?>
        <tr>
            <td><?php echo Html::encode($value['username']);?></td>
            <th><?php echo Html::encode($isHiddenPhone ? substr_replace($value['phone'],'*****',0,5) : $value['phone']); ?></th>
            <?php if($isNotMerchantAdmin): ?>
                <th><?php echo Html::encode($value['merchant_name'] ?? '无') ?></th>
            <?php endif;?>
            <td><?php echo Html::encode($value['name'] ?? '-');?></td>
            <td><a href="<?php echo Url::to(['customer/remind-admin-edit', 'id' => $value['id']]); ?>"><?php echo Yii::T('common', 'edit') ?></a>&nbsp;&nbsp;<a onclick="javascript:if(!confirm('Are you sure to delete <?=Html::encode($value['username']) ?>')){return false};" href="<?php echo Url::to(['customer/remind-admin-del', 'id' => $value['id']]); ?>"><?php echo Yii::T('common', 'del') ?></a></td>
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
