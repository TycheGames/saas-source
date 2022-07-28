<?php

use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use Carbon\Carbon;
use common\models\coupon\UserRedPacketsSlow;
use common\models\question\QuestionList;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var array $models
 * @var QuestionList $model
 * @var Pagination $pages
 */
$this->shownav('system', 'menu_language_question_list');
$this->showsubmenu(Yii::T('common', 'Language certification'), [
    [Yii::T('common', 'Questions list'), Url::toRoute('question/question-list'), 1],
    [Yii::T('common', 'Questions add'), Url::toRoute('question/question-add'), 0],
]);
?>


<?php $form = ActiveForm::begin(['id' => 'searchform', 'action' => url::to(['question-list']), 'method' => 'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'title') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('title', '')); ?>" name="title"
              class="txt">&nbsp;
<?php echo Yii::T('common', 'status') ?>：<?php echo Html::dropDownList(
    'is_used',
    Html::encode(Yii::$app->getRequest()->get('is_used', '')),
    [1 => Yii::T('common', 'Enable'), 0 => Yii::T('common', 'Disable'),],
    ['prompt' => Yii::T('common', 'All types'),]); ?>&nbsp;
    <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>


    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th><?php echo Yii::T('common', 'Questions title') ?></th>
            <th><?php echo Yii::T('common', 'Is there a map?') ?></th>
            <th><?php echo Yii::T('common', 'Creation time') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
            <th><?php echo Yii::T('common', 'status') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($models as $model): ?>
            <tr class="hover">
                <td><?php echo Html::encode($model->question_title); ?></td>
                <td><?php echo Html::encode(empty($model->question_img) ? Yii::T('common', 'no') : Yii::T('common', 'yes')); ?></td>
                <td><?php echo Html::encode(Carbon::createFromTimestamp($model->created_at)->toDateTimeString()); ?></td>
                <td><?php echo Html::encode(Carbon::createFromTimestamp($model->updated_at)->toDateTimeString()); ?></td>
                <td><?php echo Html::encode($model->is_used == 1 ? Yii::T('common', 'Enable') : Yii::T('common', 'Disable')); ?></td>
                <td>
                    <a href="<?= Url::to(['question-edit', 'id' => $model->id]); ?>"><?php echo Yii::T('common', 'edit') ?></a>
                    <?php if ($model->is_used): ?>
                        <a onclick="return confirmMsg('Are you sure you want to disable it ?');"
                           href="<?php echo Url::to(['question/question-enable', 'id' => $model->id, 'is_used' => 0]); ?>"><?php echo Yii::T('common', 'Disable') ?></a>
                    <?php else: ?>
                        <a onclick="return confirmMsg('Are you sure you want to enable it ?');"
                           href="<?php echo Url::to(['question/question-enable', 'id' => $model->id, 'is_used' => 1]); ?>"><?php echo Yii::T('common', 'Enable') ?></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php if (empty($models)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>