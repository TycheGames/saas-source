<?php

use yii\grid\GridView;
use backend\assets\AppAsset;
use common\models\fund\LoanFundOperateLog;
use backend\models\Merchant;
use common\models\fund\LoanFund;
use yii\helpers\Html;

AppAsset::register($this);

echo $this->render('/loan-fund/operate-log-submenus', ['isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);

$arrData = [
    //        ['class' => 'yii\grid\SerialColumn'],
    [
        'attribute' => 'action',
        'label' => Yii::T('common', 'action'),
        'enableSorting' => false,
        'value' => function ($model) {
            return LoanFundOperateLog::$arrAction[$model->action];
        }
    ],
    [
        'label' => Yii::T('common', 'Fund ID'),
        'enableSorting' => false,
        'value' => function ($model) {
            return Html::encode($model->fund_id ?? '无');
        }
    ],
    [
        'attribute' => 'admin_name',
        'label'     => Yii::T('common', 'Operator'),
        'enableSorting' => false
    ],
    [
        'label' => Yii::T('common', 'name'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode($arrParams['name'] ?? '-');
        }
    ],
    [
        'label' => Yii::T('common', 'Merchant name'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode(Merchant::getMerchantId()[$arrParams['merchant_id']] ?? '-');
        }
    ],
    [
        'label' => Yii::T('common', 'Payment account'),
        'enableSorting' => false,
        'value' => function ($model) use ($payAccountList) {
            $arrParams = $model::getParams($model->params, $model->id);
            $payID = $arrParams['pay_account_id'] ?? 0;
            return Html::encode($payAccountList[$payID] ?? '-');
        }
    ],
    [
        'label' => Yii::T('common', 'Small loan account'),
        'enableSorting' => false,
        'value' => function ($model) use ($loanAccountList) {
            $arrParams = $model::getParams($model->params, $model->id);
            $id = $arrParams['loan_account_id'] ?? 0;
            return Html::encode($loanAccountList[$id] ?? '-');
        }
    ],
    [
        'label' => Yii::T('common', 'Daily default limit'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            $nDayQuotaDefault = $arrParams['day_quota_default'] ?? 0;
            return $nDayQuotaDefault/100 . Yii::T('common', 'yuan');
        }
    ],
    [
        'label' => Yii::T('common', 'status'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            $nStatus = $arrParams['status'] ?? 0;
            return LoanFund::STATUS_LIST[$nStatus];
        }
    ],
    [
        'label' => Yii::T('common', 'Old customer proportion'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode($arrParams['old_customer_proportion'] ?? 0 . '%');
        }
    ],
    [
        'label' => '优先级',
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode($arrParams['score'] ?? '-');
        }
    ],
    [
        'attribute' => 'created_at',
        'label' => Yii::T('common', 'Creation time'),
        'enableSorting' => false,
        'value' => function ($model) {
            return date('Y-m-d H:i:s', $model->created_at);
        }
    ],
];

?>
<html>
<?php $this->beginPage() ?>
<head>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns'      => $arrData
]);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
