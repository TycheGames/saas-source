<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\assets\AppAsset;
use common\services\FileStorageService;
use common\models\package\PackageSetting;
use yii\bootstrap\ActiveForm;

AppAsset::register($this);

echo $this->render('/tab-bar-icon/submenus');

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
    <label for="package_setting_id">Package</label> &nbsp;&nbsp;
    <select name="package_setting_id" id="package_setting_id" class="form-control" style="width:120px;">
        <?php foreach ($package as $key => $value): ?>
            <?php if($key == Yii::$app->getRequest()->get('package_setting_id', '')):?>
                <option selected value="<?php echo Html::encode($key) ?>"><?php echo Html::encode($value) ?></option>
            <?php else:?>
                <option value="<?php echo Html::encode($key) ?>"><?php echo Html::encode($value) ?></option>
            <?php endif;?>
        <?php endforeach; ?>
    </select>
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
                    'attribute' => 'title',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'normal_img',
                    'enableSorting' => false,
                    "format" => [
                        "image",
                        [
                            "width"  => "40px",
                            "height" => "40px"
                        ]
                    ],
                    'value' => function($model) {
                        if (empty($model->normal_img)) {
                            return '';
                        } else {
                            $fileStorageService = new FileStorageService(false);
                            return $fileStorageService->getSignedUrl($model->normal_img);
                        }
                    }
                ],
                [
                    'attribute' => 'select_img',
                    'enableSorting' => false,
                    "format" => [
                        "image",
                        [
                            "width"  => "40px",
                            "height" => "40px"
                        ]
                    ],
                    'value' => function($model) {
                        if (empty($model->select_img)) {
                            return '';
                        } else {
                            $fileStorageService = new FileStorageService(false);
                            return $fileStorageService->getSignedUrl($model->select_img);
                        }
                    }
                ],
                [
                    'attribute' => 'normal_color',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'select_color',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'package_setting_id',
                    'enableSorting' => false,
                    'value' => function($model) {
                        return Html::encode(PackageSetting::findOne($model->package_setting_id)['name']);
                    }
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
