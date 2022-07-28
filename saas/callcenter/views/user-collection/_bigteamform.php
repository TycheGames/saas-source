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
    <?php if($isNotMerchantAdmin): ?>
        <tr>
            <td class="label"><?php echo $this->activeLabel($model, 'merchant_id'); ?></td>
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
    <?php if($is_self): ?>
    <tr>
        <td class="label">Company：</td>
        <td ><?php echo $form->field($model, 'outside')->dropDownList($defaultCompanyList,['onchange' => 'onOutsideChange($(this).val())']); ?></td>
    </tr>
    <?php endif;?>
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
    <?php if($strategyOperating):?>
        <tr>
            <td class="label">real name：</td>
            <td ><?php echo $form->field($model, 'real_name')->textInput(['placeholder'=>'real name']); ?>
                <span>(Can only be letters, numbers, or underscores, cannot be repeated)</span>

            </td>
        </tr>
        <tr>
            <td class="label">job number：</td>
            <td class="vtop rowform"><?php echo $form->field($model, 'job_number')->textInput(); ?></td>
        </tr>
    <?php endif; ?>
    <?php if($this->context->action->id == 'big-team-leader-add'):?>
    <tr>
        <td class="label">Password：</td>
        <td ><?php echo $form->field($model, 'password')->textInput(['value'=>$password,'placeholder'=>'初始密码']); ?>
            <span id="pwd-original" style="color:blue;display: none;">(The original mobile number login account already exists, and the login password does not change)</span>
        </td>
    </tr>
    <?php endif;?>
    <tr>
        <td class="label">To Manager team：</td>
        <td>
            <table id="default_relation">
            </table>
        </td>
    </tr>
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
    var labelArr = <?=json_encode($labelArr)?>;
    var levelArr = <?=json_encode(LoanCollectionOrder::$current_level)?>;
    var teamArr = <?=json_encode(AdminUser::$group_games)?>;
    var error = <?=json_encode($managerRelationModel->getErrors()); ?>;
    var label_id = 1;

    function onOutsideChange(outside){
        var teamChild = $(".team >option");
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                //console.log(res);
                teamChild.each(function (i,v) {
                    $(v).text(res[$(v).val()]);
                });
            }
        });
    }
    $(function(){
        var htmlStr = '';
        console.log(labelArr);
        if(labelArr.length != 0){
            $.each(labelArr,function(labelId,val){
                var groupOption ='';
                var teamOption ='';
                var help_block = '';
                var buttonHtmlStr = '';
                $.each(levelArr,function(group,v){
                    if(val.group == group){
                        groupOption += '<option value="'+group+'" selected>'+v+'</option>';
                    }else{
                        groupOption += '<option value="'+group+'">'+v+'</option>';
                    }
                });
                $.each(teamArr,function(group_game,v){
                    if(val.group_game == group_game){
                        teamOption += '<option value="'+group_game+'" selected>'+v+'</option>';
                    }else{
                        teamOption += '<option value="'+group_game+'">'+v+'</option>';
                    }
                });
                $.each(error,function(k,v){
                    if('group_game'+labelId == k) {
                        help_block = v[0];
                    }
                });

                if(labelId == 0){
                    buttonHtmlStr = '<button type="button" onclick="addLabel()">添加</button>';
                }else{
                    buttonHtmlStr = '<button type="button" onclick="delLabel('+labelId+')">删除</button>';
                }

                htmlStr += '<tr id="label_no_'+labelId+'">' +
                    '                    <td class="label">Group：</td>' +
                    '                    <td><div class="form-group field-adminmanagerrelation-group required">' +
                    '<select id="adminmanagerrelation-group'+labelId+'" class="" name="AdminManagerRelation[group][]">' +
                    groupOption  +
                    '</select>' +
                    '<div class="help-block"></div>' +
                    '</div></td>' +
                    '                    <td class="label">Team：</td>' +
                    '                    <td><div class="form-group field-adminmanagerrelation-group_game required">' +
                    '<select id="adminmanagerrelation-group_game'+labelId+'" class="team" name="AdminManagerRelation[group_game][]">' +
                    teamOption   +
                    '</select>' +
                    '<div class="help-block">'+ help_block +'</div>' +
                    '</div></td>' +
                    '                    <td class="label">'+ buttonHtmlStr +'</td>' +
                    '                </tr>'
            });
        }else{
            var groupOption ='';
            var teamOption ='';
            var help_block = '';
            $.each(levelArr,function(group,v){
                groupOption += '<option value="'+group+'">'+v+'</option>';
            });
            $.each(teamArr,function(group_game,v){
                teamOption += '<option value="'+group_game+'">'+v+'</option>';
            });

            htmlStr += '<tr>' +
                '                    <td class="label">Group：</td>' +
                '                    <td><div class="form-group field-adminmanagerrelation-group required">' +
                '<select id="adminmanagerrelation-group'+label_id+'" class="" name="AdminManagerRelation[group][]">' +
                groupOption  +
                '</select>' +
                '<div class="help-block"></div>' +
                '</div></td>' +
                '                    <td class="label">Team：</td>' +
                '                    <td><div class="form-group field-adminmanagerrelation-group_game required">' +
                '<select id="adminmanagerrelation-group_game'+label_id+'" class="team" name="AdminManagerRelation[group_game][]">' +
                teamOption   +
                '</select>' +
                '<div class="help-block">'+ help_block +'</div>' +
                '</div></td>' +
                '                    <td class="label"><button type="button" onclick="addLabel()">添加</button></td>' +
                '                </tr>'
        }

        $("#default_relation").html(htmlStr);

        onOutsideChange(<?=$model->outside ?>);

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

    function addLabel() {
        label_id++;
        var groupOption = '';
        var teamOption = '';
        $.each(levelArr,function(group,v){
            groupOption += '<option value="'+group+'">'+v+'</option>';
        });
        $.each(teamArr,function(group_game,v){
            teamOption += '<option value="'+group_game+'">'+v+'</option>';
        });
        var htmlStr = '<tr id="label_no_'+label_id+'">' +
            '                    <td class="label">Group：</td>' +
            '                    <td><div class="form-group field-adminmanagerrelation-group required">' +
            '<select id="adminmanagerrelation-group'+label_id+'" class="" name="AdminManagerRelation[group][]">' +
            groupOption +
            '</select>' +
            '<div class="help-block"></div>' +
            '</div></td>' +
            '                    <td class="label">Team：</td>' +
            '                    <td><div class="form-group field-adminmanagerrelation-group_game required">' +
            '<select id="adminmanagerrelation-group_game'+label_id+'" class="team" name="AdminManagerRelation[group_game][]">' +
            teamOption +
            '</select>' +
            '<div class="help-block"></div>' +
            '</div></td>' +
            '                    <td class="label"><button type="button" onclick="delLabel('+label_id+')">删除</button></td>' +
            '                </tr>'
        $("#default_relation").append(htmlStr);
        onOutsideChange(<?=$model->outside ?>);
    };

    function delLabel(label_id) {
        $('#label_no_'+label_id).remove();
    };

    var outsides = <?php echo json_encode($companyList);?>;
    //方式切换
    function getOutsideList(merchant_id)
    {
        var trElement = '<option value>none</option>';
        if(outsides[merchant_id]){
            $.each(outsides[merchant_id], function(key,val){
                trElement += '<option value="'+ key +'">'+ val +'</option>';
            });
            if(trElement == ''){
                trElement = '<option value="">--</option>'
            }
        }
        $('#adminuser-outside').html(trElement);
    }
</script>