<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:34
 */
use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use common\helpers\CommonHelper;
use common\services\FileStorageService;

$fileStorageService = new FileStorageService(false);
$this->shownav('manage','menu_collection_record_list');
?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'action'=>Url::to(['collection/collection-record-list']),'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/jquery-photo-gallery/jquery.photo.gallery.js"></script>
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>
ID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt" style="width:90px;">
OrderID：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('order_id', '')); ?>" name="order_id" class="txt" style="width:90px;">&nbsp;
<?php if($from):?>
Operator：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collection_name', '')); ?>" name="collection_name" class="txt" style="width:90px;" placeholder="">&nbsp;
<?php endif;?>
<?php if($strategyOperating):?>
    collector real name：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('cuishou_real_name', '')); ?>" placeholder="" name="cuishou_real_name" class="txt" style="width:80px;">&nbsp;
<?php endif;?>
<span class="s_item">collection time：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time','')); ?>" name="start_time" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:100px;">
to<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time','')); ?>"  name="end_time" onfocus="WdatePicker({startDcreated_atate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})" style="width:100px;">
<?php if($from):?>
<!--逾期等级：--><?php //echo Html::dropDownList('order_level', Yii::$app->getRequest()->get('order_level', 0), LoanCollectionOrder::$level,array('prompt' => '-所有等级-')); ?><!--&nbsp;-->
<?php endif;?>
Status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', 0)), LoanCollectionOrder::$status_list,array('prompt' => '-all status-')); ?>&nbsp;
Operation type：<?php echo Html::dropDownList('operate_tp', Html::encode(Yii::$app->getRequest()->get('operate_tp', 0)), LoanCollectionRecord::$label_operate_type,array('prompt' => '-all operate type-')); ?>&nbsp;
Connect status：<?php echo Html::dropDownList('is_connect', Html::encode(Yii::$app->getRequest()->get('is_connect', 0)), LoanCollectionRecord::$is_connect,array('prompt' => '-all connect status-')); ?>&nbsp;
Collection result：<?php echo Html::dropDownList('risk_control', Html::encode(Yii::$app->getRequest()->get('risk_control', 0)), LoanCollectionRecord::$risk_controls,array('prompt' => '-all result-')); ?>&nbsp;
<?php if($from):?>
    <span class="s_item">Company：</span><?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', 0)), $companyList,array('prompt' => '-all company-','onchange' => 'onOutsideChange($(this).val())')); ?>
<?php endif;?>
<?php echo Yii::T('common', 'Collector Group') ?>：<?=Html::dropDownList('loan_group',Html::encode(Yii::$app->getRequest()->get('loan_group', 0)),LoanCollectionOrder::$level,array('prompt' => Yii::T('common', 'All Group')));?>&nbsp;
<?php echo Yii::T('common', 'Grouping') ?>：<span id="team">
    <?php  echo \yii\helpers\Html::dropDownList('group_game', CommonHelper::HtmlEncodeToArray(Yii::$app->getRequest()->get('group_game', [])),
        $teamList,['class' => 'form-control team-select', 'multiple' => 'multiple']); ?>&nbsp;
</span>
<!--&nbsp;<input type="checkbox" name="is_summary" value="1"  --><?php //if(Yii::$app->request->get('is_summary', '0')==1):?><!-- checked --><?php //endif; ?><!-- > 显示汇总(勾选后，查询变慢)&nbsp;&nbsp;&nbsp;-->
<input type="submit" name="search_submit" value="search" class="btn">
<?php if($strategyOperating):?>
<input type="submit" name="submitcsv" value="exportcsv" onclick="$(this).val('exportData');return true;" class="btn">
<?php endif;?>
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>ID</th>
        <th>Order ID</th>
        <th>Borrower</th>
        <th>Contacts</th>
        <th>Relation</th>
        <th>Phone</th>
        <th>Order level</th>
        <th>Operation type</th>
        <th>Promise repay time</th>
        <th>Connect status</th>
        <th>Reminder result</th>
        <th>Remarks</th>
        <th>Status</th>
        <th>Operation time</th>
        <th>Operator</th>
        <?php if($strategyOperating):?>
            <th>Operator real name</th>
        <?php endif;?>
        <th>Operator Company</th>
        <th>User Amount</th>
        <th>User Utr</th>
        <th>User Pic</th>
    </tr>
    <?php foreach ($recordInfo['record'] as $value): ?>
        <?php  $content = $value['send_note'] ==0 ? '(send fail)' : '';?>
        <tr class="hover">
            <td><?php echo Html::encode($value['id']); ?></td>
            <td><?php echo Html::encode($value['order_id']); ?></td>
            <th><?php echo Html::encode($value['loan_name']); ?></th>
            <th><?php echo Html::encode($value['contact_name']); ?></th>
            <th><?php echo Html::encode($value['relation']); ?></th>
            <td><div style="width: 100px"><span class="phone_mask" onclick="showPhone($(this),<?=$value['id']?>)"><?= Html::encode(CommonHelper::strMask($value['contact_phone'],0,5,'*')); ?><img src="<?php echo $this->baseUrl; ?>/image/eye.png" width="12px" style="margin-left:5px;"></span><span style="display: none" id="phone_show_<?=$value['id']?>" class="phone_show"><?=Html::encode($value['contact_phone']); ?></span></div></td>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:"--"); ?></th>
            <th><?php echo Html::encode(isset(LoanCollectionRecord::$label_operate_type[$value['operate_type']])?LoanCollectionRecord::$label_operate_type[$value['operate_type']]:"--");  ?></th>
            <th><?php echo Html::encode(empty($value['promise_repayment_time']) ? '--' : date('Y-m-d H:i:s',$value['promise_repayment_time'])); ?></th>
            <th><?php echo Html::encode(!empty($value['is_connect'])?LoanCollectionRecord::$is_connect[$value['is_connect']]:"--");  ?></th>
            <th><?php echo Html::encode(!empty($value['risk_control']) && isset(LoanCollectionRecord::$risk_controls[$value['risk_control']]) ?LoanCollectionRecord::$risk_controls[$value['risk_control']]:"--");  ?></th>
            <th>
                <?php if(!empty($value['remark'])):?>
                    <?php echo Html::encode($value['remark']); ?>
                <?php else:?>
                    --
                <?php endif?>
            </th>
            <th><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$value['order_state']])?LoanCollectionOrder::$status_list[$value['order_state']]:"--");  ?></th>
            <th><?php echo Html::encode(!empty($value['operate_at'])?date("Y-m-d H:i:s",$value['operate_at']):"--"); ?></th>
            <th><?php echo Html::encode($value['username'] ?? "--");?></th>
            <?php if($strategyOperating):?>
                <th><?php echo Html::encode($value['real_name'] ?? "--");?></th>
            <?php endif;?>
            <th><?php echo Html::encode($value['real_title'] ?? "--");?></th>
            <th><?php echo  Html::encode($value['user_amount'] > 0 ? CommonHelper::CentsToUnit($value['user_amount']) : '--');?></th>
            <th><?php echo  Html::encode($value['user_utr'] ?? "--");?></th>
            <td class="gallerys" style="width: 150px">
                <?php if(!empty($value['user_pic'])): ?>
                    <?php foreach (json_decode($value['user_pic'],true) as $k => $info): ?>
                        <img class="gallery-pic" height="50" src="<?=$fileStorageService->getSignedUrl($info['url']); ?>"/>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($recordInfo['record'])): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $recordInfo['pages'],]); ?>
<script type="text/javascript">
    $('.gallery-pic').click(function(){
        $.openPhotoGallery(this);
    });
    $('select[name=page_size]').change(function(){
        var pages_size = $(this).val();
        $('#w0').append("<input type='hidden' name='page_size' value="+ pages_size+">");
        $('#w0').append('<input type="hidden" name="search_submit" value="search">');
        $('#w0').submit();
    });
    function onOutsideChange(outside){
        $.ajax({
            url:"<?= Url::toRoute(['user-collection/js-get-team']) ?>",
            type:"get",
            dataType:"json",
            data:{outside:outside},
            success:function(res){
                $.each(res,function(i,val){
                    $(".team-select option").eq(i-1).html(val);
                    $(".sumo_group_game .options label").eq(i-1).html(val);
                });
            }
        });
    }
    function showPhone(obj,id) {
        $('.phone_show').hide();
        $('.phone_mask').show();
        $('#phone_show_'+ id).show();
        obj.hide();
    }
</script>

