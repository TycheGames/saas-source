<?php

use yii\grid\GridView;
use backend\assets\AppAsset;
use common\models\financial\FinancialPaymentOrder;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\CommonHelper;

AppAsset::register($this);

?>
<html>
<?php $this->beginPage() ?>
<head>
    <?php $this->head() ?>
</head>
<body>
<?php $form = \yii\widgets\ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;', 'class'=>'form-inline']]); ?>
<?php echo Html::dropDownList('is_summary', yii::$app->request->get('is_summary'), FinancialPaymentOrder::$summary_map);?> &nbsp;&nbsp;&nbsp;<?php echo Yii::T('common', 'Show summary (after checking, the query will slow down)') ?>
<input type="submit" name="search_submit" value="filter" class="btn">
<input type="submit" name="exportcsv" value="exportcsv" class="btn" />
<?php \yii\widgets\ActiveForm::end(); ?>
<br />
<?php

$arrColumns = [
    [
        'label' => '支付账号ID',
        'attribute' => 'pay_account_id',
        'enableSorting' => false,
    ],
    [
        'label' => '订单号',
        'attribute' => 'order_id',
        'enableSorting' => false,
    ],
    [
        'label' => 'type',
        'attribute' => 'type',
        'enableSorting' => false,
        'value' => function($model){
            return FinancialPaymentOrder::$type_map[$model['type']];
        }
    ],
    [
        'label' => '还款类型',
        'attribute' => 'payment_type',
        'enableSorting' => false,
        'value' => function($model){
            return isset($model['payment_type']) ? FinancialPaymentOrder::$payment_type_map[$model['payment_type']] : '--';
        }
    ],
    [
        'attribute' => 'user_id',
        'enableSorting' => false,
        'value' => function ($model) {
            return Html::encode(CommonHelper::idEncryption($model['user_id'], 'user'));
        }
    ],
    [
        'attribute' => 'name',
        'enableSorting' => false,
    ],
    [
        'attribute' => 'phone',
        'enableSorting' => false,
    ],
    [
        'attribute' => 'email_address',
        'enableSorting' => false,
        'value' => function($model){
            $list = [];
            $infos = \common\models\user\UserBasicInfo::find()->select(['email_address'])->where(['user_id' => $model['user_id']])->distinct(['email_address'])->asArray()->all();
            foreach ($infos as $info)
            {
                $list[] = $info['email_address'];
            }
            return implode(',', $list);
        }
    ],
    [
        'label' => '金额',
        'attribute' => 'amount',
        'enableSorting' => false,
        'value' => function($model){
            return CommonHelper::CentsToUnit($model['amount']);
        }
    ],
    [
        'label' => 'pay_order_id',
        'attribute' => 'pay_order_id',
        'enableSorting' => false,
    ],
    [
        'label' => 'pay_payment_id',
        'attribute' => 'pay_payment_id',
        'enableSorting' => false
    ],
    [
        'label' => 'pay_account_id',
        'attribute' => 'pay_account_id',
        'enableSorting' => false
    ],
    [
        'label' => '状态',
        'attribute' => 'status',
        'filter' => FinancialPaymentOrder::$status_map,
        'filterInputOptions' => ['class' => 'form-control', 'prompt' => '全部'],
        'enableSorting' => false,
        'value' => function($model){
            return FinancialPaymentOrder::$status_map[$model['status']];
        }
    ],
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
        'label' => '已入账',
        'attribute' => 'is_booked',
        'filter' => FinancialPaymentOrder::$is_booked_map,
        'filterInputOptions' => ['class' => 'form-control', 'prompt' => '全部'],
        'enableSorting' => false,
        'value' => function($model){
            return FinancialPaymentOrder::$is_booked_map[$model['is_booked']];
        }
    ],
    [
        'label' => '已退款',
        'attribute' => 'is_refund',
        'filter' => FinancialPaymentOrder::$is_refund_map,
        'filterInputOptions' => ['class' => 'form-control', 'prompt' => '全部'],
        'enableSorting' => false,
        'value' => function($model){
            return FinancialPaymentOrder::$is_refund_map[$model['is_refund']];
        },
    ],
    [
        'label' => '创建时间',
        'format' => ['date', "php:Y-m-d H:i:s"],
        'attribute' => 'created_at',
        'enableSorting' => false,
        'filter' => DateRangePicker::widget([
            'model' => $searchModel,
            'attribute' => 'created_at',
            'value' => $searchModel->created_at,
            'convertFormat'=>true,
            'readonly' => true,
            'hideInput'=>true,
            'pluginOptions'=>[
                'timePicker'=>true,
                'locale'=>['format'=>'Y-m-d']
            ],
        ]),

    ],
    [
        'label' => '成功时间',
        'attribute' => 'success_time',
        'enableSorting' => false,
        'format' => ['date', "php:Y-m-d H:i:s"],
        'filter' => DateRangePicker::widget([
            'model' => $searchModel,
            'attribute' => 'success_time',
            'value' => $searchModel->success_time,
            'convertFormat'=>true,
            'readonly' => true,
            'hideInput'=>true,
            'pluginOptions'=>[
                'locale'=>['format'=>'Y-m-d']
            ]
        ])
    ],
    [
        'label' => '备注',
        'attribute' => 'remark',
        'enableSorting' => false
    ],
    [
        'header' => '操作',
        'class' => 'yii\grid\ActionColumn',
        'template' => '{refund}',
        'buttons' => [
            'refund' => function ($url, $model, $key) {
                if($model['is_refund'] == FinancialPaymentOrder::IS_REFUND_NO
                    && $model['is_booked'] == FinancialPaymentOrder::IS_BOOKED_NO
                    && $model['status'] == FinancialPaymentOrder::STATUS_SUCCESS)
                {
                    return Html::button('退款', ['onclick' => "confirmRefund({$model['id']})"]);
                }
            },
        ],

    ]
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
<script>
    function confirmRefund(id)
    {
        var $remark = prompt('记录id为'+ id +',请输入退款备注');
        if(null == $remark)
        {
            return false;
        }
        $.post('<?= Url::toRoute('repay-order/confirm-refund');?>',{
            id : id,
            remark : $remark,
            _csrf : '<?= Yii::$app->request->getCsrfToken();?>'
        },function (data){
            alert(data.message);
            if(data.code == 0)
            {
                window.location.reload();
            }
        });

    }
</script>
</html>
<?php $this->endPage() ?>
