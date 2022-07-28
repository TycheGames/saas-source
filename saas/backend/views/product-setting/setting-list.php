<?php
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\product\ProductSetting;
use backend\models\Merchant;
use backend\components\widgets\LinkPager;


echo $this->render('/product-setting/submenus');

/**
 * @var bool $isNotMerchantAdmin
 */
?>


<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>

    <table class="tb tb2 fixpadding">
        <tr class="header">

            <th>merchant</th>
            <th><?php echo Yii::T('common', 'product name') ?></th>
            <th><?php echo Yii::T('common', 'productType') ?></th>
            <?php if ($isNotMerchantAdmin): ?>
                <th><?php echo Yii::T('common', 'is internal') ?></th>
            <?php endif; ?>
            <th><?php echo Yii::T('common', 'Daily interest rate') ?></th>
            <th><?php echo Yii::T('common', 'Service rate') ?></th>
            <th><?php echo Yii::T('common', 'Expected rate') ?></th>
            <th><?php echo Yii::T('common', 'status') ?></th>
            <?php if ($isNotMerchantAdmin): ?>
                <th><?php echo Yii::T('common', 'is delay') ?></th>
            <?php endif; ?>
            <th><?php echo Yii::T('common', 'Operator') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>

        </tr>
        <?php if (!empty($product_setting)):?>
            <?php foreach ($product_setting as $key => $item):?>
                <tr data-id="<?=$item['id']?>">
                    <td><?php echo Html::encode(Merchant::getMerchantId()[$item->merchant_id]);?></td>
                    <td><?=Html::encode($item['product_name'])?></td>
                    <td>
                        <?php
                        $periodSetting = $item->getProductPeriodSetting()->one();
                        if ($periodSetting) {
                            echo Html::encode($periodSetting['periods'].'periods'.'/'.$periodSetting['loan_term'].$periodSetting::$loan_method_map[$periodSetting['loan_method']]);
                        }
                        ?>
                    </td>
                    <?php if ($isNotMerchantAdmin): ?>
                        <td><?php echo Html::encode(ProductSetting::$isInternal[$item->is_internal]) ?></td>
                    <?php endif; ?>
                    <td><?=Html::encode($item['day_rate'].'%')?></td>
                    <td><?=Html::encode($item['cost_rate'].'%')?></td>
                    <td><?=Html::encode($item['overdue_rate'].'%')?></td>
                    <td>
                        <?php echo $item['status'] === ProductSetting::STATUS_USABLE ? '<span class="yes">'.ProductSetting::$status[$item['status']] ?? ''.'</span>' : '<span class="no">'.ProductSetting::$status[$item['status']] ?? ''.'</span>';?>
                    </td>
                    <?php if ($isNotMerchantAdmin): ?>
                        <td><?php echo $item->delay_status == 1 ? '启用' : '停用' ?></td>
                    <?php endif; ?>
                    <td><?=Html::encode($item['opreate_name'])?></td>
                    <td>
                        <a href="<?=Url::toRoute(['product-setting/setting-edit','id'=>$item['id']])?>" ><?php echo Yii::T('common', 'edit') ?></a>&nbsp;&nbsp;
                        <a class="delItem" href="javascript:void(0)" tip="<?= Url::toRoute(['product-setting/product-setting-del','id'=>$item['id']]);?>"><?php echo Yii::T('common', 'del') ?></a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php endif;?>
    </table>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script>
    $('.delItem').click(function(){
        var url = $(this).attr('tip');
        if(confirm('Are you sure you want to delete it ?')) {
            window.location.href = url;
        }
    })
</script>
