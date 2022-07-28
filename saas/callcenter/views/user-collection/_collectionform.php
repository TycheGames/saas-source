<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use callcenter\models\loan_collection\LoanCollectionOrder;
use backend\models\Merchant;
use callcenter\models\AdminUser;

$groupTips = [];
foreach(LoanCollectionOrder::$current_level as $k => $v)
{
    $groupTips[] = "{$v}: overdue " . array_flip(LoanCollectionOrder::$reset_overdue_days)[$k] .' days';
}
$this->showtips('Group explain', $groupTips);

?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'user-add-form']); ?>
<table class="tb tb2 fixpadding">
    <!-- <tr><th class="partition" colspan="15">添加催收人信息</th></tr> -->
    <?php if($is_self): ?>
    <tr>
        <td class="label">Company：</td>
        <td ><?php echo $form->field($model, 'outside')->dropDownList($defaultCompanys,['onchange' => 'onOutsideChange($(this).val())']); ?></td>
    </tr>
    <?php endif;?>
    <tr>
        <td class="label">Group：</td>
        <td ><?php echo $form->field($model, 'group')->dropDownList(LoanCollectionorder::$current_level); ?></td>
    </tr>

    <tr>
        <td class="label">Team：</td>
        <td ><?php echo $form->field($model, 'group_game')->dropDownList(AdminUser::$group_games,['id' => 'team']); ?></td>
    </tr>
    <tr>
        <td class="label">Phone：</td>
        <td ><?php echo $form->field($model, 'phone')->textInput(['placeholder'=>'Used get OTP']); ?></td>
    </tr>
    <tr>
        <td class="label">Username：</td>
        <td ><?php echo $form->field($model, 'username')->textInput(['placeholder'=>'Username']); ?>
            <span>(Can only be letters, numbers, or underscores, cannot be repeated)</span>

        </td>
    </tr>
    <?php if($this->context->action->id == 'user-add'):?>
    <tr>
        <td class="label">Password：</td>
        <td ><?php echo $form->field($model, 'password')->textInput(['value'=>$password,'placeholder'=>'初始密码']); ?>
            <span id="pwd-original" style="color:blue;display: none;">(The original mobile number login account already exists, and the login password does not change)</span>
        </td>
    </tr>
    <?php endif;?>
    <?php if($isNotMerchantAdmin): ?>
        <tr>
            <td class="label">merchant：</td>
            <td>
                <?php echo $form->field($model, 'merchant_id')->dropDownList($arrMerchantIds,[
                    'onchange' => 'getOutsideList($(this).val())'
                ]); ?>
            </td>
        </tr>
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
    <?php if($strategyOperating): ?>
        <tr>
            <td class="label">real name：</td>
            <td ><?php echo $form->field($model, 'real_name')->textInput(['placeholder'=>'real name']); ?>
                <span>(Can only be letters, numbers, or underscores, cannot be repeated)</span>

            </td>
        </tr>
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
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton('submit',['class'=>'btn btn-primary', 'name'=>'submit_btn']);?>
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">back</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    var outsides = <?php echo json_encode($companys);?>;
    onOutsideChange(<?=$model->outside ?? array_key_first($defaultCompanys) ?>);
    function onOutsideChange(outside){
        var teamChild = $("#team >option");
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                // console.log(res);
                teamChild.each(function (i,v) {
                    $(v).text(res[$(v).val()]);
                });
            }
        });
    }
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

    //方式切换
    function getOutsideList(merchant_id)
    {
        var trElement = '';
        if(outsides[merchant_id]){
            var first_outside = '';
            $.each(outsides[merchant_id], function(key,val){
                if(first_outside == ''){
                    first_outside = key;
                }
                trElement += '<option value="'+ key +'">'+ val +'</option>';
            });
            //console.log(outsides[merchant_id]);
            onOutsideChange(first_outside)
        }
        $('#adminuser-outside').html(trElement);
    }
</script>