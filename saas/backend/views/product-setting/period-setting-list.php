<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use common\models\product\ProductPeriodSetting;
use backend\models\Merchant;
$this->shownav('system', 'menu_product_begin');

$this->showsubmenu(Yii::T('common', 'Type management'), array(
    array('List', Url::toRoute('product-setting/period-setting-list'), 1),
    array('Add type', Url::toRoute(['product-setting/period-setting-add']), 0),
));

/**
 * @var bool $isNotMerchantAdmin
 */
?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'merchant') ?></th>
        <th><?php echo Yii::T('common', 'Period of each period') ?></th>
        <th><?php echo Yii::T('common', 'periods') ?></th>
        <?php if ($isNotMerchantAdmin): ?>
            <th><?php echo Yii::T('common', 'is internal') ?></th>
        <?php endif; ?>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'updated time') ?></th>
        <th><?php echo Yii::T('common', 'Operator') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php if($list):?>
        <?php foreach ($list as $value): ?>
        <tr>
            <td><?php echo Html::encode(Merchant::getMerchantId()[$value->merchant_id]);?></td>
            <td><?php echo Html::encode($value->loan_term.ProductPeriodSetting::$loan_method_map[$value->loan_method]);?></td>
            <td><?php echo Html::encode($value->periods);?></td>
            <?php if ($isNotMerchantAdmin): ?>
                <td><?php echo Html::encode(ProductPeriodSetting::$isInternal[$value->is_internal]) ?></td>
            <?php endif; ?>
            <td><?php echo date('Y-m-d H:i:s', $value->created_at);?></td>
            <td><?php echo date('Y-m-d H:i:s', $value->updated_at);?></td>
            <td><?php echo Html::encode($value->operator_name);?></td>
            <td><?php echo Html::encode(ProductPeriodSetting::$statusMap[$value->status]);?></td>
            <td>
                <a href="<?php echo Url::toRoute(['product-setting/period-setting-edit', 'id' => $value->id]);?>"><?php echo Yii::T('common', 'edit') ?></a> |
                <a class="delItem" href="javascript:void(0)" tip="<?= Url::to(['product-setting/period-setting-del', 'id' => $value->id]);?>"><?php echo Yii::T('common', 'del') ?></a>
            </td>
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
<?php echo LinkPager::widget(['pagination' => $pages, 'firstPageLabel' => "first page", 'lastPageLabel' => "last page"]); ?>
<script>
    $('.delItem').click(function(){
        var url = $(this).attr('tip');
        if(confirm("<?php echo Yii::T('common', 'Are you sure you want to delete it ?') ?>")) {
            window.location.href = url;
        }
    })
</script>