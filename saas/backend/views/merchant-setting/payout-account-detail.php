<?php
/**
 * User : Yinghao
 * Email: yhzs15155@gmail.com
 * Date : 2020-01-12
 * Time : 11:45
 */

use yii\helpers\Url;
use backend\components\widgets\ActiveForm;
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/admin.js?<?php echo time(); ?>; ?>" type="text/javascript"></script>


<?php $form = ActiveForm::begin(["id" => "account-add-form"]); ?>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'name'); ?>
    <?php echo $form->field($accountModel, 'name')->textInput(['readonly' => 'readonly']); ?>
</tr>
<?php foreach ($model->toArray() as $key => $item):?>
    <tr class="noborder">
        <?php echo $this->activeLabel($model, $key); ?>
        <?php echo $form->field($model, $key)->textInput(['readonly' => true]); ?>
    </tr>
<?php endforeach;?>
<tr class="noborder">
    <?php echo $this->activeLabel($accountModel, 'remark'); ?>
    <?php echo $form->field($accountModel, 'remark')->textarea(['readonly' => 'readonly']); ?>
</tr>

<?php ActiveForm::end(); ?>

<tr>
    <td colspan="15">
        <button><a href="<?php echo Url::toRoute('merchant-setting/payout-account-list'); ?>">Back</a></button>
    </td>
</tr>
