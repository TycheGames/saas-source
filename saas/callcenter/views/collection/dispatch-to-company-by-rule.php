<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\user\UserActiveTime;

$this->shownav('manage', 'menu_dispatch_to_company_by_rule');
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
        <th class="partition" colspan="15">auto dispatch to company by rule</th>
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
                <select name="active_type" onchange="onChangeMerchant($(this).val())">
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
            <select name="current_overdue_level">
                <?php foreach($levelArr as $l => $name):?>
                    <option value="<?=Html::encode($l);?>"><?php echo Html::encode($name);?></option>
                <?php endforeach;?>
            </select>
        </td>
    </tr>

    <tr>
        <td class="label">dispatch rules：</td>
        <td colspan="2" id="company">
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
    var arrMerchantIds = <?=json_encode($arrMerchantIds) ?>;
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
                    var count = parseInt($("#outside"+k).html())+v;
                    $("#outside"+k).html(count);
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
        });
        return false;
    });

    function onChangeMerchant()
    {
        var merchant_id = $('select[name=merchant_id]').val();
        var active_type = $('select[name=active_type]').val();
        console.log(merchant_id);
        $.post({
            url:"<?= Url::toRoute(['collection/dispatch-to-company-by-rule']) ?>",
            dataType:"json",
            data:{action:'update',merchant_id:merchant_id,active_type:active_type},
            success:function(res){
                $.each(res.levelCount,function (k,v) {
                    $("#level_total_"+k).html(v.totalCount);
                    $("#level_new_"+k).html(v.newCount);
                    $("#level_old_"+k).html(v.oldCount);
                });

                $('#company').html('');
                var trElement = '<table  class="tb tb2 fixpadding" style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;">' +
                    '<tr>';
                <?php if($isNotMerchantAdmin):?>
                trElement += '<th>Merchant</th>';
                <?php endif; ?>
                trElement += '<th>Company</th>' +
                    '<th>Completion times</th>' +
                    '<th>Dispatch count</th>' +
                    '<th>new or old</th>' +
                    '</tr>';
                if(res.companyList.length == 0){
                    trElement += '<tr>' +
                        '<th colspan="4" style="text-align: center;">no company</th>' +
                        '</tr>';
                }
                $.each(res.companyList,function(i,val){
                    trElement += '<tr>';
                    <?php if($isNotMerchantAdmin):?>
                    if(val.merchant_id == 0){
                        trElement +='<th>all merchant</th>'
                    }else{
                        trElement +='<th>' + arrMerchantIds[val.merchant_id] + '</th>'
                    }
                    <?php endif; ?>
                    trElement +=  '<th>' + val.real_title + '</th>' +
                        '<th id="outside'+ val.id +'">0</th>'+
                        '<th><input style="width: 50px" name="dispatch_count[' + val.id + ']" value=""></th>' +
                        '<th><select name="is_first['+ val.id +']"> ' +
                        '   <option value=0>random</option>' +
                        '   <option value=1>new</option>' +
                        '   <option value=2>old</option>' +
                        '</select></th>' +
                        '</tr>';
                });
                trElement+='</table>';
                $('#company').append(trElement);
            }
        });
    }
</script>