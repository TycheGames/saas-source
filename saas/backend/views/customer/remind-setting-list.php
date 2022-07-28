<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use backend\models\remind\RemindSetting;

$this->shownav('customer', 'menu_remind_setting_list');

$this->showsubmenu(Yii::T('common', 'Remind Plan'), array(
    array('List', Url::toRoute('customer/remind-setting-list'), 1),
    array('Add', Url::toRoute(['customer/remind-setting-add']), 0),
));
?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <?php if($isNotMerchantAdmin): ?>
            <th>merchant Id</th>
        <?php endif; ?>
        <th><?php echo Yii::T('common', 'plan date before day') ?></th>
        <th><?php echo Yii::T('common', 'run time') ?></th>
        <th><?php echo Yii::T('common', 'run status') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
    </tr>
    <?php if($list):?>
        <?php foreach ($list as $value): ?>
        <tr>
            <?php if($isNotMerchantAdmin): ?>
                <td><?php echo Html::encode($value->merchant_id);?></td>
            <?php endif; ?>
            <td><?php echo Html::encode($value->plan_date_before_day);?></td>
            <td><?php echo Html::encode(date('Y-m-d H:i:s', $value->run_time));?></td>
            <td><?php echo Html::encode(Yii::T('common', RemindSetting::$run_status_map[$value->run_status]));?></td>
            <td><?php echo Html::encode(date('Y-m-d H:i:s', $value->created_at));?></td>
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
<?php $page = ceil($pages->totalCount / $pages->pageSize); ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>