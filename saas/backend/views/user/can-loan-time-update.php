<?php
/**
 * Created by phpdesigner.
 * User: user
 * Date: 2016/12/06
 * Time: 18:14
 */
use backend\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'can-loan-time-update']); ?>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="15">reset borrow again time</th></tr>
    <tr>
        <td class="label" >can borrow in</td>
        <td>
            <?php echo Html::dropDownList('loan_date', Yii::$app->getRequest()->post('loan_date', 0), $can_loan_date); ?>days
        </td> 
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <input type="submit" value="submit" name="submit_btn" class="btn"/>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

