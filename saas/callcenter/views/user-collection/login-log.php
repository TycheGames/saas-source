<?php
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\AdminUser;
use yii\helpers\Html;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_adminuser_login_log');
$this->showsubmenu('Login log', array());
?>

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>

<script language="javascript" type="text/javascript" src="<?= $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<style>
    th { text-align: center;  }
    td {text-align: center;}
</style>
<?php $form = ActiveForm::begin([
    'method'=>'get',
    'action'=>Url::to(['user-collection/login-log']),
    'options' => ['style' => 'margin-bottom:5px;'],
]); ?>
用户ID：<input type="text" value="<?= Html::encode(Yii::$app->request->get('user_id', '')); ?>" name="user_id" style="width: 100px"/>&nbsp;
用户名：<input type="text" value="<?= Html::encode(Yii::$app->request->get('username', '')); ?>" name="username" class="txt"/>&nbsp;
手机号：<input type="text" value="<?= Html::encode(Yii::$app->request->get('phone', '')); ?>" name="phone" class="txt" />&nbsp;
公司：<?php echo Html::dropDownList('outside', Html::encode(Yii::$app->request->get('outside', 0)), $companyList,array('prompt' => '-all company-')); ?>&nbsp;
账龄：<?php echo Html::dropDownList('group', Html::encode(Yii::$app->request->get('group', 0)), LoanCollectionOrder::$level,array('prompt' => '-all-')); ?>&nbsp;
小组：<?php echo Html::dropDownList('group_game', Html::encode(Yii::$app->request->get('group_game', 0)), AdminUser::$group_games,array('prompt' => '-all-')); ?>&nbsp;
登录时间：<input type="text" value="<?= Html::encode(Yii::$app->request->get('login-start-time', '')); ?>" name="login-start-time"
            onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?= Html::encode(Yii::$app->request->get('login-end-time', '')); ?>"  name="login-end-time"
        onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})" />&nbsp;&nbsp;
<input type="submit" name="search_submit" value="筛选" class="btn" />
<input type="submit" name="exportcsv" value="导出csv" onclick="$(this).val('exportData');return true;" class="btn" />
<?php ActiveForm::end(); ?>

<table class="tb tb2 fixpadding">
    <tr class="header">
        <th>序号</th>
        <th>用户ID</th>
        <th>用户名</th>
        <th>手机号</th>
        <th>所属公司</th>
        <th>账龄组</th>
        <th>小组</th>
        <th>客户端IP</th>
        <th>登录时间</th>
    </tr>
    <?php foreach ($list as $value): ?>
        <tr class="hover">
            <td class="td25" ><?= Html::encode($value['id']); ?></td>
            <td><?= Html::encode($value['user_id']); ?></td>
            <td><?= Html::encode($value['username']); ?></td>
            <td><?= Html::encode($value['phone']); ?></td>
            <td><?= Html::encode($value['real_title']); ?></td>
            <td><?= Html::encode(LoanCollectionOrder::$level[$value['group']]); ?></td>
            <td><?= Html::encode(AdminUser::$group_games[$value['group_game']]); ?></td>
            <td><?= Html::encode($value['ip']); ?></td>
            <td><?= Html::encode(date('Y-m-d H:i:s', $value['created_at'])); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?= LinkPager::widget(['pagination' => $pages]); ?>
