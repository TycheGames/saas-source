<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\LoanFund */

$this->title = Yii::T('common', 'Create management');
$this->params['breadcrumbs'][] = ['label' => 'Loan Funds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
echo $this->render('/loan-fund/submenus',['route'=>Yii::$app->controller->route, 'isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);
?>

<div class="loan-fund-create">

    <?= $this->render('_form', [
        'model' => $model,
        'isNotMerchantAdmin' => $isNotMerchantAdmin,
        'payAccountList' => $payAccountList,
        'loanAccountList' => $loanAccountList
    ]) ?>

</div>
