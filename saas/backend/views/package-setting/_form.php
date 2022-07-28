<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\services\FileStorageService;
use common\models\package\PackageSetting;
use backend\assets\AppAsset;

/**
 * @var array $arrCreditAccountId
 * @var array $arrMerchantId
 */

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

    <?php echo $form->field($model, 'package_name')->textInput(); ?>
    <?php echo $form->field($model, 'source_id')->textInput(); ?>
    <?php echo $form->field($model, 'credit_account_id')->dropDownList($arrCreditAccountId); ?>
    <?php echo $form->field($model, 'name')->textInput(); ?>
    <?php echo $form->field($model, 'merchant_id')->dropDownList($arrMerchantId); ?>
    <?php echo $form->field($model, 'firebase_token')->textarea(); ?>
    <?php echo $form->field($model, 'is_use_truecaller')->dropDownList([0=>'No', 1=>'Yes']); ?>
    <?php echo $form->field($model, 'truecaller_key')->textInput(); ?>
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