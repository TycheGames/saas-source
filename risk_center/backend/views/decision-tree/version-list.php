<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = Yii::T('common', 'List of versions');
use backend\assets\AppAsset;

AppAsset::register($this);
?>
    <html>
    <?php $this->beginPage() ?>
    <head>
        <script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
        <?php $this->head() ?>
    </head>
    <script type="text/javascript">
        var copy_result = <?= empty($copy_result)? "false" : "true" ?>;
        function copyVersion(version) {
            $(".field-ruleversion-version_base_by").hide();
            $(".map-form").show();
            $("#ruleversion-version_base_by").val(version);
        }

        function setDefault(version) {
            if (confirm("<?php echo Yii::T('common', 'Are you sure to set version') ?>" + version + "<?php echo Yii::T('common', 'to the default version?') ?><?php echo Yii::T('common', '(This version will be executed online after setup)') ?>")) {
                $.post(
                    '<?php echo $this->baseUrl; ?>/index.php?r=decision-tree/set-default&version=' + version,
                    {},
                    function (ret) {
                        if (ret) {
                            alert("<?php echo Yii::T('common', 'Setting version') ?>" + version + "<?php echo Yii::T('common', 'to the default version succeeded') ?>");
                        } else {
                            alert("<?php echo Yii::T('common', 'Setting version') ?>" + version + "<?php echo Yii::T('common', 'to the default version failed') ?>");
                        }
                        location = location;
                    },
                    'text'
                );
            }
        }

        function setGray(version) {
            if (confirm("<?php echo Yii::T('common', 'Are you sure to set version') ?>" + version + "<?php echo Yii::T('common', 'to the grayscale version?') ?><?php echo Yii::T('common', '(This version will be executed online after setup)') ?>")) {
                $.post(
                    '<?php echo $this->baseUrl; ?>/index.php?r=decision-tree/set-gray&version=' + version,
                    {},
                    function (ret) {
                        if (ret) {
                            alert("<?php echo Yii::T('common', 'Setting version') ?>" + version + "<?php echo Yii::T('common', 'to the grayscale version succeeded') ?>");
                        } else {
                            alert("<?php echo Yii::T('common', 'Setting version') ?>" + version + "<?php echo Yii::T('common', 'to the grayscale version failed') ?>");
                        }
                        location = location;
                    },
                    'text'
                );
            }
        }

        $(function () {
            if (copy_result) {
                alert("<?php echo Yii::T('common', 'Copy failed') ?>");
            }
            $(".version-copy-cancel").click(function () {
                $(".map-form").hide();
            });
        });
    </script>

    <body>
    <?php $this->beginBody() ?>

    <div class="rule-index">

        <h1><?= Html::encode($this->title) ?></h1>
        <div style="width: 100%;height: 5px;"></div>
        <?php
        $columns = [
            [
                'header'    =>  Yii::T('common', 'version'),
                'enableSorting' => true,
                'attribute' => 'version',
                'value' => function ($model) {
                    return Html::encode($model->version);
                },
            ],
            [
                'header'    =>  Yii::T('common', 'from'),
                'attribute' => 'version_base_by',
                'value' => function ($model) {
                    return Html::encode($model->version_base_by);
                },
            ],
            [
                'header'    =>  Yii::T('common', 'default version'),
                'attribute' => 'is_default',
                'filter' => [1 => Yii::T('common', 'yes'), 0 => Yii::T('common', 'no')],
                'value' => function ($model) {
                    return Html::encode($model->is_default ? Yii::T('common', 'yes') : Yii::T('common', 'no'));
                },
                'headerOptions' => ['style'=>'width:100px'],
            ],
            [
                'header'    =>  Yii::T('common', 'grayscale version'),
                'attribute' => 'is_gray',
                'filter' => [1 => Yii::T('common', 'yes'), 0 => Yii::T('common', 'no')],
                'value' => function ($model) {
                    return Html::encode($model->is_gray ? Yii::T('common', 'yes') : Yii::T('common', 'no'));
                },
                'headerOptions' => ['style'=>'width:100px'],
            ],
            [
                'header'    =>  Yii::T('common', 'Remarks'),
                'attribute' => 'remark',
                'value' => function ($model) {
                    return Html::encode($model->remark);
                },
            ],
            [
                'header'    =>  Yii::T('common', 'Creation time'),
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return Html::encode(date('Y-m-d H:i:s', $model->created_at));
                },
            ],
            [
                'header'    =>  Yii::T('common', 'update time'),
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    return Html::encode(date('Y-m-d H:i:s', $model->updated_at));
                },
            ],
        ];

        $columns[] = [
            'header' => Yii::T('common', 'operating'),
            'class' => 'yii\grid\ActionColumn',
            'template' => '<table style="width: 150px">
                        <tr><td>{copy}</td><td>{set_default}</td><td>{set_gray}</td></tr>
                        </table>',
            'buttons' => [
                'copy' => function ($url, $model, $key) {
                    return "<a  href=\"javascript:copyVersion('$model->version')\" id='$model->version'>复制</a>";
                },
                'set_default' => function ($url, $model, $key) {
                    return $model->is_default ? "" : "<a  href=\"javascript:setDefault('$model->version')\" id='$model->version'>设为默认</a>";
                },
                'set_gray' => function ($url, $model, $key) {
                    return $model->is_gray ? "" : "<a  href=\"javascript:setGray('$model->version')\" id='$model->version'>设为灰度</a>";
                },
            ],
        ];

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $columns
        ]); ?>

    </div>

    <div class="map-form" style="position: fixed;width: 100%;height: 100%;top:0;left: 0;display: none;">
        <div class="movable-form" style="position: absolute;top:50%;left: 50%; width: 40%;height: 40%;margin-top: -20%;margin-left: -25%;background-color: white;border: 1px solid #f2f2f2;z-index: 666;padding:0 20px;border-radius: 5px;">

            <h2> <?php echo Yii::T('common', 'Dependencies') ?></h2>
            <?php $form = ActiveForm::begin(); ?>

            <table>
                <?= $form->field($ruleVersionModel, 'version')->textInput()->label(Yii::T('common', 'version')) ?>
                <?= $form->field($ruleVersionModel, 'remark')->textInput()->label(Yii::T('common', 'Remarks')) ?>
                <?= $form->field($ruleVersionModel, 'version_base_by')->hiddenInput() ?>
            </table>
            <div class="form-group">
                <?php
                echo Html::submitButton( '保存', ['class' =>'btn btn-success']);
                echo "&nbsp;";
                ?>
                <a  href="javascript:void()" class='btn btn-primary version-copy-cancel'><?php echo Yii::T('common', 'cancel') ?></a>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>