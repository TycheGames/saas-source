<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/7
 * Time: 17:02
 */

use yii\helpers\Url;
use yii\helpers\Html;
use callcenter\models\CollectorClassSchedule;
use callcenter\components\widgets\ActiveForm;

$this->shownav('manage', 'menu_user_collection_begin','menu_user_class_schedule');
$this->showsubmenu('', array(
    array('Daily Work Plan', Url::toRoute('user-collection/class-schedule'), 0),
    array('Absence Apply', Url::toRoute('user-collection/absence-apply'), 1),
    array('Preliminary review', Url::toRoute('user-collection/audit-apply'), 0),
    array('Final review', Url::toRoute('user-collection/finish-audit-apply'), 0),

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
<?php $form = ActiveForm::begin(['id' => 'absence-apply-form']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <td class="label">date：</td>
        <td ><?php echo $form->field($model, 'date')->textInput(['value'=>Html::encode(Yii::$app->getRequest()->get('date') ? Yii::$app->getRequest()->get('date'):date('Y-m-d')),'placeholder'=>'date']); ?></td>
    </tr>
    <tr>
        <td class="label">collector：</td>
        <td ><?php echo $form->field($model, 'collector_id')->dropDownList($teamMember,[]); ?>
        </td>
    </tr>
    <tr>
        <td class="label">type：</td>
        <td ><?php echo $form->field($model, 'type')->dropDownList(CollectorClassSchedule::$absence_type_map,[]); ?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton('submit',['class'=>'btn btn-primary', 'name'=>'submit_btn']);?>
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">back</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

