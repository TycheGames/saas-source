<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\workOrder\UserWorkOrderAcceptLog;
use common\services\FileStorageService;

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
                <img class="gallery-pic" height="100" src="<?=   $fileStorageService->getSignedUrl($imageUrl);?>"/>
            <?php endforeach; ?>
        </td>
    </tr>
</table>

<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15">Operate</th></tr>
        <tr>
            <td class="td24">Result</td>
            <td><?php echo Html::radioList('result', UserWorkOrderAcceptLog::RESULT_ACCEPT_COMPLETED, UserWorkOrderAcceptLog::$result_map); ?></td>
        </tr>
        <tr>
            <td class="td24">Remark：</td>
            <td><?= Html::textarea('remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="submit" value="submit" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<script>
    $('.gallery-pic').click(function(){
        $.openPhotoGallery(this);
    });
</script>