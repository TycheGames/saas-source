<?php

use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminMessageTask;

$levels = LoanCollectionOrder::$current_level;
?>
<style type="text/css">
    .show{color: green;border: 1px solid green;padding: 2px;}
    .bold{font-weight: bold;}
    .aisle_type select option{padding: 20px;}
</style>

<?php $form = ActiveForm::begin(["id" => "team-message-task-form"]); ?>
<table class="tb tb2 fixpadding">
    <?php if(isset($model->id)): ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>template id</td>
        <td><?php echo $model->id; ?></td>
    </tr>
    <?php endif; ?>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>company</td>
        <?php if($this->context->action->id == 'team-message-task-add'): ?>
        <td><?php echo $form->field($model, 'outside')->dropDownList($companys); ?></td>
        <?php else:?>
        <td><?php echo $companys[$model->outside] ?? '-'; ?></td>
        <?php endif; ?>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>group</td>
        <?php if($this->context->action->id == 'team-message-task-add'): ?>
        <td><?php echo $form->field($model, 'group')->dropDownList($levels,['onchange'=>'updateGroup($(this).val())']); ?></td>
        <?php else:?>
            <td><?php echo $levels[$model->group] ?? '-'; ?></td>
        <?php endif; ?>
    </tr>

    <tr class="noborder task_type_c">
        <td class="label"><span class="highlight">*</span>task type</td>
        <?php if($this->context->action->id == 'team-message-task-add'): ?>
            <td><?php echo $form->field($model, 'task_type')->dropDownList($taskTypes); ?></td>

        <?php else:?>
            <td><?php echo $taskTypes[$model->task_type] ?? '-'; ?></td>
        <?php endif; ?>

    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>task value(Rp or %)</td>
        <td><?php echo $form->field($model, 'task_value')->textInput(['style' => 'width:300px']); ?></td>
    </tr>
    <tr class="noborder">
        <td class="label"><span class="highlight">*</span>status</td>
        <td><?php echo $form->field($model, 'status')->dropDownList(AdminMessageTask::$status_map); ?></td>
    </tr>
    <tr>
        <td colspan="2">
            <input type="submit" value="submit" class="btn">
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
<script type="text/javascript">
    function updateGroup(group){
        if(group == 2){
            $('.task_type_c').show();
        }else{
            $('.task_type_c').hide();
        }

    }
</script>
