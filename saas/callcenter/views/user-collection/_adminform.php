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
    <?php if($this->context->action->id == 'admin-add'):?>
    <tr>
        <td class="label">登陆密码：</td>
        <td ><?php echo $form->field($model, 'password')->textInput(['value'=>123456,'placeholder'=>'初始密码']); ?>
            <span id="pwd-original" style="color:blue;display: none;">(原手机号登录账户已存在，登录密码不变)</span>
        </td>
    </tr>
    <?php endif;?>
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