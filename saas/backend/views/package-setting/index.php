<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use backend\assets\AppAsset;

AppAsset::register($this);

echo $this->render('/package-setting/submenus');
?>
<html>
    <?php $this->beginPage() ?>
        <head>
            <?php $this->head() ?>
        </head>
    <body>
    <?php $this->beginBody() ?>

    <!-- 搜索 -->
    <?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;', 'class'=>'form-inline']]); ?>
        <br />
        <label for="package_name">Package Name</label>
        &nbsp;
        <input type="text" name="package_name" class="form-control" id="package_name" value="<?php echo Html::encode(Yii::$app->getRequest()->get('package_name', '')) ?>">
        &nbsp;
        <input type="submit" name="search_submit" value="filter" class="btn">
        <br /><br />
    <?php ActiveForm::end(); ?>

    <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
        //        ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'package_name',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'source_id',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'credit_account_id',
                    'enableSorting' => false,
                    'value' => function($model) {
                        if (!empty($model->credit_account_id)) {
                            return Html::encode(\common\models\pay\PayAccountSetting::findOne($model->credit_account_id)->name);
                        }
                    }
                ],
                [
                    'attribute' => 'name',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'merchant_id',
                    'enableSorting' => false,
                    'value' => function($model) {
                        return Html::encode(\backend\models\Merchant::getMerchantId()[$model->merchant_id]);
                    }
                ],
                [
                    'attribute' => 'firebase_token',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'is_use_truecaller',
                    'enableSorting' => false,
                    'value' => function($model) {
                        return $model->is_use_truecaller == 1 ? 'Yes' : 'No';
                    }
                ],
                [
                    'attribute' => 'truecaller_key',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'is_google_review',
                    'enableSorting' => false,
                    'value' => function($model) {
                        return $model->is_google_review == 1 ? 'Yes' : 'No';
                    }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作',
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('编辑', Url::to(['edit',  'id' => $model->id]));
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('删除',  Url::to(['delete',  'id' => $model->id]));
                        },
                    ],
                    'headerOptions' => ['width' => '80'],
                ],
            ],
        ]);
    ?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
