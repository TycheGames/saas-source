<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminUser;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;

$this->shownav('manage', 'menu_user_list');
$this->showsubmenu('', array(
    array('collector list', Url::toRoute('user-collection/user-list'), 1),
    array('add collector', Url::toRoute('user-collection/user-add'),0),
    array('team', Url::toRoute('user-collection/team'),0),
));

?>
    <style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    username：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php if ($setRealNameCollectionAdmin): ?>
    real name：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('real_name', '')); ?>" name="real_name" class="txt" style="width:120px;">&nbsp;
<?php endif; ?>
    phone：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
    group：<?php echo Html::dropDownList('group', Html::encode(Yii::$app->getRequest()->get('group', '')), LoanCollectionOrder::$level,array('prompt' => '-all group-')); ?>&nbsp;
    team ：<?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->getRequest()->get('group_game', '')), AdminUser::$group_games,array('prompt' => '-all team-')); ?>&nbsp;
<?php if($is_self):?>
    company：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', '')), $compamyList,array('prompt' => '-all company-')); ?>&nbsp;
<?php endif;?>
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
    status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), AdminUser::$open_status_list,array('prompt' => '-all status-')); ?>&nbsp;
    can dispatch：<?php echo Html::dropDownList('can_dispatch', Html::encode(Yii::$app->getRequest()->get('can_dispatch', '')), AdminUser::$can_dispatch_list,array('prompt' => '-all-')); ?>&nbsp;
<?php if($strategyOperating):?>
    search label：<?php echo Html::dropDownList('open_search_label', Html::encode(Yii::$app->getRequest()->get('open_search_label', '')), AdminUser::$can_search_label_map,array('prompt' => '-all status-')); ?>&nbsp;
    login app：<?php echo Html::dropDownList('login_app', Html::encode(Yii::$app->getRequest()->get('login_app', '')), AdminUser::$can_login_app_map,array('prompt' => '-all-')); ?>&nbsp;
    nx phone：<?php echo Html::dropDownList('nx_phone',Html::encode( Yii::$app->getRequest()->get('nx_phone', '')), AdminUser::$can_use_nx_phone_map,array('prompt' => '-all-')); ?>&nbsp;
<?php endif;?>
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;
<?php if($is_self):?>
    <input type="submit" name="submitcsv" value="export csv" onclick="$(this).val('export_direct');return true;" class="btn" />
    <input type="submit" name="exportcsv" value="Download the import template" onclick="$(this).val('export_tmp');return true;" class="btn" />
<?php endif;?>

<?php $form = ActiveForm::end(); ?>
<?php if($is_self):?>
    <?php $form = ActiveForm::begin(['id' => 'up', 'method' => "post", 'action'=>Url::to(['user-collection/batch-add']),'options' => ['enctype' => 'multipart/form-data']]); ?>
    <?php echo Html::fileInput('files'); ?><label style="color:red;">(Remarks: please import (CSV file) in the added format!)</label>
    <input type="submit" value="Importing"  class="btn" />&nbsp;
    <?php ActiveForm::end(); ?>
<?php endif;?>

    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>ID</th>
            <?php if (!empty($arrMerchant)): ?>
                <th>Merchant</th>
                <th>to view Merchant</th>
            <?php endif; ?>
            <?php if ($setRealNameCollectionAdmin): ?>
                <th>real name</th>
            <?php endif; ?>
            <th>username</th>
            <th>phone</th>
            <th>group</th>
            <th>team</th>
            <?php if($is_self):?>
                <th>company</th>
            <?php endif;?>
            <th>status</th>
            <th>can dispatch</th>
            <?php if($strategyOperating):?>
                <th>search label</th>
                <th>login app</th>
                <th>nx phone</th>
            <?php endif;?>
            <th>created time</th>
            <th>operation</th>
        </tr>
        <?php foreach ($users as $value): ?>
            <tr class="hover">
                <td><?php echo $value['id']; ?></td>
                <?php if (!empty($arrMerchant)): ?>
                    <th>
                        <?php echo Html::encode(!empty($arrMerchant[$value['merchant_id']]) ? $arrMerchant[$value['merchant_id']] : ''); ?>
                    </th>
                    <th>
                        <?php
                        if(!empty($value['to_view_merchant_id'])){
                            $arr = explode(',',$value['to_view_merchant_id']);
                            foreach ($arr as $mid){
                                echo Html::encode($arrMerchant[$mid] ?? '-');
                                echo '&nbsp;';
                            }
                        }; ?>
                    </th>
                <?php endif; ?>
                <?php if ($setRealNameCollectionAdmin): ?>
                    <th>
                        <?php echo Html::encode($value['real_name']); ?>
                    </th>
                <?php endif; ?>
                <th><?php echo Html::encode($value['username']); ?></th>
                <th><?php echo Html::encode($isHiddenPhone ? substr_replace($value['phone'],'*****',0,5) : $value['phone']); ?></th>
                <th><?php echo Html::encode(LoanCollectionOrder::$level[$value['group']] ?? '--'); ?></th>
                <th><?php echo Html::encode(AdminUser::$group_games[$value['group_game']] ?? '--'); ?></th>
                <?php if($is_self):?>
                    <th><?php echo Html::encode($value['real_title']); ?></th>
                <?php endif;?>
                <th><?php echo Html::encode(isset(AdminUser::$open_status_list[$value['open_status']]) ?AdminUser::$open_status_list[$value['open_status']]:"--" ); ?></th>
                <th><?php echo Html::encode(isset(AdminUser::$can_dispatch_list[$value['can_dispatch']]) ?AdminUser::$can_dispatch_list[$value['can_dispatch']]:"--" ); ?></th>
                <?php if($strategyOperating):?>
                    <th><?php echo Html::encode(isset(AdminUser::$can_search_label_map[$value['open_search_label']]) ?AdminUser::$can_search_label_map[$value['open_search_label']]:"--" ); ?></th>
                    <th><?php echo Html::encode(isset(AdminUser::$can_login_app_map[$value['login_app']]) ?AdminUser::$can_login_app_map[$value['login_app']]:"--" ); ?></th>
                    <th><?php echo Html::encode(isset(AdminUser::$can_use_nx_phone_map[$value['nx_phone']]) ?AdminUser::$can_use_nx_phone_map[$value['nx_phone']]:"--" ); ?></th>
                <?php endif;?>
                <th><?php echo date("Y-m-d H:i:s" , $value['created_at']); ?></th>
                <td>
                    <?php if(isset($value['can_dispatch']) && $value['can_dispatch'] == 1):?>
                        <a href="JavaScript:;" onclick="updatedisable(<?php echo $value['id'];?>)">stopDispatch</a>
                    <?php else:?>
                        <a style="color: red;" href="JavaScript:;" onclick="updateable(<?php echo $value['id'];?>)">openDispatch</a>
                    <?php endif;?>
                    <a href="<?php echo Url::to(['user-collection/edit', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')]); ?>">Edit</a>
                    <a href="<?php echo Url::to(['user-collection/change-pwd', 'id' => $value['id']]); ?>">ChangePwd</a>
                    <a style="color: red;" onclick="return confirm('Operation irreversible, confirm delete？');" href="<?php echo Url::to(['user-collection/del', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')]); ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>

<script>
    function updatedisable(id){
        if(!confirmMsg('Are you sure stop dispatch?')){
            return false;
        }
        var url = '<?php echo Url::to(["user-collection/update"]);?>';
        var params = {id:id,status:0};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert('success');
                window.location.reload(true);
            }else{
                alert(data.message);
            }
        })
    }
    function updateable(id){
        if(!confirmMsg('Are you sure open dispatch?')){
            return false;
        }
        var url = '<?php echo Url::to(["user-collection/update"]);?>';
        var params = {id:id,status:1};
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
