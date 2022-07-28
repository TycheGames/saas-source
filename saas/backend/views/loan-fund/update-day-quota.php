<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\fund\LoanFund;
use common\models\fund\LoanFundDayQuota;

/* @var $this yii\web\View */
/* @var $model common\models\fund\LoanFundDayQuota */
/* @var $form yii\widgets\ActiveForm */
echo $this->render('/loan-fund/submenus',['route'=>Yii::$app->controller->route, 'isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<div class="loan-fund-day-quota-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'fund_id')->dropDownList($fund_options) ?>

    <?= $form->field($model, 'quota')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'remaining_quota')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'date')->textInput(['maxlength' => true,'onfocus'=>"WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})"]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::T('common', 'create') : Yii::T('common', 'update
        '), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
