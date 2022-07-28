<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\models\CollectionCheckinLog;

/**
 * @var callcenter\components\View $this
 */

$this->shownav('manage', 'menu_collector_attendance_day_data');
$this->showsubmenu(Yii::T('common', 'Collector Attendance'), array(
    [Yii::T('common', 'Collector Attendance'),Url::toRoute('collection-statistics/collector-attendance-day-data'),0],
    [Yii::T('common', 'Collector talk time talk times'),Url::toRoute('collection-statistics/collector-call-data'),0],
    [Yii::T('common', 'Collector punch'),Url::toRoute('collection-statistics/collector-punch-card-data'),1],
    [Yii::T('common', 'Collector NX Log'),Url::toRoute('collection-statistics/collector-nx-phone-data'),0],
));
?>
<?php $form = ActiveForm::begin(); ?>
<table class="tb tb2">
    <tr>
    <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'date'); ?></td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?=Html::encode($model->date) ?></td>
        <td class="vtop tips2"></td>
    </tr>

    <tr><td class="td27" colspan="2">username</td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?=Html::encode($name) ?></td>
        <td class="vtop tips2"></td>
    </tr>
    <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'address_type'); ?></td></tr>
    <tr class="noborder">
        <td class="vtop rowform"><?php echo $form->field($model, 'address_type')->dropDownList(CollectionCheckinLog::$address_type_map); ?></td>
        <td class="vtop tips2"></td>
    </tr>
    <tr>
        <td colspan="5">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>