<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\services\FileStorageService;
use common\models\package\PackageSetting;
use backend\assets\AppAsset;
use kartik\file\FileInput;

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

        <?php echo $form->field($model, 'title')->textInput(); ?>
        <?php echo $form->field($model, 'type')->dropDownList(['index'=>'首页', 'my'=>'个人中心']); ?>

        <!-- 未选中状态icon -->
        <?php if (!empty($model->normal_img)): ?>
            <img class="gallery-pic" height="100"
                 src="<?= $fileStorageService->getSignedUrl($model->normal_img); ?>"/>
        <?php endif; ?>
        <?php echo $form->field($model, 'normal_img')->widget(FileInput::class, [
            'options' => [
                'accept'   => 'image/*',
                'multiple' => false,
            ],
        ]); ?>
        <!-- 已选中状态icon -->
        <?php if (!empty($model->select_img)): ?>
            <img class="gallery-pic" height="100"
                 src="<?= $fileStorageService->getSignedUrl($model->select_img); ?>"/>
        <?php endif; ?>
        <?php echo $form->field($model, 'select_img')->widget(FileInput::class, [
            'options' => [
                'accept'   => 'image/*',
                'multiple' => false,
            ],
        ]); ?>

        <?php echo $form->field($model, 'normal_color')->textInput(); ?>
        <?php echo $form->field($model, 'select_color')->textInput(); ?>
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