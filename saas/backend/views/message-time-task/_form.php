<?php
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use common\models\message\MessageTimeTask;
use common\helpers\CommonHelper;

$pack_names = $package_setting;

?>
<style type="text/css">
    .hover{height: 80px;}
    .show{color: green;border: 1px solid green;padding: 2px;}
    .bold{font-weight: bold;}
    .aisle_type select option{padding: 20px;}
</style>
<?php $this->showtips(Yii::T('common', 'Tips'), [
    '<span class="bold">文案自动替换：</span><span class="show">username - 用户名</span> &nbsp;，&nbsp; <span class="show">loan_money - 借款本金</span> &nbsp;，&nbsp; <span class="show">overdue_fee - 逾期费</span> &nbsp;，&nbsp; <span class="show">repay_money - 需还款金额</span> &nbsp;，&nbsp; <span class="show">card_no - 银行卡尾号</span> &nbsp;，&nbsp; <span class="show">repayment_date - 还款日 格式：1 September, 2019</span>&nbsp;，&nbsp; <span class="show">export_package_name - 导流包名（外部任务生效）</span>&nbsp;，&nbsp; <span class="show">show_product_name - 显示产品名（外部任务生效）</span>',
    Yii::T('common', 'Ordinary borrowing copy example: Dear username, the loan_money loan you applied for at source_name expires today, please open the app to repay; if it is not repaid, the platform will automatically debit from your card_no bank card Ensure funds are adequate to avoid overdue charges. If repaid, please ignore.'),
]); ?>
<?php $form = ActiveForm::begin(['id' => 'admin-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <?php if($this->context->action->id == 'add'){ ?>
                <th colspan="15" class="partition"><?php echo Yii::T('common', 'Basic config') ?></th>
            <?php }else{ ?>
                <th class="partition"><?php echo Yii::T('common', 'Basic config') ?></th>
                <th colspan="14" class="partition"><?php echo $this->activeLabel($model, 'id'); ?><?php echo $model->id;?></th>
            <?php } ?>
        </tr>
        <tr>
            <td class="td27"><?php echo $this->activeLabel($model, 'tips_type'); ?></td>
            <td class="rowform"><?php echo $form->field($model, 'tips_type')->dropDownList(CommonHelper::getListT(MessageTimeTask::$tips_type_map), ['prompt' => Yii::T('common', 'please choose')]); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr class="days_type">
            <td class="td27"><?php echo $this->activeLabel($model, 'user_type'); ?></td>
            <td class="rowform"><?php echo $form->field($model, 'user_type')->dropDownList(CommonHelper::getListT(MessageTimeTask::$user_type_map)); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr class="days_type">
            <td class="td27"><?php echo $this->activeLabel($model, 'days_type'); ?> (<?php echo Yii::T('common', 'If overdue, it can be configured: 1-10 means 1-10 days, 1,2,3 means 1,2,3 days. If not mentioned, fill in the number directly, and the unit is hour') ?>)</td>
            <td class="rowform"><?php echo $form->field($model, 'days_type')->textInput(); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr>
            <td class="td27"><?php echo $this->activeLabel($model, 'task_time'); ?></td>
            <td class="rowform"><?php echo $form->field($model, 'task_time')->dropDownList(CommonHelper::getListT(MessageTimeTask::$task_time_map), ['prompt' => Yii::T('common', 'please choose')]); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr>
            <td class="td27"><?php echo $this->activeLabel($model, 'is_app_notice'); ?></td>
            <td class="rowform"><?php echo $form->field($model, 'is_app_notice')->dropDownList(CommonHelper::getListT(MessageTimeTask::$is_app_notice_map)); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr>
            <th class="partition"><?php echo $this->activeLabel($model, 'config'); ?></th>
            <th class="partition"><?php echo Yii::T('common', 'Channel (can not be configured and will not send)') ?></th>
            <th class="partition" colspan="13"><?php echo Yii::T('common', 'Copywriting') ?></th>
        </tr>


        <?php foreach($pack_names as $pack_name){ ?>
            <?php if(!empty($pack_name)): ?>
                <tr class="hover">
                    <td class="td27 label"><?php echo $pack_name;?></td>
                    <?php $aisle_type = MessageTimeTask::getAisleType($pack_name,$merchantId);?>
                    <td class="aisle_type rowform">
                        <input type="hidden" name="MessageTimeTask[config][apps_<?php echo Html::encode($pack_name);?>][pack_name]" value="<?php echo Html::encode($pack_name);?>">
                        <?php echo Html::dropDownList('MessageTimeTask[config][apps_'.Html::encode($pack_name).'][aisle_type]', 0, $aisle_type, ['class' => 'aisle_apps_'.Html::encode($pack_name)]); ?>
                        <?php echo Html::radioList('MessageTimeTask[config][apps_'.Html::encode($pack_name).'][batch_send]', 0, MessageTimeTask::$is_batch_send_map, ['class' => 'batch_apps_'.Html::encode($pack_name)]); ?>
                    </td>
                    <td class="text_type rowform" colspan="13">
<!--                        --><?php //echo Html::textInput('MessageTimeTask[config][apps_'.$pack_name.'][text_type]', 0, ['class' => 'mbn mtm txt text_apps_'.$pack_name, 'placeholder' => '语音模板标识']); ?>
<!--                        <br/>-->
                        <?php echo Html::textArea('MessageTimeTask[config][apps_'.Html::encode($pack_name).'][content]', '', ['class' => 'mbm tarea content_apps_'.Html::encode($pack_name), 'placeholder' => Yii::T('common', 'SMS copywriter content'), 'style' => 'width: 400px;']); ?>
                    </td>
                </tr>
            <?php endif;?>
        <?php }?>
        <tr>
            <th colspan="15" class="partition"><?php echo $this->activeLabel($model, 'remark'); ?></th>
        </tr>
        <tr>
            <td colspan="2"><?php echo $form->field($model, 'remark')->textArea(); ?></td>
            <td colspan="13"></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="hidden" name="MessageTimeTask[is_export]" value="<?php echo Html::encode($is_export);?>">
                <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn">
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>
<script type="text/javascript">
    var action = "<?php echo $this->context->action->id; ?>";
    var task_config = <?php echo $model["config"] ? $model["config"] : "{}"; ?>;
    $(function(){
        console.log($('.aisle_type select option'));
        if(action == 'edit'){
            $.each(task_config,function(i,config){
                var aisle_select = ".aisle_"+i;
                $(aisle_select).find("option[value="+config['aisle_type']+"]").attr("selected",true);

                var batch_select = ".batch_"+i;
                $(batch_select).find("input[value="+config['batch_send']+"]").attr("checked",true);

                // var text_input = ".text_"+i;
                // $(text_input).val(config['text_type']);

                var content_area = ".content_"+i;
                $(content_area).val(config['content']);
            });
        }
    });

    $("#messagetimetask-tips_type").change(function(){
        if($(this).val() == <?php echo MessageTimeTask::TIPS_TODAY?>){
            $("#messagetimetask-days_type").val(0);
        }
    });
</script>