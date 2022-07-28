<?php

use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use common\models\manual_credit\ManualCreditRules;

/**
 * @var backend\components\View $this
 */
$this->shownav('creditAudit', 'menu_manual_rules_list');
$this->showsubmenu(Yii::T('common', 'Rule Edit'), array(
    array(Yii::T('common', 'Show Rules'), Url::toRoute('credit-audit/manual-rules-list'), 1),
));
?>
    <style type="text/css">
        .item{ float: left; width: 400px; line-height: 25px; margin-left: 5px; border-right: 1px #deeffb dotted; }
    </style>
<script language="javascript" type="text/javascript">
    var type_info = <?php echo json_encode(\yii\helpers\ArrayHelper::htmlEncode($typeIds));?>;
    var current_type_id = <?php echo Html::encode($model->type_id ?? 0);?>;
</script>
<?php $form = ActiveForm::begin(['id' => 'manual-type-form']); ?>
    <table class="tb tb2">
        <tr><td class="td27" colspan="2"><?php echo Yii::T('common', 'Module') ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?= \yii\helpers\Html::dropDownList(
                    '',
                    $current_moudule_id,
                    array_merge(['0' => 'all module'],$moduleIds),
                    [
                        'onchange' => 'onChange($(this).val())'
                    ]
                ); ?></td>
            <td class="vtop tips2"></td>
        </tr>

        <tr><td class="td27" colspan="2">Type</td></tr>
        <tr class="noborder">
            <td class="vtop rowform">
                <select name="ManualCreditRules[type_id]" id ="type_id" >
                </select>
            </td>
            <td class="vtop tips2"></td>
        </tr>

        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'back_code'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'back_code')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'rule_name'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'rule_name')->textarea(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'type'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'type')->dropDownList(ManualCreditRules::$type_list,['prompt' => 'Select type']); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'questions'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop"><?php echo $form->field($model, 'questions')->textarea(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'pass_que_count'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop"><?php echo $form->field($model, 'pass_que_count')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'reject_text'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'reject_text')->textInput(); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr><td class="td27" colspan="2"><?php echo $this->activeLabel($model, 'status'); ?></td></tr>
        <tr class="noborder">
            <td class="vtop rowform"><?php echo $form->field($model, 'status')->dropDownList(\common\helpers\CommonHelper::getListT(ManualCreditRules::$status_list),['prompt' => Yii::T('common', 'Select status')]); ?></td>
            <td class="vtop tips2"></td>
        </tr>
        <tr>
            <td colspan="5">
                <input type="submit" value="submit" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    //方式切换
    onChange(<?=$current_moudule_id?>)
    function onChange(value)
    {
        var trElement = '';
        $.each(type_info, function(n,info){
            if(n == value){
                $.each(info, function(i,item){
                    if(item.id == current_type_id){
                        trElement += '<option selected value="'+ item.id +'">'+ item.type_name +'</option>';
                    }else{
                        trElement += '<option value="'+ item.id +'">'+ item.type_name +'</option>';
                    }
                    console.log(item);
                });
            }
        });
        if(trElement == ''){
            trElement = '<option value="">--</option>'
        }
        $('#type_id').html(trElement);
    }
</script>

