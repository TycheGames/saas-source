<?php
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\enum\Gender;
use common\helpers\CommonHelper;

$this->shownav('user', 'menu__user');
$this->showsubmenu(Yii::T('common', 'User list search'), array(
   array('List', Url::toRoute('customer/user-list'), 1),
));
?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin([
    'method' => "get",
    'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],
]); ?>
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Html::encode(Yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn" />
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'userId') ?></th>
        <th><?php echo Yii::T('common', 'name') ?></th>
        <th><?php echo Yii::T('common', 'aadhaarNumber') ?></th>
        <th>panCode</th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'birthday') ?></th>
        <th><?php echo Yii::T('common', 'gender') ?></th>
        <th><?php echo Yii::T('common', 'canLoan') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
    </tr>
    <?php foreach ($loan_person as $value): ?>
        <tr class="hover">
            <td><?= Html::encode(CommonHelper::idEncryption($value['id'], 'user')); ?></td>
            <th><?= Html::encode($value['name'] ? $value['name'] : '-'); ?></th>
            <th><?= Html::encode($value['aadhaar_number'] ?? '-'); ?></th>
            <th><?= Html::encode($value['pan_code'] ?? '-'); ?></th>
            <th><?= Html::encode($value['phone']); ?></th>
            <th><?= Html::encode($value['birthday']); ?></th>
            <th><?= Html::encode(Gender::$map[$value['gender']] ?? '-'); ?></th>
            <th>
                <?php if(empty($value['can_loan_time'])):?>
                    Ready to borrow
                <?php else:?>
                    <?=  date("Y-m-d H:i", $value['can_loan_time']); ?>
                <?php endif;?>
            </th>
            <th><?= date("Y-m-d H:i",$value['created_at']); ?></th>
            <td>
                <?php if(isset($value['black_status']) && $value['black_status'] == 1):?>
                    <a href="JavaScript:;" onclick="delBlackList(<?= '\'' . Html::encode(CommonHelper::idEncryption($value['id'], 'user')) . '\'';?>)"><?php echo Yii::T('common', 'Cancel blacklist') ?></a>
                <?php else:?>
                    <a href="JavaScript:;" onclick="addBlackList(<?= '\'' . Html::encode(CommonHelper::idEncryption($value['id'], 'user')) . '\'';?>)"><?php echo Yii::T('common', 'add to blacklist') ?></a>
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>


<script>
    function addBlackList(id){
        var name = prompt("remark！");

        if (name != null && name != "") {
            var url = '<?= Url::to(["user/add-black-list"]);?>';
            var params = {id:id, mark: name};
            $.get(url,params,function(data){
                if(data.code == 0){
                    alert('add success');
                    window.location.reload(true);
                }else{
                    alert('add fail');
                }
            })
        } else if(name === "") {
            alert("remark is required");
        }
        return;
    }

    function delBlackList(id){
        if(!confirmMsg('confirm to remove from blacklist')){
            return false;
        }
        var url = '<?= Url::to(["user/del-black-list"]);?>';
        var params = {id:id};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert('remove success');
                window.location.reload(true);
            }else{
                alert('remove fail');
            }
        })
    }
</script>
