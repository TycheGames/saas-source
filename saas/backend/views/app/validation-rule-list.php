<?php

use backend\components\View;
use backend\components\widgets\ActiveForm;
use backend\components\widgets\LinkPager;
use common\models\enum\validation_rule\ValidationServiceProvider;
use common\models\enum\validation_rule\ValidationServiceType;
use common\models\third_data\ValidationRule;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View $this
 * @var array $models
 * @var ValidationRule $model
 * @var Pagination $pages
 */
$this->shownav('system', 'menu_validation_switch_rule');
$this->showsubmenu(Yii::T('common', 'Authentication service routing rules'), [
    [Yii::T('common', 'Rules List'), Url::toRoute('app/validation-switch-rule'), 1],
    [Yii::T('common', 'Add rules'), Url::toRoute('app/validation-switch-rule-add'), 0],
]);
?>
<?php $form = ActiveForm::begin(['id' => 'searchform', 'method' => 'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'Certification type') ?>：<?php echo Html::dropDownList('validation_type',
    Html::encode(Yii::$app->getRequest()->get('validation_type', '')),
    \common\helpers\CommonHelper::getListT(ValidationServiceType::$map),
    ['prompt' => Yii::T('common', 'All types')]); ?>&nbsp;
<?php echo Yii::T('common', 'Whether to enable') ?>：<?php echo Html::dropDownList('is_used',
    Html::encode(Yii::$app->getRequest()->get('is_used', '')),
    [0 => Yii::T('common', 'no'), 1 => Yii::T('common', 'yes')],
    ['prompt' => Yii::T('common', 'All types')]); ?>&nbsp;
    <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>

    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th><?php echo Yii::T('common', 'Certification type') ?></th>
            <th><?php echo Yii::T('common', 'Number of triggers') ?></th>
            <th><?php echo Yii::T('common', 'Trigger time (minutes)') ?></th>
            <th><?php echo Yii::T('common', 'Current service provider') ?></th>
            <th><?php echo Yii::T('common', 'Replace service provider') ?></th>
            <th><?php echo Yii::T('common', 'Whether to enable') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($models as $model): ?>
            <tr class="hover">
                <td><?php echo Yii::T('common', Html::encode(ValidationServiceType::$map[$model->validation_type])); ?></td>
                <td><?php echo Html::encode($model->service_error); ?></td>
                <td><?php echo Html::encode($model->service_time); ?></td>
                <td><?php echo Yii::T('common', Html::encode(ValidationServiceProvider::$map[$model->service_current])); ?></td>
                <td><?php echo Yii::T('common', Html::encode(ValidationServiceProvider::$map[$model->service_switch])); ?></td>
                <td><?php echo $model->is_used ? Yii::T('common', 'Enable') : Yii::T('common', 'Disable'); ?></td>
                <td class="td24">
                    <a href="<?php echo Url::to(['app/validation-switch-rule-edit', 'id' => $model->id]); ?>"><?php echo Yii::T('common', 'edit') ?></a>
                    <?php if ($model->is_used): ?>
                        <a onclick="return confirmMsg('Are you sure you want to disable it ?');"
                           href="<?php echo Url::to(['app/validation-switch-rule-used', 'id' => $model->id, 'is_used' => 0]); ?>"><?php echo Yii::T('common', 'Disable') ?></a>
                    <?php else: ?>
                        <a onclick="return confirmMsg('Are you sure you want to enable it ?');"
                           href="<?php echo Url::to(['app/validation-switch-rule-used', 'id' => $model->id, 'is_used' => 1]); ?>"><?php echo Yii::T('common', 'Enable') ?></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>