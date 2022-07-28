<?php

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use backend\components\widgets\LinkPager;
use yii\helpers\Html;
use common\helpers\CommonHelper;
use common\models\message\MessageTimeTask;
/**
 * @var backend\components\View $this
 */
$this->shownav('content', 'menu_message_time_task_list');
$this->showsubmenu(Yii::T('common', 'SMS &amp; voice timed task management'), [
   [Yii::T('common', 'task list'), Url::toRoute(['message-time-task/list', 'is_export' => $is_export]), 1],
   [Yii::T('common', 'add task'), Url::toRoute(['message-time-task/add', 'is_export' => $is_export]), 0],
]);

foreach ($package_setting as $pack_name)
{
    if($merchantId == 0)
    {
        $aisle_type = MessageTimeTask::getAisleType($pack_name,$merchantId);
    }
    else
    {
        $aisle_type = array();
        $aisle_type1[] = MessageTimeTask::getAisleType($pack_name,$merchantId);
        foreach($aisle_type1 as $k => $v)
        {
            foreach($v as $kk=>$vv)
            {
                $aisle_type[$kk] = $vv;
            }
        }
    }
}



?>
<style type="text/css">
    .hide{display:none;}
    .bold{font-weight:700;}
    .font-s1{font-size:14px;}
    .green{color:green;}
    .blue{color:#0474e7;}
    .pink{color:#f67e9c;}
    .purple{color:#905eff;}
    .yellow{color:#f0aa00;}
    .red{color:#cb013a;}
    .pop-master{position:fixed;top:0;left:0;width:100%;height:100%;background:#000;opacity:.4;}
    .pop-box{position:fixed;top:10%;left:10%;z-index:100;overflow:scroll;padding:2%;width:76%;height:76%;background:#fff;}
    .pop-box .page{padding-bottom:20px;text-align:center;}
    .pop-box .page span{text-decoration:underline;cursor:pointer;}
    .pop-box table th{background:#f5f5f5;}
    .pop-box table tr{border:1px solid #a9a9a9;}
    .pop-box table tr:hover td{background:#fcfcfc;color:#52a2ff;}
    .pop-box .task_btn button{margin:10px;padding:5px 20px;font-weight:700;cursor:pointer;}
    .pop-box .task_btn button:hover{color:#52a2ff;}
    .pop-box .task_btn button[disabled=disabled]{color:#999;}
    .pop-box .task_btn button[disabled=disabled]:hover{color:#999;cursor:default;}
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action' => ['message-time-task/list', 'is_export' => $is_export], 'options' => ['style' => 'margin-top:5px;']]); ?>&nbsp;
<?php echo Yii::T('common', 'Reminder type') ?>：<?php echo Html::dropDownList('tips_type', Html::encode(Yii::$app->getRequest()->get('tips_type', '')), CommonHelper::getListT(MessageTimeTask::$tips_type_map), array('prompt' => Yii::T('common', 'all')))?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'Point in time') ?>：<?php echo Html::dropDownList('task_time', Html::encode(Yii::$app->getRequest()->get('task_time', '')), MessageTimeTask::$task_time_map, array('prompt' => Yii::T('common', 'all')))?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'Task status') ?>：<?php echo Html::dropDownList('task_status', Html::encode(Yii::$app->getRequest()->get('task_status', '')), CommonHelper::getListT(MessageTimeTask::$task_status_map), array('prompt' => Yii::T('common', 'all')))?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo Yii::T('common', 'Service provider') ?>：<?php echo Html::dropDownList('provider', 0, $aisle_type); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>
<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th><?php echo Yii::T('common', 'Reminder type') ?></th>
            <th><?php echo Yii::T('common', 'Service provider') ?></th>
            <th><?php echo Yii::T('common', 'User type') ?></th>
            <th><?php echo Yii::T('common', 'Point in time') ?></th>
            <th><?php echo Yii::T('common', 'Task status') ?></th>
            <th>App<?php echo Yii::T('common', 'message') ?></th>
            <th><?php echo Yii::T('common', 'Remarks') ?></th>
            <th><?php echo Yii::T('common', 'Creation time') ?></th>
            <th><?php echo Yii::T('common', 'update time') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($list as $k => $value): ?>
            <tr class="hover">
                <td class="purple font-s1"><?php
                    if($value['tips_type'] == 4){
                        echo Html::encode($value['days_type'].'小时之内'.MessageTimeTask::$tips_type_map[$value['tips_type']]);
                    }
                    else{
                        echo Html::encode($value['days_type'] > 0 ? MessageTimeTask::$tips_type_map[$value['tips_type']].$value['days_type'].'天还款' : MessageTimeTask::$tips_type_map[$value['tips_type']].'还款');
                    }?></td>                <td><?php echo Html::encode($value['provider']); ?></td>
                <td class="green"><?php echo Html::encode(MessageTimeTask::$user_type_map[$value['user_type']]); ?></td>
                <td class="yellow font-s1"><?php echo Html::encode($value['task_time']); ?></td>
                <td class="red font-s1" id="task_<?php echo $value['id'];?>"><?php echo Html::encode(MessageTimeTask::$task_status_map[$value['task_status']]); ?></td>
                <td><?php echo Html::encode(MessageTimeTask::$is_app_notice_map[$value['is_app_notice']]); ?></td>
                <td><?php echo Html::encode($value['remark']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', $value['created_at']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', $value['updated_at']); ?></td>
                <td>
                    <a href="<?php echo Url::to(['message-time-task/edit', 'id' => $value['id'], 'is_export'=>$is_export]); ?>"><?php echo Yii::T('common', 'edit') ?></a>&nbsp;&nbsp;
                    <a onclick="preView(<?php echo $value['id'];?>);" href="javascript:void(0)"><?php echo Yii::T('common', 'preview') ?>/<?php echo Yii::T('common', 'Task operation') ?></a>
                </td>
        <?php endforeach; ?>
    </table>
    <?php if (empty($list)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<div class="pop-master hide" onclick="hidePop()"></div>
<div class="pop-box hide">
    <div class="page">
        <span class="font-s1 bold" onclick="hidePop()"><?php echo Yii::T('common', 'close preview') ?></span>
    </div>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th class="td23"><?php echo Yii::T('common', 'task') ?>ID</th>
            <th class="td23"><?php echo Yii::T('common', 'Reminder type') ?>/<?php echo Yii::T('common', 'days') ?></th>
            <th class="td23"><?php echo Yii::T('common', 'Task timing') ?></th>
            <th class="td23"><?php echo Yii::T('common', 'Task status') ?></th>
            <th class="td23"><?php echo Yii::T('common', 'Creation time') ?></th>
            <th class="td23"><?php echo Yii::T('common', 'update time') ?></th>
        </tr>
        <tr class="task_hover task_info">
            <td class="id bold"></td>
            <td class="tips_type purple font-s1"></td>
            <td class="task_time yellow font-s1"></td>
            <td class="task_status red font-s1"></td>
            <td class="created_at"></td>
            <td class="updated_at"></td>
        </tr>
        <tr class="header task_config">
            <th class=""><?php echo Yii::T('common', 'packName') ?></th>
            <th class="" colspan="2"><?php echo Yii::T('common', 'passage') ?></th>
            <th class="" colspan="1"><?php echo Yii::T('common', 'single') ?>/<?php echo Yii::T('common', 'batch') ?></th>
            <th class="" colspan="4"><?php echo Yii::T('common', 'Copywriter content') ?></th>
        </tr>
        <tr class="header">
            <th class="" colspan="2"><?php echo Yii::T('common', 'Task status update') ?></th>
            <th class="" colspan="6"><?php echo Yii::T('common', 'Remarks') ?></th>
        </tr>
        <tr class="task_hover task_remark">
            <td class="task_btn" colspan="2">
                <button class="btn_on" type="button" onclick="toConfirm(this,<?php echo MessageTimeTask::STATUS_ON;?>)"><?php echo Yii::T('common', 'open task') ?></button>
                <button class="btn_down" type="button" onclick="toConfirm(this,<?php echo MessageTimeTask::STATUS_DOWN;?>)"><?php echo Yii::T('common', 'close task') ?></button>
            </td>
            <td class="remark" colspan="6"></td>
        </tr>
        <tr class="header task_send">
            <th class=""><?php echo Yii::T('common', 'Total data') ?></th>
            <th class=""><?php echo Yii::T('common', 'Number of requests sent') ?></th>
            <th class=""><?php echo Yii::T('common', 'Send date') ?></th>
            <th class="" colspan="5"><?php echo Yii::T('common', 'Date of completion') ?></th>
        </tr>
        <tr class="header task_handle">
            <th class=""><?php echo Yii::T('common', 'Operator') ?></th>
            <th class=""><?php echo Yii::T('common', 'Operating behavior') ?></th>
            <th class="" colspan="6"><?php echo Yii::T('common', 'Operating time') ?></th>
        </tr>
    </table>
</div>
<script type="text/javascript">
    var task_id = 0;
    var aisle_type_df = '<?php echo MessageTimeTask::smsService_None;?>';
    function preView(id){
        $('.task_info td').html('');
        $('.task_remark .remark').html('');
        $('.task_config_str').remove();
        $('.task_handle_tr').remove();
        $('.task_send_str').remove();
        $('.task_btn button').removeAttr('disabled');
        $('.task_btn button.btn_on').html("<?php echo Yii::T('common', 'open task') ?>").attr('onclick',"toConfirm(this,<?php echo MessageTimeTask::STATUS_ON;?>)");
        $('.task_btn button.btn_down').html("<?php echo Yii::T('common', 'close task') ?>").attr('onclick',"toConfirm(this,<?php echo MessageTimeTask::STATUS_DOWN;?>)");

        $('.pop-master').show();
        $('.pop-box').show();
        $.ajax({
            url: '<?php echo Url::to(['message-time-task/pre-view']); ?>',
            data: {id: id},
            dataType: "json",
            success: function(res){
                task_id = res['id'];
                console.log(res);
                $('.task_info .id').html(res['id']);
                $('.task_info .tips_type').html(res['tips_type']);
                $('.task_info .task_time').html(res['task_time']);
                $('.task_info .task_status').html(res['task_status']);
                $('.task_info .created_at').html(res['created_at']);
                $('.task_info .updated_at').html(res['updated_at']);
                $('.task_remark .remark').html('<span class="pink">' + res['is_app_notice'] + '</span><br/><br/>' + res['remark']);

                if(res['task_status'] == '<?php echo MessageTimeTask::$task_status_map[MessageTimeTask::STATUS_ON];?>'){
                    $('.task_btn button.btn_on').attr('disabled','disabled');
                }else if(res['task_status'] == '<?php echo MessageTimeTask::$task_status_map[MessageTimeTask::STATUS_DOWN];?>'){
                    $('.task_btn button.btn_down').attr('disabled','disabled');
                }

                var config_str = '';
                var content = '--'

                $.each(res['config'],function(i,config){
                    if(config['aisle_type'] != aisle_type_df){
                        config_str += '<tr class="task_hover task_config_str">';
                        config_str += '<td class="bold">'+config['pack_name']+'</td>';
                        config_str += '<td colspan="2">'+config['aisle_name']+'</td>';
                        config_str += '<td colspan="1">'+config['batch_send_name']+'</td>';
                        content = config['content'] ? config['content'] : '--';
                        config_str += '<td colspan="4">'+content+'</td>';
                        config_str += '</tr>';
                    }
                });
                $('.task_config').after(config_str);

                var handle_str = '';
                $.each(res['handle_log'],function(i,handle){
                    handle_str += '<tr class="task_hover task_handle_tr">';
                    handle_str += '<td>'+handle['handler']+'</td>';
                    handle_str += '<td>'+handle['action']+'</td>';
                    handle_str += '<td colspan="6">'+handle['time']+'</td>';
                    handle_str += '</tr>';
                });
                $('.task_handle').after(handle_str);

                var send_str = '';
                $.each(res['send_log'],function(i,send){
                    send_str += '<tr class="task_hover task_send_str">';
                    send_str += '<td>'+send['total_num']+'</td>';
                    send_str += '<td>'+send['true_num']+'</td>';
                    send_str += '<td>'+send['start_time']+'</td>';
                    send_str += '<td colspan="5">'+send['time']+'</td>';
                    send_str += '</tr>';
                });
                $('.task_send').after(send_str);
            }
        });
    }

    function toConfirm(obj,task_status){
        var obj_html = $(obj).html();
        $(obj).html("<?php echo Yii::T('common', 'confirm') ?>"+obj_html).attr('onclick','statusChange('+task_status+')');
    }
    function statusChange(task_status){
        $.ajax({
            url: '<?php echo Url::to(['message-time-task/change-status']); ?>',
            data: {id: task_id,status: task_status},
            dataType: "json",
            success: function(res){
                $('#task_'+task_id).html(res['task_status']);
                $('.task_info .task_status').html(res['task_status']);
                $('.task_info .updated_at').html(res['updated_at']);
                $('.task_btn button').removeAttr('disabled');
                if(res['task_status'] == '<?php echo MessageTimeTask::$task_status_map[MessageTimeTask::STATUS_ON];?>'){
                    $('.task_btn button.btn_on').html("<?php echo Yii::T('common', 'open task') ?>").attr('disabled','disabled');
                }else if(res['task_status'] == '<?php echo MessageTimeTask::$task_status_map[MessageTimeTask::STATUS_DOWN];?>'){
                    $('.task_btn button.btn_down').html("<?php echo Yii::T('common', 'close task') ?>").attr('disabled','disabled');
                }

                $('.task_handle_tr').remove();
                var handle_str = '';
                $.each(res['handle_log'],function(i,handle){
                    handle_str += '<tr class="task_hover task_handle_tr">';
                    handle_str += '<td>'+handle['handler']+'</td>';
                    handle_str += '<td>'+handle['action']+'</td>';
                    handle_str += '<td colspan="6">'+handle['time']+'</td>';
                    handle_str += '</tr>';
                });
                $('.task_handle').after(handle_str);
            }
        });
    }

    function hidePop(){
        task_id = 0;
        $('.pop-master').hide();
        $('.pop-box').hide();
    }
</script>