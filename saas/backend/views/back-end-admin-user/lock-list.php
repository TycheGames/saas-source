<?php

use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\models\AdminUser;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_lock_list');
$this->showsubmenu('账号解锁', array(
    array('List', Url::toRoute('back-end-admin-user/lock_list'), 1),
));

?>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>ID</th>
            <th>用户名</th>
            <th>手机号</th>
            <th>角色</th>
            <th>操作</th>
        </tr>
        <?php foreach ($users as $value): ?>
            <tr class="hover">
                <td class="td25"><?php echo Html::encode($value->id); ?></td>
                <td><?php echo Html::encode($value->username); ?></td>
                <td><?php echo Html::encode($value->phone); ?></td>
                <td style="word-wrap: break-word; word-break: normal;word-break:break-all; "><?php echo Html::encode($value->role); ?></td>
                <td class="td24">
                    <a onclick="return confirmMsg('Are you sure you want to unlock it ?');" href="<?php echo Url::to(['admin-user/unlock', 'id' => Html::encode($value->id)]); ?>">解锁</a>
                    <a href="<?= Html::encode(Url::to(['back-end-admin-user/login-records', 'username' => $value->username, 'search_submit' => 'filter']));?>">查看详情</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>