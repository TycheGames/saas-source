<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use backend\models\remind\RemindSmsTemplate;

$this->shownav('customer', 'menu_remind_sms_template');

$this->showsubmenu(Yii::T('common', 'Remind setting'), array(
    array('Template List', Url::toRoute('customer/remind-sms-template'), 1),
    array('Add', Url::toRoute(['customer/remind-template-add']), 0),
));
?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'template name') ?></th>
        <th><?php echo Yii::T('common', 'package name') ?></th>
        <th><?php echo Yii::T('common', 'template content') ?></th>
        <th><?php echo Yii::T('common', 'template status') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'update time') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php if($list):?>
        <?php foreach ($list as $value): ?>
        <tr>
            <td><?php echo Html::encode($value->name);?></td>
            <td><?php echo Html::encode($value->package_name);?></td>
            <td><?php echo Html::encode($value->content);?></td>
            <td><?php echo Html::encode(Yii::T('common', RemindSmsTemplate::$status_map[$value->status]));?></td>
            <td><?php echo Html::encode(date('Y-m-d H:i:s', $value->created_at));?></td>
            <td><?php echo Html::encode(date('Y-m-d H:i:s', $value->updated_at));?></td>
            <td><a href="<?= Url::to(['customer/remind-template-edit', 'id' => $value->id]);?>"><?php echo Yii::T('common', 'edit') ?></a></td>
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