<?php
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_visit_list');
$this->showsubmenu(Yii::T('common', 'Operation record'), array(
    array('List', Url::toRoute('admin-user/visit-list'), 1),
));
?>
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<style>
    th { text-align: center;  }
    td {text-align: center;}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'searchform',
    'method'=>'get',
    'options' => ['style' => 'margin-bottom:5px;'],
]); ?>
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('admin_user_id', '')); ?>" name="admin_user_id" style="width: 100px"/>&nbsp;
<?php echo Yii::T('common', 'username') ?> ：<input type="text" value="<?= Html::encode(Yii::$app->request->get('username', '')); ?>" name="username" class="txt" placeholder="Please enter keywords"/>&nbsp;
<?php echo Yii::T('common', 'Request URL') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('url', '')); ?>" name="url" class="txt" placeholder="Please enter keywords" />&nbsp;
<?php echo Yii::T('common', 'Request parameters') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('request_param', '')); ?>" name="request_param" class="txt" placeholder="Please enter keywords" />&nbsp;
<?php echo Yii::T('common', 'Access time') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('visit-start-time', '')); ?>" name="visit-start-time"
            onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(Yii::$app->request->get('visit-end-time', '')); ?>"  name="visit-end-time"
        onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />&nbsp;&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'userId') ?></th>
        <th><?php echo Yii::T('common', 'username') ?></th>
        <th><?php echo Yii::T('common', 'type') ?></th>
        <th><?php echo Yii::T('common', 'Request URL') ?></th>
        <th><?php echo Yii::T('common', 'Request parameters') ?></th>
        <th><?php echo Yii::T('common', 'Client IP') ?></th>
        <th><?php echo Yii::T('common', 'Request time') ?>/th>
    </tr>
    <?php foreach ($visitLogs as $value): ?>
        <tr class="hover">
            <td><?= Html::encode($value->admin_user_id); ?></td>
            <td><?= Html::encode($value->admin_user_name); ?></td>
            <td><?= Html::encode($value->request); ?></td>
            <td> <textarea style="width:400px;height:40px;background-color: white" readonly="readonly"><?php echo Html::encode($value->route); ?></textarea></td>
            <?php if($value->request_params != '[]'):?>
                <td> <textarea style="width:500px;height:60px;background-color: white" readonly="readonly"><?php echo Html::encode($value->request_params); ?></textarea></td>
            <?php else:?>
                <td class="params"><?= Html::encode($value->request_params); ?></td>
            <?php endif;?>
            <td><?= Html::encode($value->ip) ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s', $value->created_at)); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?= LinkPager::widget(['pagination' => $pages, 'firstPageLabel' => "first page ", 'lastPageLabel' => "last page"]); ?>
<?php if(!empty($visitLogs)):?>
    <script type="text/javascript">
        $('select[name=page_size]').change(function(){
            var pages_size = $(this).val();
            $('#searchform').append("<input type='hidden' name='page_size' value="+ pages_size+">");
            $('#searchform').append('<input type="hidden" name="search_submit" value="filter">');
            $('#searchform').submit();
        });
    </script>
<?php endif;?>
