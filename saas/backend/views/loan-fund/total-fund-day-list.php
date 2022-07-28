<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\AppAsset;
use common\models\fund\LoanFundDayQuota;
use common\models\fund\LoanFund;

AppAsset::register($this);

echo $this->render('/loan-fund/submenus', ['isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);

$arrData = [
    //        ['class' => 'yii\grid\SerialColumn'],
    [
        'attribute' => 'id',
        'enableSorting' => false
    ],
    [
        'attribute' => 'name',
        'enableSorting' => false,
    ],
    [
        'label' => '今日总配额',
        'enableSorting' => false,
        'value' => function ($model) {
            return LoanFundDayQuota::getTotalQuota($model->id) / 100 . Yii::T('common', 'yuan');
        }
    ],
    [
        'label' => Yii::T('common', 'Today\'s remaining credit'),
        'enableSorting' => false,
        'value' => function ($model) {
            return LoanFundDayQuota::getRemainingQuota($model->id) / 100 . Yii::T('common', 'yuan');
        }
    ],
    'old_customer_proportion' => [
        'label' => '全老本老:全老本新:全新本新 占比',
        'enableSorting' => false,
        'value' => function ($model) {
            if( LoanFund::IS_EXPORT_NO == $model->is_export)
            {
                return '-';
            }
            $selfOldAllOld = 0;
            $selfNewAllOld = 0;
            $selfNewAllNew = 0;
            $list = LoanFundDayQuota::find()->where(['fund_id' => $model->id, 'date' => date('Y-m-d')])->all();
            foreach ($list as $item)
            {
                switch ($item->type){
                    case LoanFundDayQuota::TYPE_NEW:
                        $selfNewAllNew = $item->pr;
                        break;
                    case LoanFundDayQuota::TYPE_OLD:
                        $selfNewAllOld = $item->pr;
                        break;
                    case LoanFundDayQuota::TYPE_REAL_OLD:
                        $selfOldAllOld = $item->pr;
                        break;
                }
            }

            return Html::encode("{$selfOldAllOld}:{$selfNewAllOld}:{$selfNewAllNew}");

        }
    ],
    [
        'attribute' => 'created_at',
        'enableSorting' => false,
        'value' => function ($model) {
            return date('Y-m-d H:i:s', $model->created_at);
        }
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'header' => '操作',
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, $model, $key) {
                return Html::a('调整', Url::to(['total-fund-day-edit',  'id' => $model->id])) . '<br />';
            }
        ],
        'headerOptions' => ['width' => '80'],
    ],
];

if (empty($isNotMerchantAdmin)) {
    unset($arrData['old_customer_proportion']);
}
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
    'columns' => $arrData
]);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
