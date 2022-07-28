<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var backend\components\View $this
 */
$this->shownav('manage', 'menu_collection_stop_list');
$this->showsubmenu('停催列表', array(
    array('停催列表', Url::toRoute('collection/collection-stop-list'), 0),
    array('添加停催订单', Url::toRoute('collection/collection-stop-add'), 1)
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js?<?php echo time(); ?>"></script>
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr>
            <td class="td24">loan order Id：</td>
            <td colspan="15">
                <input name="order_id" value="">
            </td>
        </tr>
        <tr>
            <td class="td24">is set input date</td>
            <td colspan="15">
                <?php echo Html::dropDownList('is_set_input_date',0, [0 => 'not set',1 => 'set'],[
                    'onchange' => 'onSetChange($(this).val())'
                ]); ?>
            </td>
        </tr>
        <tr id="input_date_tr" style="display: none">
            <td class="td24">next input date</td>
            <td colspan="15">
                <input type="text" value="" name="input_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">
            </td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="submit" value="提交" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<script>
    function onSetChange(is_set) {
        if(is_set == 1){
            $("#input_date_tr").show();
        }else{
            $("#input_date_tr").hide();
        }
    }
</script>
