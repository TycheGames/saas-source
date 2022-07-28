<?php

use backend\components\widgets\ActiveForm;
use common\services\FileStorageService;
use kartik\file\FileInput;

$fileStorageService = new FileStorageService(false);
?>

<?php $form = ActiveForm::begin(["id" => "validation-form"]); ?>
    <table class="tb tb2 fixpadding">
        <tr class="noborder">
            <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Questions title') ?></td>
            <td><?php echo $form->field($model, 'question_title')->textInput(); ?></td>
            <td><?php echo Yii::T('common', 'Description: Used for background list display, not displayed on the client') ?></td>
        </tr>
        <tr class="noborder">
            <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Questions content') ?></td>
            <td><?php echo $form->field($model, 'question_content')->textarea(); ?></td>
            <td><?php echo Yii::T('common', 'Description: The content of the problem displayed on the client') ?></td>
        </tr>
        <tr class="noborder">
            <td class="label"><?php echo Yii::T('common', 'Question picture (optional)') ?></td>
            <td><?php if (!empty($model->question_img)): ?>
                    <img class="gallery-pic" height="100"
                         src="<?= $fileStorageService->getSignedUrl($model->question_img); ?>"/>
                <?php endif; ?>
                <?php echo $form->field($model, 'question_img')->widget(FileInput::class, [
                    'options' => [
                        'accept'   => 'image/*',
                        'multiple' => false,
                    ],
                ]); ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr class="noborder">
            <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Question options') ?></td>
            <td><?php echo $form->field($model, 'question_option')->textarea(); ?></td>
            <td><?php echo Yii::T('common', 'Fill in the sample') ?>：[{"label":"A","val":"答案1"},{"label":"B","val":"答案2"}]</br><?php echo Yii::T('common', 'Verify URL') ?>：https://www.json.cn</td>
        </tr>
        <tr class="noborder">
            <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Answer') ?></td>
            <td><?php echo $form->field($model, 'answer')->textInput(); ?></td>
            <td><?php echo Yii::T('common', 'Sample') ?>：A</td>
        </tr>
        <tr class="noborder">
            <td class="label"><span class="highlight">*</span><?php echo Yii::T('common', 'Whether to enable') ?></td>
            <td><?php echo $form->field($model, 'is_used')->dropDownList([0 => Yii::T('common', 'Disable'), 1 => Yii::T('common', 'Enable')]); ?></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3">
                <input type="submit" value="submit" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>