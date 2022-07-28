<?php

use backend\assets\AppAsset;
use callcenter\models\ScriptTaskLog;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

AppAsset::register($this);

/**
 * @var ActiveDataProvider $dataProvider
 * @var ScriptTaskLog $searchModel
 */
?>

<?php $this->beginPage() ?>
    <html>
    <head>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>

    <!-- 搜索 -->
    <div class="row">
        <div class="col-10">
            <?php $form = ActiveForm::begin(['id' => 'script-task-log', 'options' => ['class' => 'form-inline']]); ?>
            <div class="col-10">
                <?php echo $form->field($searchModel, 'exec_status')->dropDownList(ScriptTaskLog::$execStatusMap, ['class' => 'form-control', 'prompt' => 'Exec Status Type'])->label('执行状态：'); ?>
            </div>
            <div class="col-2">
                <?php echo Html::submitButton('搜索', ['class' => 'btn btn-primary']); ?>
            </div>
            <?php ActiveForm::end() ?>
        </div>
        <div class="col-2">
            <div class="form-group">
                <?php echo Html::a('添加', Url::to(['dispatch-script/add']), ['class' => 'btn btn-info']); ?>
            </div>
        </div>
    </div>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [
                'label'          => '序号',
                'attribute'      => 'id',
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
            [
                'label'          => '执行状态',
                'attribute'      => 'exec_status',
                'enableSorting'  => false,
                'value'          => function ($model) {
                    return ScriptTaskLog::$execStatusMap[$model->exec_status];
                },
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
            [
                'label'          => '执行开始时间',
                'attribute'      => 'exec_start_time',
                'value'          => function ($model) {
                    return empty($model->exec_start_time) ? '-' : date('Y-m-d H:i:s', $model->exec_start_time);
                },
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
            [
                'label'          => '执行结束时间',
                'attribute'      => 'exec_end_time',
                'value'          => function ($model) {
                    return empty($model->exec_end_time) ? '-' : date('Y-m-d H:i:s', $model->exec_end_time);
                },
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
            [
                'label'          => '创建时间',
                'attribute'      => 'created_at',
                'value'          => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                },
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
            [
                'label'          => '更新时间',
                'attribute'      => 'updated_at',
                'value'          => function ($model) {
                    return date('Y-m-d H:i:s', $model->updated_at);
                },
                'contentOptions' => ['style' => 'vertical-align: middle;'],
            ],
        ],

    ]); ?>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>