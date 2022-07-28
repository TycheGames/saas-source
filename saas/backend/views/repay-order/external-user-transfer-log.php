<?php

use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\helpers\CommonHelper;
use yii\helpers\Html;
use common\services\FileStorageService;
use common\models\order\UserLoanOrderRepayment;
use yii\helpers\Url;

$service = new FileStorageService();
$this->shownav('loanOrder', 'menu_external_user_transfer_log');
$this->showsubmenu(Yii::T('common', 'External user transfer log'), array(
));
?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.photo.gallery.js"></script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'orderId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Creation time') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('begintime', '')); ?>" name="begintime" onfocus="WdatePicker({startDate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})">
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('endtime', '')); ?>"  name="endtime" onfocus="WdatePicker({startDcreated_atate:'<?=date('Y-m-d H:i:00') ?>',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})">
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>ID</th>
                <th><?php echo Yii::T('common', 'orderId') ?></th>
                <th><?php echo Yii::T('common', 'Repayment order ID') ?></th>
                <th>用户上传金额</th>
                <th>utr</th>
                <th>pic</th>
                <th>beneficary bank account number</th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
                <th><?php echo Yii::T('common', 'operation') ?></th>
            </tr>
            <?php
            foreach ($info as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['id'], 'log')); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value['order_id'], 'order'));?></td>
                    <td><?php echo !empty($value['repayment_id']) ? Html::encode(CommonHelper::idEncryption($value['repayment_id'], 'repayment')) : '-'; ?></td>
                    <td><?php echo Html::encode($value['amount']); ?></td>
                    <td><?php echo Html::encode($value['utr']); ?></td>
                    <td class="gallerys">
                        <?php foreach (json_decode($value['pic'],true) ?? [] as $pic): ?>
                        <image class="gallery-pic" height="50" src="<?php echo Html::encode($service->getSignedUrl($pic, 3600, 'loan_s3')); ?>">
                            <?php endforeach; ?>
                    </td>
                    <td><?php echo Html::encode($value['account_number']);?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
                    <td>
                        <?php if(!empty($value['repayment_id']) && $value['status'] != UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){?>
                            <a href="<?php echo Url::to(['finish-debit', 'id' => CommonHelper::idEncryption($value['repayment_id'], 'repayment')]);?>"><?php echo Yii::T('common', 'Manual repayment') ?></a>
                        <?php }?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($info)): ?>
            <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
        <?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script>
    $('.gallery-pic').click(function () {
        $.openPhotoGallery(this);
    });
</script>