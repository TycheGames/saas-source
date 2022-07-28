<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/8/3
 * Time: 10:10
 */
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionRecord;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_operate_list');
$this->showsubmenu('后台访问记录', array(
    array('列表', Url::toRoute('admin-user/collection-operate-list'), 1),
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
    'action'=>Url::to(['admin-user/collection-operate-list']),
    'options' => ['style' => 'margin-bottom:5px;'],
]); ?>
用户ID：<input type="text" value="<?= Html::encode(Yii::$app->request->get('admin_user_id', '')); ?>" name="admin_user_id" style="width: 100px"/>&nbsp;
用户名：<input type="text" value="<?= Html::encode(Yii::$app->request->get('username', '')); ?>" name="username" class="txt" placeholder="请输入关键字"/>&nbsp;
访问URL：<input type="text" value="<?= Html::encode(Yii::$app->request->get('url', '')); ?>" name="url" class="txt" placeholder="请输入关键字" />&nbsp;
请求参数：<input type="text" value="<?= Html::encode(Yii::$app->request->get('request_param', '')); ?>" name="request_param" class="txt" placeholder="请输入关键字" />&nbsp;
访问时间：<input type="text" value="<?= Html::encode(Yii::$app->request->get('visit_start_time', '')); ?>" name="visit_start_time"
            onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(Yii::$app->request->get('visit_end_time', '')); ?>"  name="visit_end_time"
        onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />&nbsp;&nbsp;
<input type="submit" name="search_submit" value="筛选" class="btn" />
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>序号</th>
        <th>用户ID</th>
        <th>用户名</th>
        <th>姓名</th>
        <th>所属公司</th>
        <th>类型</th>
        <th>请求URL</th>
        <th>请求参数</th>
        <th>客户端IP</th>
        <th>请求时间</th>
    </tr>
    <?php foreach ($visitLogs as $value): ?>
        <tr class="hover">
            <td class="td25" ><?= Html::encode($value->id); ?></td>
            <td><?= Html::encode($value->admin_user_id); ?></td>
            <td><?= Html::encode($value->admin_user_name); ?></td>
            <td><?= isset($collection_list[$value->admin_user_id]['real_name'])?Html::encode($collection_list[$value->admin_user_id]['real_name']):'--'; ?></td>
            <td><?= isset($collection_list[$value->admin_user_id]['outside']) && isset($outside_list[$collection_list[$value->admin_user_id]['outside']]) ?Html::encode($outside_list[$collection_list[$value->admin_user_id]['outside']]):'--'; ?></td>
            <td><?= Html::encode($value->request); ?></td>
            <td> <textarea style="width:300px;height:30px;background-color: white" readonly="readonly"><?php echo Html::encode($value->route); ?></textarea></td>
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
<?= LinkPager::widget(['pagination' => $pages, 'firstPageLabel' => "首页", 'lastPageLabel' => "尾页"]); ?>
<?php if(!empty($visitLogs)):?>
    <div style="color:#428bca;font-size: 14px;font-weight:bold;" >每页&nbsp;<?php echo Html::dropDownList('page_size', Yii::$app->getRequest()->get('page_size', 15), LoanCollectionRecord::$page_size); ?>&nbsp;条</div>
    <script type="text/javascript">
        $('select[name=page_size]').change(function(){
            var pages_size = $(this).val();
            $('#searchform').append("<input type='hidden' name='page_size' value="+ pages_size+">");
            $('#searchform').append('<input type="hidden" name="search_submit" value="筛选">');
            $('#searchform').submit();
        });
    </script>
<?php endif;?>
