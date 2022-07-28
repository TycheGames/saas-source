<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/7/7
 * Time: 17:02
 */

use yii\helpers\Url;
use backend\models\AdminUser;
use backend\models\remind\ReminderClassSchedule;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->shownav('customer','menu_user_class_schedule');
$this->showsubmenu('', array(
    array('Daily Work plan', Url::toRoute('customer/class-schedule'), 1),
));

$week = date('w',time());
$hour = date('H',time());
?>
<style>
    .table th{
        border:1px solid darkgray;
        font-weight: bold;
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        font-weight: bold;
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
</style>
<style type="text/css">
    .hide{display:none;}
    .bold{font-weight:700;}
    .font-s1{font-size:14px;}
    .pop-master{position:fixed;top:0;left:0;width:100%;height:100%;background:#000;opacity:.4;}
    .pop-box{position:fixed;top:33%;left:23%;z-index:100;overflow:scroll;padding:2%;width:50%;height:30%;background:#fff;}
    .pop-box .page{padding-bottom:20px;text-align:center;}
    .pop-box .page span{text-decoration:underline;cursor:pointer;}
    .pop-box table th{background:#f5f5f5;}
    .pop-box table tr{border:1px solid #a9a9a9;}
    .pop-box table tr:hover td{background:#fcfcfc;color:#52a2ff;}
    .pop-box .task_btn button{margin:10px;padding:5px 20px;font-weight:700;cursor:pointer;}
    .pop-box .task_btn button:hover{color:#52a2ff;}
    .pop-box .task_btn button[disabled=disabled]{color:#999;}
    .pop-box .task_btn button[disabled=disabled]:hover{color:#999;cursor:default;}
    .tcursor{cursor:pointer;}
</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
startDate：<input type="text" value="<?php echo Html::encode($startDate); ?>" name="start_date"  onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})" style="width:100px;">&nbsp;
endDate：<input type="text" value="<?php echo Html::encode($endDate); ?>"  name="end_date" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})" style="width:100px;">&nbsp;
<?php if (!empty($merchantList)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', 0)), $merchantList); ?>&nbsp;
<?php endif; ?>
username：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
phone：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
group：<?php echo Html::dropDownList('group', Html::encode(Yii::$app->getRequest()->get('group', '')), $remindGroup,array('prompt' => '-all company-')); ?>&nbsp;
status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), AdminUser::$open_status_list,array('prompt' => '-all status-')); ?>&nbsp;
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;
<input type="submit" name="submitcsv" value="export csv" onclick="$(this).val('exportData');return true;" class="btn btn-success btn-xs" />
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th style="color: green">group</th>
        <th style="color: green">id</th>
        <th style="color: green">username</th>
        <th style="color: green">phone</th>
        <th style="color: green">NX_name</th>
        <th style="color: green">NX_password</th>
        <?php foreach ($dateArr as $date => $value):
            $time = strtotime($date);
            $isWeek = in_array(date('w',$time),[0,6]);
            $weekName = date('D',$time);
            $isToday = ($date == date('Y-m-d'));
            ?>

            <th <?php if($isToday):?>style="color: green"<?php else:?><?php if($isWeek):?>style="color: red"<?php endif; ?><?php endif; ?>><?php echo Html::encode($date)?><br><?php echo Html::encode($weekName)?></th>
        <?php endforeach;?>
    </tr>
    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <th <?php if($value['open_status'] == 0):?>style="color: red"<?php endif;?>><?php echo Html::encode($value['group_name'] ?? '--'); ?></th>
            <th <?php if($value['open_status'] == 0):?>style="color: red"<?php endif;?>><?php echo Html::encode($value['admin_user_id']); ?></th>
            <th <?php if($value['open_status'] == 0):?>style="color: red"<?php endif;?>><?php echo Html::encode($value['username']); ?></th>
            <th <?php if($value['open_status'] == 0):?>style="color: red"<?php endif;?>><?php echo Html::encode($value['phone']); ?></th>
            <th><?php echo Html::encode($value['nx_name_str'] ?? '-'); ?></th>
            <th><?php echo Html::encode($value['nx_password_str'] ?? '-'); ?></th>
            <?php foreach ($dateArr as $date => $val):?>
                <?php if(($value['open_status'] == 0 && date('Y-m-d',$value['updated_at']) <= $date) || date('Y-m-d',$value['created_at']) > $date):?>
                    <th>
                        <?php if(isset($val[$value['admin_user_id']])): ?>
                            <?php echo Html::encode((ReminderClassSchedule::$absence_type_map[$val[$value['admin_user_id']]['type']] ?? '-'));?>
                        <?php else:?>
                            --
                        <?php endif;?>
                    </th>
                <?php else:?>
                    <th
                        <?php if($date >= date('Y-m-d')): ?>
                            <?php if($isManager || (!$isManager && $date > date('Y-m-d') && $week == 2 && $hour >= 13 &&  $hour <= 18)):?>
                                class="tcursor" id="<?php echo Html::encode($date.'_'.$value['admin_user_id']);?>" onclick="preView(<?php echo Html::encode($value['admin_user_id']);?>,'<?php echo Html::encode($date);?>');"
                            <?php endif;?>
                        <?php endif;?>
                    >
                    <?php if(isset($val[$value['admin_user_id']])): ?>
                        <?php echo Html::encode((ReminderClassSchedule::$absence_type_map[$val[$value['admin_user_id']]['type']] ?? '-'));?>
                    <?php else:?>
                        √
                    <?php endif;?>
                    </th>
                <?php endif;?>
            <?php endforeach;?>
        </tr>
    <?php endforeach; ?>
</table>
<div class="pop-master hide" onclick="hidePop()"></div>
<div class="pop-box hide">
    <div class="page">
        <span class="font-s1 bold" onclick="hidePop()">close</span>
    </div>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>username</th>
            <th>date</th>
        </tr>
        <tr>
            <td id="_username">
                load..
            </td>
            <td id="_date">
                load..
            </td>
        </tr>
        <tr class="header">
            <th>is absence</th>
            <th>remark</th>
        </tr>
        <tr>
            <td id='absence_str'>
                load..
            </td>
            <td id='textarea_str'>
                load..
            </td>
        </tr>
    </table>
    <div style="text-align:center;margin-top: 20px"><button id="sub_btn" type="button" onclick="toConfirm(this)">submit</button></div>
</div>
<script type="text/javascript">
    var absence_type_map = <?php echo json_encode(ReminderClassSchedule::$absence_type_map)?>;
    var absence_type_today_after_map = <?php echo json_encode(ReminderClassSchedule::$absence_type_today_after_map)?>;
    var admin_id = 0;
    var work_date = '';
    var username = '';
    var remark = '';
    var type = '';
    var flag = 0;
    function preView(id,date){
        flag = 0;
        $('#_username').html('load..');
        $('#_date').html('load..');
        $('#absence_str').html('load..');
        $('#textarea_str').html('load..');
        $('#sub_btn').html('submit');
        $('.pop-master').show();
        $('.pop-box').show();
        $.ajax({
            url: '<?php echo Url::to(['customer/class-schedule-view']); ?>',
            data: {id: id,date:date},
            dataType: "json",
            success: function(res){
                if(res.code == 0){
                    admin_id = res.data['admin_id'];
                    username = res.data['username'];
                    work_date = res.data['date'];
                    remark = res.data['remark'];
                    type = res.data['type'];

                    $('#_username').html(username);
                    $('#_date').html(work_date);
                    var html_str = '';
                    if(type == 0){
                        html_str+= '<input type="radio" name="absence_status" value="0" checked="checked" onclick="hideSelect()">出勤inWork <input type="radio" name="absence_status" value="1" onclick="showSelect()">缺勤Absence';
                        html_str+= ' <select id="_absence" style="display: none">';
                    }else{
                        html_str+= '<input type="radio" name="absence_status" value="0" onclick="hideSelect()">出勤inWork <input type="radio" name="absence_status" value="1" checked="checked" onclick="showSelect()">缺勤Absence';
                        html_str+= ' <select id="_absence">';
                    }

                    if(res.data['is_today']){
                        $.each(absence_type_map,function(k,v){
                            if(type == k){
                                html_str += '<option value="'+k+'" selected>'+v+'</option>';
                            }else{
                                html_str += '<option value="'+k+'">'+v+'</option>';
                            }
                        });

                    }else{
                        $.each(absence_type_today_after_map,function(k,v){
                            if(type == k){
                                html_str += '<option value="'+k+'" selected>'+v+'</option>';
                            }else{
                                html_str += '<option value="'+k+'">'+v+'</option>';
                            }
                        });
                    }

                    html_str+= '</select>';
                    if(remark == null){
                        remark = '';
                    }
                    $('#absence_str').html(html_str);
                    $('#textarea_str').html('<textarea id="_remark" style="width: 98%">'+remark+'</textarea>');
                }else{
                    alert(res.message);
                }
                flag = 1;
            }
        });
    }


    function statusChange(){
        var is_absence = $('input[name="absence_status"]').filter(':checked').val();
        var a_type = $('#_absence').val();
        var a_remark = $('#_remark').val();
        $.ajax({
            url: '<?php echo Url::to(['customer/class-schedule-edit']); ?>',
            data: {id: admin_id,date: work_date,is_absence:is_absence,type:a_type,remark:a_remark},
            dataType: "json",
            success: function(res){
                console.log(res);
                if(res.code == 0){
                    var res_str = '';
                    if(res.data.is_absence == 1){
                        res_str += absence_type_map[res.data.type];
                    }else{
                        res_str += '√';
                    }
                    $('#'+work_date+'_'+admin_id).html(res_str);
                }
                flag = 1;
                hidePop();
                alert(res.message);
            }
        });
    }

    function toConfirm(obj){
        console.log(flag);
        if(flag == 0){
            return;
        }
        flag = 0;
        $(obj).html('Loading');
        statusChange();
    }


    function hidePop(){
        if(flag == 1){
            $('.pop-master').hide();
            $('.pop-box').hide();
        }
    }

    function hideSelect(){
        $('#_absence').hide();
    }
    function showSelect(){
        $('#_absence').show();
    }
</script>

