<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/1/18
 * Time: 14:15
 */
use yii\helpers\Url;
use backend\components\widgets\ActiveForm;
use yii\grid\GridView;
use backend\assets\AppAsset;
use common\models\pay\PayoutAccountInfo;
use yii\helpers\Html;

AppAsset::register($this);

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_merchant_list');


$this->showsubmenu(Yii::T('common', 'Account Management'), array(
    array(Yii::T('common', 'List'), Url::toRoute('merchant-setting/account-list'), 1),
    array(Yii::T('common', 'Add Razorpay Account'), Url::toRoute(['merchant-setting/payout-account-add', 'type' => 1]), 0),
    array(Yii::T('common', 'Add Mpurse Account'), Url::toRoute(['merchant-setting/payout-account-add', 'type' => 2]), 0),
    array(Yii::T('common', 'Add CashFree Account'), Url::toRoute(['merchant-setting/payout-account-add', 'type' => 3]), 0),
    array(Yii::T('common', 'Add PayTM Account'), Url::toRoute(['merchant-setting/payout-account-add', 'type' => 4]), 0),

));

?>

<html>
    <?php $this->beginPage() ?>
    <head>
        <script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>

    <?php ActiveForm::end(); ?>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'label' => 'id',
                'attribute' => 'id',
            ],
            [
                'label' => Yii::T('common', 'merchant_id'),
                'attribute' => 'merchant_id',
                'value' => function($model)
                {
                    return Html::encode(\backend\models\Merchant::getMerchantId()[$model->merchant_id]);
                },
            ],
            [
                'label' => Yii::T('common', 'name'),
                'attribute' => 'name',
                'enableSorting' => false,
            ],
            [
                'label' => Yii::T('common', 'type'),
                'attribute' => 'service_type',
                'value' => function($model)
                {
                    return Html::encode(PayoutAccountInfo::$service_type_map[$model->service_type]);
                },
            ],
            [
                'label' => Yii::T('common', 'remark'),
                'attribute' => 'remark',
            ],
            [
                'label' => Yii::T('common', 'Creation time'),
                'attribute' => 'created_at',
                'value' => function($model)
                {
                    return date('Y-m-d H:i:s', $model->created_at);
                },
            ],
            [
                'label' => Yii::T('common', 'update time'),
                'attribute' => 'updated_at',
                'value' => function($model)
                {
                    return date('Y-m-d H:i:s', $model->updated_at);
                },
            ],
            [
                'header' => Yii::T('common', 'operation'),
                'class' => 'yii\grid\ActionColumn',
                'template' => '{edit} {info}',
                'buttons' => [
                    'edit' => function ($url, $model, $key) {
                        return Html::a(Yii::T('common', 'edit'), ['payout-account-edit', 'id' => $model->id]);
                    },
                    'info' => function ($url, $model, $key) {
                        return Html::a(Yii::T('common', 'detail'), ['payout-account-detail', 'id' => $model->id]);
                    },
                ],
            ]
        ]

    ]);?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>