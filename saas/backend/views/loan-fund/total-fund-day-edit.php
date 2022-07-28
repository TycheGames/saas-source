<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\services\FileStorageService;
use backend\assets\AppAsset;
use common\models\fund\LoanFund;
use common\models\fund\LoanFundDayQuota;

AppAsset::register($this);

$fileStorageService = new FileStorageService(false);

$route = Yii::$app->requestedRoute;

echo $this->render('/loan-fund/submenus', ['isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);
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

    <?php echo $form->field($model, 'name')->label('资方名称')->textInput(['readonly' => 'readonly']); ?>
    <?php echo $form->field($model, 'day_quota_default')->label('今日配额(元)')->textInput(['value'=> LoanFundDayQuota::getTotalQuota($model->id) / 100]); ?>

    <?php if (!empty($isNotMerchantAdmin) && LoanFund::IS_EXPORT_YES == $model->is_export): ?>
        <?php echo $form->field($model, 'old_customer_proportion')->textInput(); ?>
        <?php echo $form->field($model, 'all_old_customer_proportion')->textInput(); ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
