<?php
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\enum\Gender;
use yii\helpers\Html;
use common\models\risk\RiskBlackList;
use common\helpers\CommonHelper;

$this->shownav('user', 'menu__user');
$this->showsubmenu(Yii::T('common', 'User'), array(
   array('List', Url::toRoute('user/list'), 1),
));
?>
<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin([
    'method' => "get",
    'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],
]); ?>
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?= Yii::$app->request->get('id', ''); ?>" name="id" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?= Yii::$app->request->get('phone', ''); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'pan code') ?>：<input type="text" value="<?= Yii::$app->request->get('pan_code', ''); ?>" name="pan_code" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'name') ?>：<input type="text" value="<?= Yii::$app->request->get('name', ''); ?>" name="name" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'sourceId') ?>：<?php echo Html::dropDownList('source_id', Yii::$app->getRequest()->get('source_id'), $package_setting,['prompt' => 'all']); ?>

<?php echo Yii::T('common', 'Creation time') ?>：<input type="text" value="<?= Yii::$app->request->get('begintime', ''); ?>" name="begintime"
            onfocus="WdatePicker({lang:'en',startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Yii::$app->request->get('endtime', ''); ?>" name="endtime"
        onfocus="WdatePicker({lang:'en',startDcreated_atate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:00',alwaysUseStartDate:true,readOnly:true})" />
<?php echo Yii::T('common', 'Blacklist or not') ?>：<?= Html::dropDownList('black_status', Yii::$app->request->get('black_status', ''), \common\helpers\CommonHelper::getListT(RiskBlackList::$status_list), ['prompt' => Yii::T('common', 'All types')]); ?>&nbsp;
<input type="submit" name="search_submit" value="过滤" class="btn" />
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th><?php echo Yii::T('common', 'userId') ?></th>
        <th><?php echo Yii::T('common', 'name') ?></th>
        <th><?php echo Yii::T('common', 'aadhaarNumber') ?></th>
        <th>panCode</th>
        <th><?php echo Yii::T('common', 'phone') ?></th>
        <th><?php echo Yii::T('common', 'sourceId') ?></th>
        <th><?php echo Yii::T('common', 'birthday') ?></th>
        <th><?php echo Yii::T('common', 'gender') ?></th>
        <th><?php echo Yii::T('common', 'canLoan') ?></th>
        <th><?php echo Yii::T('common', 'Creation time') ?></th>
        <th><?php echo Yii::T('common', 'operation') ?></th>
    </tr>
    <?php foreach ($loan_person as $value): ?>
        <tr class="hover">
            <th><?= Html::encode(CommonHelper::idEncryption($value['id'], 'user')); ?></th>
            <th><?= Html::encode($value['name'] ? $value['name'] : '-'); ?></th>
            <th><?= Html::encode($value['aadhaar_number'] ?? '-'); ?></th>
            <th><?= Html::encode($value['pan_code'] ?? '-'); ?></th>
            <th><?= Html::encode($value['phone']); ?></th>
            <th><?= Html::encode($package_setting[$value['source_id']] ?? '-'); ?></th>
            <th><?= Html::encode($value['birthday']); ?></th>
            <th><?= Gender::$map[$value['gender']] ?? '-'; ?></th>
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
                    <a href="JavaScript:;" onclick="delBlackList(<?php echo '\'' . CommonHelper::idEncryption($value['id'], 'user') . '\'' ; ?>)"><?php echo Yii::T('common', 'Cancel blacklist') ?></a>|
                <?php else:?>
                    <a href="JavaScript:;" onclick="addBlackList(<?php echo '\'' . CommonHelper::idEncryption($value['id'], 'user') . '\'' ; ?>)"><?php echo Yii::T('common', 'add to blacklist') ?></a>|
                <?php endif;?>
                <a href="<?= Url::to(['user/user-view', 'id' => CommonHelper::idEncryption($value['id'], 'user'),'type' => $value['type']]); ?>"><?php echo Yii::T('common', 'detail') ?></a>|
                <a href="<?= Url::to(['user/can-loan-time-update', 'id' => CommonHelper::idEncryption($value['id'], 'user')]); ?>"><?php echo Yii::T('common', 'Reset borrowable time') ?></a>
                <?php if(isset($value['status']) && $value['status'] == 1):?>
                    |<a href="JavaScript:;" onclick="userDisable(<?= $value['id'];?>)"><?php echo Yii::T('common', 'User disable') ?></a>
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($loan_person)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No records') ?></div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $pages]); ?>

<script>
    function addBlackList(id){

        var name = prompt("<?php echo Yii::T('common', 'Must fill in notes') ?>");

        if (name != null && name != "") {
            var url = '<?= Url::to(["user/add-black-list"]);?>';
            var params = {id:id, mark: name};
            $.get(url,params,function(data){
                if(data.code == 0){
                    alert("<?php echo Yii::T('common', 'Add success') ?>");
                    window.location.reload(true);
                }else{
                    alert(data.message);
                }
            })
        } else if(name === "") {
            alert("<?php echo Yii::T('common', 'Must fill in notes') ?>");
        }
        return;
    }

    function delBlackList(id){
        if(!confirmMsg("<?php echo Yii::T('common', 'Confirm to cancel blacklist') ?>")){
            return false;
        }
        var url = '<?= Url::to(["user/del-black-list"]);?>';
        var params = {id:id};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert("<?php echo Yii::T('common', 'Cancel success') ?>");
                window.location.reload(true);
            }else{
                alert(data.message);
            }
        })
    }

    function userDisable(id){
        if(!confirmMsg("<?php echo Yii::T('common', 'Confirm logout and users will not be able to log in') ?>")){
            return false;
        }
        var url = '<?= Url::to(["user/user-disable"]);?>';
        var params = {id:id};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert('success');
                window.location.reload(true);
            }else{
                alert(data.message);
            }
        })
    }
</script>
