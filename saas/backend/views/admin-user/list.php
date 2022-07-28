<?php

use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\models\AdminUser;
use yii\helpers\Html;
use backend\components\widgets\ActiveForm;
use backend\models\Merchant;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu(Yii::T('common', 'Administrator management'), array(
	array('List', Url::toRoute('admin-user/list'), 1),
	array('Add', Url::toRoute('admin-user/add'), 0),
));
$merchantList = Merchant::getMerchantId();
?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'Username keywords') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Phone keywords') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
    created user：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('created_user', '')); ?>" name="created_user" class="txt" style="width:120px;">&nbsp;
<?php echo Yii::T('common', 'Roles') ?>：<?php echo Html::dropDownList('role', Html::encode(Yii::$app->getRequest()->get('role', '')), \common\helpers\CommonHelper::getListT($role_lsit), ['prompt' => Yii::T('common', 'All Groups')]); ?>&nbsp;
<?php if($isNotMerchantAdmin):?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $merchantList, ['prompt' => Yii::T('common', 'All')]); ?>&nbsp;&nbsp;
    账号状态：<?php echo Html::dropDownList('open_status', Html::encode(Yii::$app->getRequest()->get('open_status', '')), AdminUser::$open_status_list, ['prompt' => 'all']); ?>&nbsp;
<?php endif;?>
    <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>ID</th>
		<th><?php echo Yii::T('common', 'username') ?>	</th>
		<th><?php echo Yii::T('common', 'phone') ?></th>
		<th><?php echo Yii::T('common', 'Roles') ?></th>
		<th><?php echo Yii::T('common', 'founder') ?></th>
		<th><?php echo Yii::T('common', 'Creation time') ?></th>
		<th><?php echo Yii::T('common', 'Remarks') ?>/<?php echo Yii::T('common', 'name') ?></th>
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Yii::T('common', 'merchant') ?></th>
            <th>帐号状态</th>
        <?php endif;?>
		<th><?php echo Yii::T('common', 'operation') ?></th>
	</tr>
	<?php foreach ($users as $value): ?>
	<tr class="hover">
		<td><?php echo Html::encode($value->id); ?></td>
		<td><?php echo Html::encode($value->username); ?></td>
        <th><?php echo Html::encode($isHiddenPhone ? substr_replace($value->phone,'*****',0,5) : $value->phone); ?></th>
		<td style="word-wrap: break-word; word-break: normal;word-break:break-all; "><?php echo Html::encode($value->role); ?></td>
		<td><?php echo Html::encode($value->created_user); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
		<td><?php echo Html::encode($value->mark); ?></td>
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Html::encode($merchantList[$value->merchant_id] ?? ''); ?></th>
            <th><?php echo Html::encode(AdminUser::$open_status_list[$value->open_status] ?? ''); ?></th>
        <?php endif;?>
		<td class="td24">
            <?php if($value->open_status == AdminUser::OPEN_STATUS_OFF): ?>
                <a onclick="return confirmMsg('确定要恢复吗?');" href="<?php echo Url::to(['admin-user/recovery', 'id' => Html::encode($value->id)]); ?>">账号恢复</a>
            <?php else:?>
                <a href="<?php echo Url::to(['admin-user/change-pwd', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'change Password') ?></a>
                <a href="<?php echo Url::to(['admin-user/edit', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'edit') ?></a>
                <a onclick="return confirmMsg('Are you sure you want to delete it ?');" href="<?php echo Url::to(['admin-user/delete', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'del') ?></a>
                <?php if(AdminUser::OPEN_STATUS_LOCK == $value->open_status): ?>
                    <a onclick="return confirmMsg('Are you sure you want to unlock it ?');" href="<?php echo Url::to(['admin-user/unlock', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'unlock') ?></a>
                <?php endif; ?>
            <?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>