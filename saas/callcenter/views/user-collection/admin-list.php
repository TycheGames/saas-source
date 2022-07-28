<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminUser;

$this->shownav('manage', 'menu_admin_list');
$this->showsubmenu('', array(
    array('admin list', Url::toRoute('user-collection/admin-list'), 1),
    array('add admin', Url::toRoute('user-collection/admin-add'),0),
));

?>
<style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
username：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
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
        <?php endif; ?>
        <th>username</th>
        <th>phone</th>
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
            <?php endif; ?>
            <th><?php echo Html::encode($value['username']); ?></th>
            <th><?php echo Html::encode($isHiddenPhone ? substr_replace($value['phone'],'*****',0,5) : $value['phone']); ?></th>
            <th><?php echo Html::encode(date("Y-m-d H:i:s" , $value['created_at'])); ?></th>
            <td>
                <a href="<?php echo Url::to(['user-collection/admin-edit', 'id' => $value['id']]); ?>">Edit</a>
                <a style="color: red;" onclick="return confirm('Operation irreversible, confirm delete？');" href="<?php echo Url::to(['user-collection/admin-del', 'id' => $value['id']]); ?>">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>