<?php

use yii\grid\GridView;
use backend\assets\AppAsset;
use common\models\razorpay\RazorpayVirtualAccount;
use yii\widgets\ActiveForm;
use common\helpers\CommonHelper;
use yii\helpers\Html;

AppAsset::register($this);

?>
<html>
<?php $this->beginPage() ?>
    <head>
        <?php $this->head() ?>
    </head>
    <body>
    <?php

    $arrColumns = [
        'merchant_id' => [
            'label' => Yii::T('common', 'belongsToMerchants'),
            'attribute' => 'merchant_id',
            'filter' => \backend\models\Merchant::getMerchantId(),
            'filterInputOptions' => ['class' => 'form-control', 'prompt' => '全部'],
            'enableSorting' => false,
            'value' => function($model){
                return Html::encode(\backend\models\Merchant::getMerchantId()[$model['merchant_id']]);
            }
        ],
        [
            'label' => 'pay_account_id',
            'attribute' => 'pay_account_id',
            'enableSorting' => false,
        ],
        [
            'attribute' => 'user_id',
            'enableSorting' => false,
            'value' => function ($model) {
                return Html::encode(CommonHelper::idEncryption($model->user_id, 'user'));
            }
        ],
        [
            'attribute' => 'order_id',
            'enableSorting' => false,
            'value' => function ($model) {
                return Html::encode(CommonHelper::idEncryption($model->order_id, 'order'));
            }
        ],
        [
            'label' => 'vid',
            'attribute' => 'vid',
            'enableSorting' => false,
        ],
        [
            'label' => '收款人',
            'attribute' => 'va_name',
            'enableSorting' => false
        ],
        [
            'label' => '虚拟账号',
            'attribute' => 'va_account',
            'enableSorting' => false,
        ],
        [
            'attribute' => 'va_ifsc',
            'enableSorting' => false
        ],
        [
            'label' => '虚拟upi',
            'attribute' => 'address',
            'enableSorting' => false,
        ],
        [
            'label' => '状态',
            'attribute' => 'status',
            'enableSorting' => false,
            'value' => function($model){
                return RazorpayVirtualAccount::$status_map[$model->status];
            }
        ],
        [
            'label' => '创建时间',
            'attribute' => 'created_at',
            'enableSorting' => false,
            'value' => function($model){
                return date('Y-m-d H:i:s', $model->created_at);
            }
        ],
    ];


    if (empty($isNotMerchantAdmin)) {
        unset($arrColumns['merchant_id']);
    }

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $arrColumns
        ]);
    ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
