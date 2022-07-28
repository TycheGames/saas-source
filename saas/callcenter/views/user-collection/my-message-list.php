<?php

use yii\helpers\Html;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminMessage;

$this->shownav('manage', 'menu_my_message_list');
$this->showsubmenu('', array(
));

?>
    <style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
Receiving time：<input type="text" value="<?= Html::encode(Yii::$app->request->get('begintime', '')); ?>" name="begintime"
            onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />
to <input type="text" value="<?= Html::encode(Yii::$app->request->get('endtime', '')); ?>" name="endtime"
        onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />
Is new：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), AdminMessage::$status_map,array('prompt' => '-all-')); ?>&nbsp;

<input type="submit" name="search_submit" value="search" class="btn">
<?php $form = ActiveForm::end(); ?>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>Choose</th>
            <th>ID</th>
            <th>Content</th>
            <th>Receiving time</th>
            <th>Is new</th>
            <th></th>
        </tr>
        <?php foreach ($list as $value): ?>
            <tr class="hover">
                <td><input type="checkbox" name="ids[]" value="<?=Html::encode($value['id'])?>"></td>
                <td><?php echo Html::encode($value['id']); ?></td>
                <td><?php echo Html::encode($value['content']); ?></td>
                <td><?php echo Html::encode(date("Y-m-d H:i:s" , $value['created_at'])); ?></td>
                <td style="color: <?php if($value['status'] == AdminMessage::STATUS_NEW): ?>red<?php else:?>green<?php endif;?>"><?php echo Html::encode(AdminMessage::$status_map[$value['status']]); ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <label> &nbsp;<input type="checkbox" id="allchecked"><span>check all</span></label>
    <button id="read_button">Batch read</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (empty($list)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script>
    $("#allchecked").click(function(){
        if(this.checked){
            $("input[name^=ids]").each(function() {
                $(this).prop("checked", true);
            });
        }else{
            $("input[name^=ids]").each(function() {
                $(this).prop("checked", false);
            });
        }
    });
    $('#read_button').click(function(){
        var ids = [];
        $("input[name^=ids]").each(function() {
            if($(this).prop("checked")){
                ids.push($(this).val());
            }
        });
        if (ids.length == 0) {
            alert('Please check before operation！');
            return false;
        }
        if(!confirm('Are you sure you have read it ?')){
            return false;
        }
        if (ids.length > 0) {
            var url = "index.php?r=user-collection/message-batch-read&ids="+ids.join();
            window.location = url;
        };
        return false;
    });
</script>
