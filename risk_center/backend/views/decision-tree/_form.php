<?php
use backend\components\widgets\ActiveForm;
use yii\helpers\Html;

?>
<style type="text/css">
.item{ float: left; width: 180px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; position: relative;}
/*.desc_show{display: none;position: absolute;top:20px;left:25px;background-color: #fff;}*/

</style>
<?php $this->showtips('Tips', [Yii::T('common', 'For changes in administrators or roles, it is generally necessary for the corresponding administrators to re-login to take effect！')]); ?>

<?php $form = ActiveForm::begin(['id' => 'form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'name'); ?></td></tr>
        <tr class="noborder">
            <?php if ($this->context->action->id == 'version-add'): ?>
                <td class="vtop rowform"><?php echo $form->field($model, 'name')->textInput(['autocomplete' => 'off']); ?></td>
                <td class="vtop tips2"><?php echo Yii::T('common', 'It can only be letters, numbers or underscores. It can\'t be repeated. It can\'t be modified after adding') ?></td>
            <?php else: ?>
                <td colspan="2"><?php echo Html::encode($model->name); ?></td>
            <?php endif; ?>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'remark'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'remark')->textArea(); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="submit" value="submit" name="submit_btn" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
