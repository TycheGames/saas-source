<?php


use common\services\FileStorageService;
use common\models\workOrder\UserWorkOrderAcceptLog;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
$this->shownav('customer', 'menu_complaint_order_list');
$fileStorageService = new FileStorageService(false);
$imageArr = json_decode($userApplyComplaint['image_list']);
?>
<style>
    .table {
        max-width: 100%;
        width: 100%;
        border:1px solid #ddd;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
</style>
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.photo.gallery.js"></script>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">information</th></tr>
    <tr>
        <td class="td21">Name：</td>
        <td width="200"><?php echo Html::encode($info['name']); ?></td>
        <td class="td21">Phone：</td>
        <td width="200"><?php echo Html::encode($info['phone']); ?></td>
    </tr>
    <tr><th class="partition" colspan="10">Complaint problem：<?= Html::encode($info['problem_text']); ?></th></tr>
    <tr>
        <td class="td21">Contact Information</td>
        <td ><?= Html::encode($userApplyComplaint['contact_information'] ?? '--'); ?></td>
        <td class="td21">Description</td>
        <td ><?= Html::encode($userApplyComplaint['description'] ?? '--'); ?></td>
    </tr>
    <tr>
        <td class="td21">image</td>
        <td class="gallerys" colspan="3">
            <?php foreach ($imageArr as $imageUrl):?>
                <img class="gallery-pic" height="100" src="<?=   $fileStorageService->getSignedUrl($imageUrl);;?>"/>
            <?php endforeach; ?>
        </td>
    </tr>
</table>

<table class="tb tb2 fixpadding" id="creditreport">
    <tr><th class="partition" colspan="5">history</th></tr>
    <tr>
        <td style=" padding: 2px;margin-bottom: 1px">
            <table style="margin-bottom: 0px" class="table">
                <tr>
                    <th>operator</th>
                    <th>accept result</th>
                    <th>remark</th>
                    <th>accept time</th>
                </tr>
                <?php foreach ($acceptLog as $log): ?>
                    <tr>
                        <td><?= Html::encode($log['operator_name']);?></td>
                        <td><?= Html::encode(UserWorkOrderAcceptLog::$result_map[$log['result']] ?? '-');?></td>
                        <td><?= Html::encode($log['remark']);?></td>
                        <td><?= Html::encode(date("Y-m-d H:i:s",$log['created_at']));?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>
<script>
    $('.gallery-pic').click(function(){
        $.openPhotoGallery(this);
    });
</script>