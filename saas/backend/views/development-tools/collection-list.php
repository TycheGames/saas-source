<?php
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;

$this->shownav('user', 'menu__user');
$this->showsubmenu('用户管理', array(
   array('List', Url::toRoute('user/list'), 1),
));
?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin([
    'method' => "get",
    'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],
]); ?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />

<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>id</th>
        <th>操作人</th>
        <th>type</th>
        <th>text</th>
        <th>status</th>
        <th>createdTime</th>
        <th>操作</th>
    </tr>
    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td><?= Html::encode($value['id']); ?></td>
            <th><?= Html::encode($value['admin_user_id']); ?></th>
            <th><?= Html::encode(\common\models\CollectionTask::$old_type_map[$value['type']]); ?></th>
            <th><?= Html::encode($value['text'] );?></th>
            <th><?= Html::encode(\common\models\CollectionTask::$status_map[$value['status']]); ?></th>
            <th><?= Html::encode(date("Y-m-d H:i",$value['created_at'])); ?></th>
            <th>
                <?php if($value['status'] == \common\models\CollectionTask::STATUS_DEFAULT):?>
                    <a href="javascript:;" onclick="pass(<?= $value['id'];?>)">通过</a>
                    <a href="javascript:;" onclick="reject(<?= $value['id'];?>)">驳回</a>
                <?php endif;?>
            </th>
        </tr>
    <?php endforeach; ?>
</table>
<script>
    function pass(id) {
        if(!confirmMsg('是否通过')){
            return;
        }
        $.post('<?= Url::toRoute('development-tools/collection-pass');?>',{
            id : id,
            _csrf : '<?= Yii::$app->request->getCsrfToken();?>'
        },function (data){
            alert(data.msg);
            window.location.reload();
        });
    }

    function reject(id) {
        if(!confirmMsg('是否驳回')){
            return;
        }
        $.post('<?= Url::toRoute('development-tools/collection-reject');?>',{
            id : id,
            _csrf : '<?= Yii::$app->request->getCsrfToken();?>'
        },function (data){
            alert(data.msg);
            window.location.reload();
        });
    }
</script>

