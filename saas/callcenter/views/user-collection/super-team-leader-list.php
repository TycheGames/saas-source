<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminUser;
use callcenter\models\loan_collection\LoanCollectionOrder;

$this->shownav('manage', 'menu_super_team_leader_list');
$this->showsubmenu('', array(
    array('super team leader list', Url::toRoute('user-collection/super-team-leader-list'), 1),
    array('add super team leader', Url::toRoute('user-collection/super-team-leader-add'),0),
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
<?php if (!empty($arrMerchant)): ?>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;
<?php endif; ?>
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
        <?php if ($setRealNameCollectionAdmin): ?>
            <th>real name</th>
        <?php endif; ?>
        <th>phone</th>
        <th>status</th>
        <th>created time</th>
        <th>operation</th>
    </tr>
    <?php foreach ($users as $value): ?>
        <tr class="hover">
            <td><?php echo Html::encode($value['id']); ?></td>
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
            <?php if ($setRealNameCollectionAdmin): ?>
                <th>
                    <?php echo Html::encode($value['real_name']); ?>
                </th>
            <?php endif; ?>
            <th><?php echo Html::encode($value['phone']); ?></th>
            <th><?php echo Html::encode(isset(AdminUser::$open_status_list[$value['open_status']]) ?AdminUser::$open_status_list[$value['open_status']]:"--" ); ?></th>
            <th><?php echo Html::encode(date("Y-m-d H:i:s" , $value['created_at'])); ?></th>
            <td>
                <a href="<?php echo Html::encode(Url::to(['user-collection/super-team-leader-edit', 'id' => $value['id']])); ?>">Edit</a>
                <a href="<?php echo Html::encode(Url::to(['user-collection/set-super-team-leader-deputy', 'id' => $value['id']])); ?>">Set Deputy</a>
                <a href="<?php echo Html::encode(Url::to(['user-collection/change-pwd', 'id' => $value['id']])); ?>">ChangePwd</a>
                <a style="color: red;" onclick="return confirm('Operation irreversible, confirm delete？');" href="<?php echo Html::encode(Url::to(['user-collection/super-team-leader-del', 'id' => $value['id']])); ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>