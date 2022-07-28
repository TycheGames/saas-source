<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use common\models\user\UserActiveTime;

$this->shownav('manage', 'menu_dispatch_to_person_by_rule');
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
<?php $form = ActiveForm::begin(['id' => 'dispatch-order']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <th class="partition" colspan="15">auto dispatch to person by rule</th>
    </tr>
    <?php if($isNotMerchantAdmin):?>
        <tr>
            <td class="label">choose merchant：</td>
            <td>
                <select name="merchant_id" onchange="onChangeMerchant($(this).val())">
                    <?php foreach($arrMerchantIds as $l => $name):?>
                        <option value="<?=Html::encode($l);?>"><?php echo Html::encode($name);?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
    <?php endif;?>
    <?php if($strategyOperating):?>
        <tr>
            <td class="label">choose active type：</td>
            <td>
                <select name="active_type" onchange="onChange(1)">
                    <option value="0">all</option>
                    <?php foreach(UserActiveTime::$colorMap as $key => $value):?>
                        <option value="<?=Html::encode($key);?>"><?php echo Html::encode($value[1]);?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
    <?php endif;?>
    <tr>
        <td class="label">waiting dispatch info：</td>
        <td >
            <table class="tb tb2 fixpadding" style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">
                <tr>
                <?php foreach ($levelArr as $l => $level):?>
                    <th><?=Html::encode($level) ?></th>
                <?php endforeach;?>
                </tr>
                <tr>
                <?php foreach ($levelArr as $l => $level):?>
                    <th><span>total:</span><span id="level_total_<?=Html::encode($l)?>">loading</span></th>
                <?php endforeach;?>
                </tr>
                <tr>
                <?php foreach ($levelArr as $l => $level):?>
                    <th><span>new:</span><span id="level_new_<?=Html::encode($l)?>">loading</span></th>
                <?php endforeach;?>
                </tr>
                <tr>
                <?php foreach ($levelArr as $l => $level):?>
                    <th><span>old:</span><span id="level_old_<?=Html::encode($l)?>">loading</span></th>
                <?php endforeach;?>
                </tr>
            </table>

        </td>
    </tr>
    <tr>
        <td class="label">choose level：</td>
        <td>
            <select name="current_overdue_level" onchange="onChange(1)">
                <?php foreach($levelArr as $l => $name):?>
                    <option value="<?=Html::encode($l);?>"><?php echo Html::encode($name);?></option>
                <?php endforeach;?>
            </select>
        </td>
    </tr>
<!--    <tr>-->
<!--        <td class="label">choose small level：</td>-->
<!--        <td>-->
<!--            <select name="small_level_section">-->
<!--                <option value="[]">--ALL--</option>-->
<!--            </select>-->
<!--        </td>-->
<!--    </tr>-->
    <?php if($isManager):?>
    <tr>
        <td class="label">choose company：</td>
        <td>
            <select name="outside" id="company" onchange="onChange(2)">

            </select>
        </td>
    </tr>
    <?php endif;?>
    <tr>
        <!-- 指派 -->
        <td class="label" >choose small group</td>
        <td id="team">loading</td>
    </tr>
    <tr>
        <td class="label" >dispatch rules</td>
        <td colspan="2" id="colection">
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton('dispatch',['class'=>'btn btn-primary', 'name'=>'submit_btn' ,'submit']);?>
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">back</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    var companyList = <?=json_encode($companyList) ?>;
    var merchant_default_id = <?php reset($arrMerchantIds);echo key($arrMerchantIds) ?>;
    onChangeMerchant(merchant_default_id);

    $("#dispatch-order .btn").click(function(){
        if(!confirm('Are you sure to distribute orders according to this rule')){
            return false;
        }
        var targetUrl = $("#dispatch-order").attr("action");
        var data = $("#dispatch-order").serialize();
        $.ajax({
            type:'post',
            url:targetUrl,
            cache: false,
            data:data,  //重点必须为一个变量如：data
            dataType:'json',
            success:function(data){
                $.each(data.result,function (k,v) {
                    var count = parseInt($("#aui"+k).html())+v;
                    $("#aui"+k).html(count);
                })
                $.each(data.levelCount,function (k,v) {
                    $("#level_total_"+k).html(v.totalCount);
                    $("#level_new_"+k).html(v.newCount);
                    $("#level_old_"+k).html(v.oldCount);
                })
                alert(data.message);
            },
            error:function(){
                alert("请求失败")
            }
        })
        return false;
    });

    function onChange(type)
    {
        var current_overdue_level = $('select[name=current_overdue_level]').val();
        var outside = $('select[name=outside]').val();
        // var small_level_section = $('select[name=small_level_section]').val();
        // var small_html = '<option value="[]">--ALL--</option>';
        // if(small_level_overdue_days_section_json[current_overdue_level]){
        //     $.each(small_level_overdue_days_section_json[current_overdue_level],function (i,n) {
        //         // console.log(n);
        //         small_html += '<option value=' + n.section+ '>'+ n.name + '</option>';
        //     });
        // }
        // $('select[name=small_level_section]').html(small_html);
        if(type == 2){
            $("#team").html('loading');
            $.ajax({
                url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
                type:"get",
                dataType:"json",
                data:{outside:outside},
                success:function(res){
                    var htmlStr = '<select name="group_game" onchange="onChange(1)"><option value="0" selected="">--all small group--</option>';
                    $.each(res,function(i,val){
                        htmlStr += '<option value='+i+'>'+val+'</option>';
                    });

                    htmlStr+='</select>';
                    $("#team").html(htmlStr);
                }
            });
        }

        var group_game = $('select[name=group_game]').val();
        var merchant_id = $('select[name=merchant_id]').val();
        var active_type = $('select[name=active_type]').val();
        $.post({
            url:"<?= Url::toRoute(['collection/dispatch-to-person-by-rule']) ?>",
            dataType:"json",
            data:{action:'update',current_overdue_level:current_overdue_level,outside:outside,group_game:group_game,merchant_id:merchant_id,active_type:active_type},
            success:function(res){
                // console.log(res)
                $('#colection').html('');
                var trElement = '<table  class="tb tb2 fixpadding" style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">' +
                    '<tr>' +
                    '<th>Dispatch person</th>' +
                    '<th>Currently count</th>' +
                    '<th>Dispatch count</th>' +
                    '<th>new or old</th>' +
                    '<th>No Finish In Hand</th>' +
                    '</tr>';
                if(res.collectionList.length == 0){
                    trElement += '<tr>' +
                        '<th colspan="5" style="text-align: center;">no person</th>' +
                        '</tr>';
                }
                $.each(res.collectionList,function(i,val){
                    trElement += '<tr>' +
                        '<th>' + val.username + '</th>' +
                        '<th id = "aui' + val.id + '">0</th>' +
                        '<th><input style="width: 50px" name="dispatch_count[' + val.id + ']" value=""></th>' +
                        '<th><select name="is_first['+ val.id +']"> ' +
                        '   <option value=0>random</option>' +
                        '   <option value=1>new</option>' +
                        '   <option value=2>old</option>' +
                        '</select></th>' +
                        '<th>' + val.order_num + '</th>' +
                        '</tr>';
                });
                trElement+='</table>';
                $('#colection').append(trElement);
                $.each(res.levelCount,function (k,v) {
                    $("#level_total_"+k).html(v.totalCount);
                    $("#level_new_"+k).html(v.newCount);
                    $("#level_old_"+k).html(v.oldCount);
                })
            }
        });
    }

    function onChangeMerchant(merchant_id)
    {
        $('#company').html('');
        var trElement = '';
        var companyArr = {};
        if(companyList[merchant_id]){
            companyArr = companyList[merchant_id];
        }
        if(companyList[0] && merchant_id > 0){
            $.extend(companyArr,companyList[0]);
        }
        $.each(companyArr,function (k,v) {
            if(v.merchant_id == 0){
                trElement += '<option value="'+v.id+'">'+v.real_title+'(all merchant)</option>';
            }else{
                trElement += '<option value="'+v.id+'">'+v.real_title+'</option>';
            }

        });
        $('#company').append(trElement);
        onChange(2);
    }
</script>