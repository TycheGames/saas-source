<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;

/**
 * @var backend\components\View $this
 */

$this->shownav('customer', 'menu_nx_phone_data');
$this->showsubmenu('客服牛信拨打记录', array(
    ['客服牛信拨打记录',Url::toRoute('customer/nx-phone-data'),1],
));
?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['customer/nx-phone-data']),'options' => ['style' => 'margin-top:5px;']]); ?>
客服：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
拨打电话：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<span class="s_item">日期：</span><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_time', date('Y-m-d',strtotime('- 7day')))) ; ?>" name="start_time" onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 00:00:00',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
至<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_time', date('Y-m-d'))); ?>"  name="end_time" onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd 23:59:59',alwaysUseStartDate:true,readOnly:true})" style="width:140px;">
<input type="submit" name="search_submit" value="过滤" class="btn">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th >客服账号</th>
            <th >牛信账号</th>
            <th >拨打电话</th>
            <th >通话时长</th>
            <th >录音地址</th>
            <th >开始时间</th>
            <th >接通时间</th>
            <th >结束时间</th>
            <th >挂断原因</th>
        </tr>
        <?php foreach ($data as $key => $value): ?>
            <tr class="hover">
                <td ><?php echo Html::encode($value['username']); ?></td>
                <td ><?php echo Html::encode($value['nx_name']); ?></td>
                <td ><?php echo Html::encode($value['phone']); ?></td>
                <td ><?php echo Html::encode($value['duration']); ?>(s)</td>
                <td ><audio src=<?php echo Html::encode($value['record_url']); ?> controls="controls">
                        Your browser does not support the audio element.
                    </audio></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['start_time'])); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['answer_time'])); ?></td>
                <td ><?php echo Html::encode(date('Y-m-d H:i:s',$value['end_time'])); ?></td>
                <td ><?php echo Html::encode($value['hangup_cause']); ?></td>

            </tr>
        <?php endforeach; ?>
    </table>
    <?php if (empty($data)): ?>
        <div class="no-result">no record</div>
    <?php endif; ?>
</form>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>


