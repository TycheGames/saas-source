<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\risk\RiskRules;
use common\models\risk\RuleVersion;

$this->title = Yii::T('common', 'Feature list');
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
        function reject(id, version) {
            if (confirm("<?php echo Yii::T('common', 'Determining disable feature') ?>" + id + "?")) {
                $.post(
                    '<?php echo $this->baseUrl; ?>/index.php?r=version-decision-tree/characteristics-reject&id=' + id + '&version=' + version,
                    {},
                    function (ret) {
                        if (ret) {
                            $("#reject_" + id).attr('href', 'javascript:debug(' + id + ',' + version + ')');
                            $("#reject_" + id).text("<?php echo Yii::T('common', 'debug') ?>");
                            $("[data-key=" + id + "]").children('td').eq(3).text("<?= RiskRules::$status_map[RiskRules::STATUS_STOP] ?>");
                        } else {
                            alert("<?php echo Yii::T('common', 'Disable feature') ?>" + id + "<?php echo Yii::T('common', 'failure') ?>");
                        }
                    },
                    'text'
                );
            }
        }
        function debug(id, version) {
            $.post(
                '<?php echo $this->baseUrl; ?>/index.php?r=version-decision-tree/characteristics-debug&id=' + id + '&version=' + version,
                {},
                function (ret) {
                    if (ret) {
                        $("#reject_" + id).attr('href', 'javascript:approve(' + id + ',' + version + ')');
                        $("#reject_" + id).text("<?php echo Yii::T('common', 'Enable') ?>");
                        $("[data-key=" + id + "]").children('td').eq(3).text("<?= RiskRules::$status_map[RiskRules::STATUS_TEST] ?>");
                    } else {
                        alert("<?php echo Yii::T('common', 'feature') ?>\n" + id + "<?php echo Yii::T('common', 'Set to debug failed') ?>");
                    }
                },
                'text'
            );
        }
        function approve(id, version) {
            if (confirm("<?php echo Yii::T('common', 'Determining enable feature') ?>" + id + "?")) {
                $.post(
                    '<?php echo $this->baseUrl; ?>/index.php?r=version-decision-tree/characteristics-approve&id=' + id + '&version=' + version,
                    {},
                    function (ret) {
                        if (ret) {
                            $("#reject_" + id).attr('href', 'javascript:reject(' + id + ',' + version + ')');
                            $("#reject_" + id).text("<?php echo Yii::T('common', 'Disable') ?>");
                            $("[data-key=" + id + "]").children('td').eq(3).text("<?= RiskRules::$status_map[RiskRules::STATUS_OK] ?>");
                        } else {
                            alert("<?php echo Yii::T('common', 'Enable feature') ?>" + id + "<?php echo Yii::T('common', 'failure') ?>");
                        }
                    },
                    'text'
                );
            }
        }
    </script>

    <body>
    <?php $this->beginBody() ?>

    <div class="rule-index">

        <h1><?= Html::encode($this->title) ?></h1>
        <div style="width: 100%;height: 5px;"></div>
        <p>
            <?= Html::a(Yii::T('common', 'New'), ['rule-add'], ['class' => 'btn btn-success']) ?>
        </p>
        <div style="width: 100%;height: 5px;"></div>
        <?php
        $columns = [
            [
                'header' => 'ID',
                'attribute' => 'id',
                'value' => function ($model) {
                    return Html::encode($model->id);
                },
            ],
            [
                'header' => Yii::T('common', 'Rule name'),
                'attribute' => 'alias',
                'value' => function ($model) {
                    return Html::encode($model->alias);
                },
            ],
            [
                'header' => Yii::T('common', 'version'),
                'attribute' => 'version',
                'filter' => RuleVersion::getVersionList(),
                'value' => function ($model) {
                    $version_list = RuleVersion::getVersionList();
                    return Html::encode(in_array($model->version, array_keys($version_list)) ? $version_list[$model->version] : RuleVersion::DEFAULT_VERSION);
                },
                'headerOptions' => ['style'=>'width:100px'],
            ],
            [
                'header' => Yii::T('common', 'Node code'),
                'attribute' => 'code',
                'value' => function ($model) {
                    return Html::encode($model->code);
                },
            ],
            [
                'header' => Yii::T('common', 'priority'),
                'attribute' => 'order',
                'value' => function ($model) {
                    return Html::encode($model->order);
                },
            ],
            [
                'header' => Yii::T('common', 'expression'),
                'attribute' => 'guard',
                'value' => function ($model) {
                    return $model->guard;
                },
            ],
            [
                'header' => Yii::T('common', 'return'),
                'attribute' => 'result',
                'value' => function ($model) {
                    return $model->result;
                },
            ],
            [
                'header' => Yii::T('common', 'type'),
                'attribute' => 'type',
                'filter' => RiskRules::$type_map,
                'value' => function ($model) {
                    return Html::encode(in_array($model->type, array_keys(RiskRules::$type_map)) ? Yii::T('common', RiskRules::$type_map[$model->type]) : Yii::T('common', 'unknown'));
                },
            ],
            [
                'header' => Yii::T('common', 'status'),
                'attribute' => 'status',
                'filter' => RiskRules::$status_map,
                'value' => function ($model) {
                    return Html::encode(in_array($model->status, array_keys(RiskRules::$status_map)) ? Yii::T('common', RiskRules::$status_map[$model->status]) : Yii::T('common', 'unknown'));
                },
            ],
        ];


        $tmp = [
            'header' => Yii::T('common', 'operating'),
            'class' => 'yii\grid\ActionColumn',
            'template' => '<table>
                        <tr><td>{update}</td><td>{viewdependencetree}</td></tr>
                        </table>',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a(Yii::T('common', 'edit'), $model->type == RiskRules::TYPE_BASE ? ['rule-node-edit', 'id' => $model->id] :['rule-edit', 'code' => $model->code, 'alias' => $model->alias, 'version' => $model->version], ['class' => '']);
                },
                'viewdependencetree' => function ($url, $model, $key) {
                    return Html::a(Yii::T('common', 'View dependency'), ['view-dependence-tree', 'code' => $model->code, 'version' => $model->version], ['class' => '']);
                },
            ],
        ];

         $columns[] = $tmp;
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $columns
        ]); ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>