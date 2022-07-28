<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use callcenter\models\AdminUser;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_user_list');
$this->showsubmenu('', array(
    array('collector list', Url::toRoute('user-collection/user-list'), 0),
    array('add collector', Url::toRoute('user-collection/user-add'),0),
    array('team', Url::toRoute('user-collection/team'),1),
));
?>

<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<table class="tb tb2 fixpadding">
    <!-- <tr><th class="partition" colspan="15">添加催收人信息</th></tr> -->
    <?php if($isSelf):?>
    <tr>
        <td class="label">Outside：</td>
        <td >   <?= Html::dropDownList(
                'outside',
                1,
                $outsides,
                [
                    'onchange' => 'onChange()'
                ]
            ); ?>
    </tr>
    <?php endif;?>
    <tr>
        <td class="label">Team：</td>
        <td >
            <table  class="tb tb2 fixpadding" style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
                <tr>
                    <th class="label">team ID</th>
                    <th >Alias</th>
                    <th colspan="5"></th>
                </tr>
                <?php foreach (AdminUser::$group_games as $key => $group_game):?>
                <tr>
                    <td ><?=Html::encode($group_game) ?>：</td>
                    <td ><?= Html::textInput('team_'.$key,'',['placeholder'=>'team alias']); ?></td>
                    <th colspan="5" id="tips_<?=$key?>"><?= Html::button('update',['class'=>'btn btn-primary', 'name'=>'btn_'.$key,'onClick' => 'onSubmit('.$key.',$(this))']);?></th>
                </tr>
                <?php endforeach;; ?>
            </table>
        </td>
    </tr>
</table>
<script>
    var group_games = <?=json_encode(AdminUser::$group_games); ?>;
    //方式切换
    onChange();
    function onChange()
    {
        $.each(group_games,function (k,v) {
            // console.log(v);
            $('input[name=team_'+k+']').val('');
        });
        var outside = $('select[name=outside]').val();
        $.post({
            url:"<?= Url::toRoute(['user-collection/team']) ?>",
            dataType:"json",
            data:{action:'update',outside:outside},
            success:function(res){
                $.each(res,function (k,v) {
                    // console.log(v);
                    $('input[name=team_'+k+']').val(v);
                })
            }
        });
    }

    function onSubmit(team,obj)
    {
        var val = $('input[name=team_'+team+']').val();
        if(val != '' ){
            var outside = $('select[name=outside]').val();
            obj.html('loading');
            $.post({
                url:"<?= Url::toRoute(['user-collection/team-js-edit']) ?>",
                dataType:"json",
                data:{outside:outside,team:team,alias:val},
                success:function(res){
                    if(res.code==0){
                        alert('success');
                        obj.html('update');
                    }else{
                        alert(res.message);
                    }
                }
            });
        }
    }
</script>
