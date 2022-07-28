<?php

use yii\grid\GridView;
use backend\assets\AppAsset;
use common\models\fund\LoanFundOperateLog;
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
            return Html::encode($model->fund_id);
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
            return Html::encode($arrParams['name']);
        }
    ],
    [
        'label' => Yii::T('common', 'date'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode($arrParams['date']);
        }
    ],
    [
        'label' => Yii::T('common', 'Daily default limit'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return $arrParams['day_quota_default']/100 . Yii::T('common', 'yuan');
        }
    ],
    [
        'label' => Yii::T('common', 'Old customer proportion'),
        'enableSorting' => false,
        'value' => function ($model) {
            $arrParams = $model::getParams($model->params, $model->id);
            return Html::encode($arrParams['old_customer_proportion'] . '%');
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
