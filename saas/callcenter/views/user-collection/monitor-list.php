<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminUser;

$this->shownav('manage', 'menu_monitor_list');
$this->showsubmenu('', array(
    array('monitor list', Url::toRoute('user-collection/monitor-list'), 1),
    array('add monitor', Url::toRoute('user-collection/monitor-add'),0),
));

?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
username：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
phone：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
<?php if($is_self===false):?>
    company：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->getRequest()->get('outside', '')), UserCompany::outsideRealName($merchant_id),array('prompt' => '-all company-')); ?>&nbsp;
<?php endif;?>
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', '')), AdminUser::$open_status_list,array('prompt' => '-all status-')); ?>&nbsp;
<input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;
<?php $form = ActiveForm::end(); ?>
<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>ID</th>
        <?php if (!empty($arrMerchant)): ?>
            <th>Merchant</th>
            <th>to view Merchant</th>
        <?php endif; ?>
        <th>username</th>
        <th>phone</th>
        <th>company</th>
        <th>status</th>
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
            <th><?php echo Html::encode($value['username']); ?></th>
            <th><?php echo Html::encode($isHiddenPhone ? substr_replace($value['phone'],'*****',0,5) : $value['phone']); ?></th>
            <th><?php echo Html::encode($value['real_title']); ?></th>
            <th><?php echo Html::encode(isset(AdminUser::$open_status_list[$value['open_status']]) ?AdminUser::$open_status_list[$value['open_status']]:"--" ); ?></th>
            <th><?php echo Html::encode(date("Y-m-d H:i:s" , $value['created_at'])); ?></th>
            <td>
                <?php if (!empty($arrMerchant)): ?>
                    <?php if ($value['merchant_id'] == 0): ?>
                        <a href="<?php echo Url::to(['user-collection/monitor-edit', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')/*,'is_monitor'=>Yii::$app->getRequest()->get('is_monitor', '')*/]); ?>">Edit</a>
                        <a href="<?php echo Url::to(['user-collection/change-pwd', 'id' => $value['id'],]); ?>">ChangePwd</a>
                        <a style="color: red;" onclick="return confirm('Operation irreversible, confirm delete？');" href="<?php echo Url::to(['user-collection/monitor-del', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')/*,'is_monitor'=>Yii::$app->getRequest()->get('is_monitor', '')*/]); ?>">Delete</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo Url::to(['user-collection/monitor-edit', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')/*,'is_monitor'=>Yii::$app->getRequest()->get('is_monitor', '')*/]); ?>">Edit</a>
                    <a href="<?php echo Url::to(['user-collection/change-pwd', 'id' => $value['id'],]); ?>">ChangePwd</a>
                    <a style="color: red;" onclick="return confirm('Operation irreversible, confirm delete？');" href="<?php echo Url::to(['user-collection/monitor-del', 'id' => $value['id'],'page'=>$pages->page,'group'=>Yii::$app->getRequest()->get('group', ''),'outside'=>Yii::$app->getRequest()->get('outside', ''),'status'=>Yii::$app->getRequest()->get('status', '')/*,'is_monitor'=>Yii::$app->getRequest()->get('is_monitor', '')*/]); ?>">Delete</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>