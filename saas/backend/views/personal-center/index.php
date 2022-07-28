<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use common\services\FileStorageService;
use common\models\package\PackageSetting;
use yii\bootstrap\ActiveForm;
use backend\assets\AppAsset;

AppAsset::register($this);

echo $this->render('/personal-center/submenus');

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
        <label for="package_setting_id">Package</label> &nbsp;
        <select name="package_setting_id" id="package_setting_id" class="form-control" style="width:120px;">
            <option value="">All</option>
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
                    'attribute' => 'sorting'
                ],
                [
                    'attribute'     => 'icon',
                    'enableSorting' => false,
                    "format" => [
                        "image",
                        [
                            "width"  => "84px",
                            "height" => "84px"
                        ]
                    ],
                    'value'=>function($model) {
                        if (empty($model->icon)) {
                            return '';
                        } else {
                            $fileStorageService = new FileStorageService(false);
                            return $fileStorageService->getSignedUrl($model->icon);
                        }
        //                return Html::img('https://yinghao-notes.oss-cn-hangzhou.aliyuncs.com/images/2019-11-21-092110.png',["width"=>"84","height"=>"84"]);
                    }
                ],
                [
                    'attribute' => 'title',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'path',
                    'enableSorting' => false
                ],
                [
                    'attribute' => 'jump_page',
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
            'caption'=>"个人中心"
        ]);
    ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
