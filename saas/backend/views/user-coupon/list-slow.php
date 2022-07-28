<?php

/**
 * author wolfbian
 * date 2016-09-24
 */
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\coupon\UserRedPacketsSlow;
use backend\models\Merchant;

$this->showsubmenu(Yii::T('common', 'Operations Center'), array(
    array(Yii::T('common', 'Coupon template list'), Url::toRoute('user-coupon/list-slow'), 1),
    array(Yii::T('common', 'Coupon template add'), Url::toRoute('user-coupon/add-slow'), 0),
));
?>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<?php $form = ActiveForm::begin(['id' => 'searchform', 'action' => url::to(['list-slow']), 'method' => 'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'title') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('title', '')); ?>" name="title" class="txt">&nbsp;
<?php echo Yii::T('common', 'type') ?>：<?php echo Html::dropDownList('use_case', Html::encode(Yii::$app->getRequest()->get('use_case', '')), \common\helpers\CommonHelper::getListT(UserRedPacketsSlow::$use_case_arr),
        array(
            'prompt' => Yii::T('common', 'All types'),
        )); ?>&nbsp;
<?php echo Yii::T('common', 'status') ?>：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), \common\helpers\CommonHelper::getListT(UserRedPacketsSlow::$status_arr),
array(
    'prompt' => Yii::T('common', 'All types'),
)); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>

<?php ActiveForm::begin(['id' => 'listform']); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Yii::T('common', 'Merchant name') ?></th>
        <?php endif;?>
        <th><?php echo Yii::T('common', 'Coupon title') ?></th>
        <th><?php echo Yii::T('common', 'coupon amount') ?></th>
        <th><?php echo Yii::T('common', 'Coupon content') ?></th>
        <th><?php echo Yii::T('common', 'Batch number prefix') ?></th>
        <th><?php echo Yii::T('common', 'scenes to be used') ?></th>
        <th><?php echo Yii::T('common', 'Use validity') ?></th>
        <th><?php echo Yii::T('common', 'status') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
<?php foreach ($data_list as $value): ?>
        <tr class="hover">
            <?php
                $string = '';
                if($value->status == '1') {
                    $string = 'style="color:blue"';
                }
            ?>
            <?php if($isNotMerchantAdmin):?>
                <td <?php echo $string; ?>><?= Html::encode(Merchant::getMerchantId()[$value->merchant_id]); ?></td>
            <?php endif;?>
            <td <?php echo $string; ?>><?php echo Html::encode($value->title); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode($value->amount/100 ."元"); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode($value->remark); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode($value->code_pre); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode(UserRedPacketsSlow::$use_case_arr[$value->use_case] ?? '-'); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode($value->expire_str); ?></td>
            <td <?php echo $string; ?>><?php echo Html::encode(UserRedPacketsSlow::$status_arr[$value->status] ?? '-'); ?></td>
            <td>
                <a href="<?= Url::to(['edit-slow', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'edit') ?></a>
                <a href="<?= Url::to(['update-slow', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'Update status') ?></a>
                <a href="<?= Url::to(['show-once', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'Effective immediately') ?></a>
            </td>
        </tr>
<?php endforeach; ?>
</table>
<?php ActiveForm::end(); ?>

<?php if (empty($data_list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>