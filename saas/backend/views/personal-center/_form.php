<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\services\FileStorageService;
use kartik\file\FileInput;
use common\models\package\PackageSetting;
use backend\assets\AppAsset;

AppAsset::register($this);

$fileStorageService = new FileStorageService(false);
$arrPackage = PackageSetting::getPackageMap();
?>
<html>
<?php $this->beginPage() ?>
<head>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="loan-fund-form">
    <?php $form = ActiveForm::begin(); ?>

    <?php if (!empty($model->icon)): ?>
        <img class="gallery-pic" height="100"
             src="<?= $fileStorageService->getSignedUrl($model->icon); ?>"/>
    <?php endif; ?>
    <?php echo $form->field($model, 'icon')->widget(FileInput::class, [
        'options' => [
            'accept'   => 'image/*',
            'multiple' => false,
        ],
    ]); ?>

    <?php echo $form->field($model, 'title')->textInput(); ?>
    <?php echo $form->field($model, 'is_finish_page')->dropDownList([0 => 'no', 1 => 'yes']); ?>
    <?php echo $form->field($model, 'path')->dropDownList(['/h5/webview' => '/h5/webview', '/app/open_browser' => '/app/open_browser']); ?>
    <?php echo $form->field($model, 'jump_page')->textInput(); ?>
    <?php echo $form->field($model, 'sorting')->input('int', ['value'=>0]); ?>
    <?php echo $form->field($model, 'package_setting_id')->dropDownList($arrPackage); ?>
    <?php echo $form->field($model, 'is_google_review')->dropDownList([0=>'No', 1=>'Yes']); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>