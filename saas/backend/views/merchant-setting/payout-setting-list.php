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


$this->showsubmenu(Yii::T('common', '放款设置'), array(
    array(Yii::T('common', 'List'), Url::toRoute('merchant-setting/payout-setting-list'), 1),
    array(Yii::T('common', 'add'), Url::toRoute(['merchant-setting/payout-setting-add']), 0),
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
                'label' => Yii::T('common', 'account_id'),
                'attribute' => 'account_id',
                'value' => function($model)
                {
                    return Html::encode(PayoutAccountInfo::getListMap()[$model->account_id]);
                },
            ],
            [
                'label' => Yii::T('common', 'remark'),
                'attribute' => 'remark',
            ],
            [
                'label' => Yii::T('common', 'group'),
                'attribute' => 'group',
            ],

            [
                'label' => Yii::T('common', 'status'),
                'attribute' => 'status',
                'value' => function($model)
                {
                    return Html::encode(\common\models\pay\PayoutAccountSetting::$status_map[$model->status]);
                },
            ],
            [
                'label' => Yii::T('common', 'weight'),
                'attribute' => 'weight',
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
                'template' => '{edit}',
                'buttons' => [
                    'edit' => function ($url, $model, $key) {
                        return Html::a(Yii::T('common', 'edit'), ['payout-setting-edit', 'id' => $model->id]);
                    },
                ],
            ]
        ]

    ]);?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>