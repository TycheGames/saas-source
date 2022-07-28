<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\fund\LoanFund;
use backend\models\Merchant;

/* @var $this yii\web\View */
/* @var $model common\models\fund\LoanFund */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="loan-fund-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'day_quota_default')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(LoanFund::STATUS_LIST)) ?>
    <?= $form->field($model, 'open_loan')->dropDownList(LoanFund::$open_loan_map) ?>
    <?php if($model->isNewRecord): ;?>
        <?php if($isNotMerchantAdmin): ;?>
        <?= $form->field($model, 'is_export')->dropDownList(LoanFund::$is_export_map) ?>
        <?php else: ;?>
            <?= $form->field($model, 'is_export')->dropDownList([LoanFund::IS_EXPORT_NO => 'no']) ?>
        <?php endif;?>
    <?php else: ;?>
        <?= $form->field($model, 'is_export')->dropDownList(LoanFund::$is_export_map, ['disabled' => 'disabled']) ?>
    <?php endif ;?>
    <?php if(! (!$model->isNewRecord && LoanFund::IS_EXPORT_YES == $model->is_export) ) :?>
        指定app_market,多个以逗号分割,如 xxx_vivo,xxx_xiaomi,不填则代表全部
        <?= $form->field($model, 'app_markets')->textarea(['rows' => '6']); ?>
    <?php endif;?>



    <?php if($isNotMerchantAdmin):?>
        <?= $form->field($model, 'is_old_customer')->dropDownList(LoanFund::$is_old_costomer_map, ["disabled" =>$model->isNewRecord ? false : "disabled"]) ?>
        <?= $form->field($model, 'pay_account_id')->dropDownList($payAccountList) ?>
        <?= $form->field($model, 'loan_account_id')->dropDownList($loanAccountList + ['0' => '无']) ?>
        <?= $form->field($model, 'payout_group')->textInput() ?>
        <?= $form->field($model, 'score')->textInput(['maxlength' => true]) ?>
        全老本新 + 全新本新 + 全老本老 不能超过100
        <?= $form->field($model, 'old_customer_proportion')->textInput(); ?>
        <?= $form->field($model, 'all_old_customer_proportion')->textInput(); ?>
        <?= $form->field($model, 'merchant_id')->dropDownList(Merchant::getMerchantId(false)) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::T('common', 'create') : Yii::T('common', 'update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
