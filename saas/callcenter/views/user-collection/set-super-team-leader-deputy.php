<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
$this->showsubmenu('', array(
    array('super team leader list ', Url::toRoute('user-collection/super-team-leader-list'), 0),
));
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'user-add-form']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <td class="label">Username：</td>
        <td ><?php echo $model->username; ?></td>
    </tr>
    <tr>
        <td class="label">deputy team leader：</td>
        <td>
            <?php echo $form->field($deputyModel, 'slave_admin_id')->dropDownList($teamLeaderList,['prompt' => '--NOT SET--']); ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton('submit',['class'=>'btn btn-primary', 'name'=>'submit_btn']);?>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    $(function(){
        $("[name=submit_btn]").click(function(){
            // console.log('hello');
            $(this).text('提交中。。。');
            $(this).css('display', 'none');

            // return false;
        });
    });
</script>