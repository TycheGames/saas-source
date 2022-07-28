<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminUser;

?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'monitor-add-form']); ?>
<table class="tb tb2 fixpadding">
    <!-- <tr><th class="partition" colspan="15">添加催收人信息</th></tr> -->
    <tr>
        <td class="label"><?php echo Yii::T('common', 'agency') ?>：</td>
        <td ><?php echo $form->field($model, 'outside')->dropDownList($outsideRealName); ?></td>
    </tr>
    <tr>
        <td class="label">手机号：</td>
        <td ><?php echo $form->field($model, 'phone')->textInput(['placeholder'=>'用于接收验证码']); ?></td>
    </tr>
    <tr>
        <td class="label">登录名：</td>
        <td ><?php echo $form->field($model, 'username')->textInput(['placeholder'=>'建议使用姓名拼音']); ?>
            <span>(只能是字母、数字或下划线，不能重复，添加后不能修改)</span>

        </td>
    </tr>
    <?php if($this->context->action->id == 'monitor-add'):?>
    <tr>
        <td class="label">登陆密码：</td>
        <td ><?php echo $form->field($model, 'password')->textInput(['value'=>123456,'placeholder'=>'初始密码']); ?>
            <span id="pwd-original" style="color:blue;display: none;">(原手机号登录账户已存在，登录密码不变)</span>
        </td>
    </tr>
    <?php endif;?>
    <?php if($strategyOperating): ?>
        <tr>
            <td class="label"><?php echo $this->activeLabel($model, 'open_search_label')?></td>
            <td ><?php echo $form->field($model, 'open_search_label')->dropDownList(AdminUser::$can_search_label_map); ?></td>
        </tr>
        <tr>
            <td class="label"><?php echo $this->activeLabel($model, 'login_app')?></td>
            <td ><?php echo $form->field($model, 'login_app')->dropDownList(AdminUser::$can_login_app_map); ?></td>
        </tr>
        <tr>
            <td class="label"><?php echo $this->activeLabel($model, 'nx_phone')?></td>
            <td ><?php echo $form->field($model, 'nx_phone')->dropDownList(AdminUser::$can_use_nx_phone_map); ?></td>
        </tr>
        <tr>
            <td class="label"><?php echo $this->activeLabel($model, 'job_number'); ?></td>
            <td><?php echo $form->field($model, 'job_number')->textInput(); ?></td>
        </tr>
    <?php endif;?>
    <?php if($isNotMerchantAdmin): ?>
        <tr>
            <td class="label">To view merchant：</td>
            <td>
                <?php if (!empty($arrMerchantIds)) : ?>
                    <?php foreach ($arrMerchantIds as $k => $v): ?>

                        <label class="txt"><input type="checkbox" class="checkbox" value="<?php echo Html::encode($k);?>" name="to_view_merchant_id[]" <?php if(in_array($k,explode(",", $model->to_view_merchant_id))){echo "checked";}?>>
                            <?php echo Html::encode($v); ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton('提交',['class'=>'btn btn-primary', 'name'=>'submit_btn']);?>
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
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
        $("[id=loancollection-phone]").blur(function(){
            $.ajax({
                url:"<?= Url::toRoute(['admin-user/phone-ajax']) ?>",
                type:"get",
                dataType:"json",
                data:{phone:$(this).val()},
                success:function(res){
                    if(res != ''){
                        console.log('yes');
                        $("[id=loancollection-username]").val(res.username).attr("readonly","readonly");
                        $("[id=loancollection-password]").css('display','none');
                        $("[id=pwd-original]").css('display','block');
                    }else{
                        $("[id=loancollection-username]").attr("readonly",false);
                        $("[id=loancollection-password]").css('display','block');
                        $("[id=pwd-original]").css('display','none');
                    }
                },
                error:function(res){
                    alert('ajax error'+res);
                }
            });
        });
    });
</script>