<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;
use common\services\customer_remind\CustomerRemindService;

$this->shownav('manage', 'menu_remind_dispatch');
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'dispatch-order']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <th class="partition" colspan="15"><?php echo Yii::T('common', 'auto dispatch to person by rule') ?></th>
    </tr>
    <?php if($merchantList):?>
        <tr>
            <td class="label">Merchant</td>
            <td><?= Html::dropDownList('merchant_id',$merchantId,$merchantList,['onchange'=>'updateRemind($(this).val())']) ;?></td>
        </tr>
    <?php endif;?>
    <tr>
        <td class="label"><?php echo Yii::T('common', 'wait dispatch count') ?>：</td>
        <td><?=$sumCount?></td>
    </tr>
    <?php foreach (CustomerRemindService::$before_day_map as $before_day => $item): ?>
        <tr>
            <td class="label">D-<?= Html::encode($before_day) ;?> ：</td>
            <td>
                <?php if($strategyOperating): ?>
                <input type="radio" name="dispatch_type" <?php if($before_day == 0): ?>checked<?php endif; ?> value="<?= Html::encode($before_day.'_0') ;?>">all(<?= Html::encode($count[$before_day][0] ?? 0) ;?>)

                    <?php foreach (CustomerRemindService::$user_type_map as $user_type => $val): ?>
                        <input type="radio" name="dispatch_type" value="<?= Html::encode($before_day.'_'.$user_type) ;?>"><?= Html::encode($val) ;?>(<?= Html::encode($count[$before_day][$user_type] ?? 0) ;?>)
                    <?php endforeach;?>
                <?php else:?>
                    <?= Html::encode($count[$before_day][0] ?? 0) ;?><input type="radio" name="dispatch_type" <?php if($before_day == 0): ?>checked<?php endif; ?> value="<?= Html::encode($before_day.'_0') ;?>">
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach;?>
    <tr>
        <td class="label" ><?php echo Yii::T('common', 'dispatch rules') ?></td>
        <td colspan="2" id="colection">
            <table  class="tb tb2 fixpadding" style="padding: 2px;margin-bottom: 1px;border:1px solid darkgray;width:600px">
                <tr>
                    <th><?php echo Yii::T('common', 'Group') ?></th>
                    <th><?php echo Yii::T('common', 'Dispatch person') ?></th>
                    <th><?php echo Yii::T('common', 'Dispatch count') ?></th>
                    <th><?php echo Yii::T('common', 'On reminder count') ?></th>
                    <th><?php echo Yii::T('common', 'operation') ?></th>
                </tr>
                <?php foreach ($adminList as $admin):?>
                    <tr>
                        <th> <?=Html::encode($remindGroup[$admin['remind_group']]['name'] ?? '-') ?> </th>
                        <th> <?=Html::encode($admin['username']) ?> </th>
                        <th><input style="width: 50px" name="dispatch_count[<?=Html::encode($admin['admin_user_id']) ?>]" value=""></th>
                        <th> <?=Html::encode($canRecycleData[$admin['admin_user_id']] ?? 0) ?> </th>
                        <th> <?= Html::button('recycle',['class'=>'recycle','onclick' => sprintf("onRecycle(%d, '%s', %d)", Html::encode($admin['admin_user_id']), Html::encode($admin['username']), Html::encode($canRecycleData[$admin['admin_user_id']] ?? 0))]);?> </th>
                    </tr>
                <?php endforeach;?>
            </table>
        </td>
    </tr>
    <tr>
        <td></td>
        <td colspan="15">
            <?= Html::submitButton(Yii::T('common', 'dispatch'),['class'=>'btn btn-primary', 'name'=>'submit_btn' ,'submit']);?>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>

<script type="text/javascript">
    function updateRemind(merchant_id){
        window.location.href = '<?=Url::to(['customer/remind-dispatch'])?>&merchant_id='+merchant_id;
    }
    $(".btn-primary").click(function(){
        if(confirm("<?php echo Yii::T('common', 'Are you sure to distribute orders according to this rule?') ?>")){
            var targetUrl = $("#dispatch-order").attr("action");
            var data = $("#dispatch-order").serialize();
            $.ajax({
                type:'post',
                url:targetUrl,
                cache: false,
                data:data,  //重点必须为一个变量如：data
                dataType:'json',
                success:function(data){
                    if(data.code == 0){
                        alert("<?php echo Yii::T('common', 'Dispatching completed, system is dispatching, please refresh the page later') ?>");
                    }else{
                        alert(data.message);
                    }
                },
                error:function(){
                    alert("<?php echo Yii::T('common', 'Request failed') ?>")
                }
            })
            return false;
        }else{
            return;
        }
    });

    function onRecycle(admin_id,name,default_count){
        var recycle_count = prompt("Please enter the number of orders recycle from "+name+" And sure",default_count);
        if(recycle_count){
            $.ajax({
                type:'post',
                url:'<?= Html::encode(Url::to(['customer/remind-recycle'])) ;?>',
                cache: false,
                data:{admin_id:admin_id,recycle_count:recycle_count},  //重点必须为一个变量如：data
                dataType:'json',
                success:function(data){
                    if(data.code == 0){
                        alert('Recycle Success'+data.count+'count');
                    }else{
                        alert(data.message);
                    }

                },
                error:function(){
                    alert("fail");
                }
            })
            return false;
        }else{
            return;
        };
    }
</script>