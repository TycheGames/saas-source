<?php

use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\models\AdminUser;
use yii\helpers\Html;
use callcenter\components\widgets\ActiveForm;
use backend\models\Merchant;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_adminuser_list');
$this->showsubmenu('管理员管理', array(
	array('列表', Url::toRoute('admin-user/list'), 1),
	array('添加管理员', Url::toRoute('admin-user/add'), 0),
));
$merchantList = Merchant::getMerchantId();
?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>
	用户名关键词：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
	手机号关键词：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
    创建人：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('created_user', '')); ?>" name="created_user" class="txt" style="width:120px;">&nbsp;
    角色：<?php echo Html::dropDownList('role', Html::encode(Yii::$app->getRequest()->get('role', '')), $role_lsit, ['prompt' => '所有角色']); ?>&nbsp;
    <?php if($isNotMerchantAdmin):?>
        商户：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $merchantList, ['prompt' => Yii::T('common', 'All')]); ?>&nbsp;&nbsp;
    <?php endif;?>
    账号状态：<?php echo Html::dropDownList('open_status', Html::encode(Yii::$app->getRequest()->get('open_status', '')), AdminUser::$open_status_list, ['prompt' => 'all']); ?>&nbsp;
    <input type="submit" name="search_submit" value="过滤" class="btn">
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
	<tr class="header">
		<th>ID</th>
		<th>用户名</th>
		<th>手机号</th>
		<th>角色</th>
		<th>创建人</th>
		<th>创建时间</th>
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Yii::T('common', 'merchant') ?></th>
        <?php endif;?>
        <th>账号状态</th>
		<th>备注/姓名</th>
		<th>操作</th>
	</tr>
	<?php foreach ($users as $value): ?>
	<tr class="hover">
		<td class="td25"><?php echo Html::encode($value->id); ?></td>
		<td><?php echo Html::encode($value->username); ?></td>
        <th><?php echo $isHiddenPhone ? Html::encode(substr_replace($value->phone,'*****',0,5)) : Html::encode($value->phone); ?></th>
        <td style="word-wrap: break-word; word-break: normal;word-break:break-all; "><?php echo Html::encode(rtrim($value['role'],',')) ?></td>
		<td><?php echo Html::encode($value->created_user); ?></td>
		<td><?php echo Html::encode(date('Y-m-d', $value->created_at)); ?></td>
        <?php if($isNotMerchantAdmin):?>
            <th><?php echo Html::encode($merchantList[$value->merchant_id] ?? ''); ?></th>
        <?php endif;?>
        <td><?php echo Html::encode(AdminUser::$open_status_list[$value->open_status]); ?></td>
		<td><?php echo Html::encode($value->mark); ?></td>
		<td class="td24">
            <?php if($value->open_status == AdminUser::OPEN_STATUS_OFF): ?>
                <a onclick="return confirmMsg('确定要恢复吗,会影响班表的在职时间?');" href="<?php echo Url::to(['admin-user/recovery', 'id' => Html::encode($value->id)]); ?>">账号恢复</a>
            <?php else:?>
                <a href="<?php echo Html::encode(Url::to(['admin-user/change-pwd', 'id' => $value->id])); ?>">修改密码</a>
                <a href="<?php echo Html::encode(Url::to(['admin-user/edit', 'id' => $value->id])); ?>">编辑</a>
                <a onclick="return confirmMsg('确定要删除吗？');" href="<?php echo Html::encode(Url::to(['admin-user/delete', 'id' => $value->id])); ?>">删除</a>
                <?php if(AdminUser::OPEN_STATUS_LOCK == $value->open_status): ?>
                    <a onclick="return confirmMsg('Are you sure you want to unlock it ?');" href="<?php echo Html::encode(Url::to(['admin-user/unlock', 'id' => $value->id])); ?>"><?php echo Yii::T('common', 'unlock') ?></a>
                <?php endif; ?>
            <?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>