<?php

use yii\helpers\Url;
use callcenter\components\widgets\ActiveForm;
use yii\grid\GridView;
use callcenter\assets\AppAsset;
use callcenter\components\widgets\LinkPager;
use yii\helpers\Html;

AppAsset::register($this);

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_adminuser_nx_list');


$this->showsubmenu('催收员牛信账号绑定', array(
    array(Yii::T('common', 'List'), Url::toRoute('admin-user/nx-list'), 1),
    array(Yii::T('common', 'Add'), Url::toRoute('admin-user/nx-add'), 0),
));

?>

    <style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['admin-user/nx-list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    collector_id：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collector_id', '')); ?>" name="collector_id" class="txt" style="width:120px;">&nbsp;
    username：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?>" name="username" class="txt" style="width:120px;">&nbsp;
    nx_name：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('nx_name', '')); ?>" name="nx_name" class="txt" style="width:120px;">&nbsp;
    <input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;

<?php $form = ActiveForm::end(); ?>

    <?php $form = ActiveForm::begin(['id' => 'up', 'method' => "post", 'action'=>Url::to(['admin-user/nx-batch-add']),'options' => ['enctype' => 'multipart/form-data']]); ?>
    <?php echo Html::fileInput('files'); ?><label style="color:red;">(Remarks: please import (CSV file) in the added format!)</label>
    <input type="submit" value="Importing"  class="btn" />&nbsp;
    <?php ActiveForm::end(); ?>


    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>ID</th>
            <th>username</th>
            <th>nx_name</th>
            <th>password</th>
            <th>status</th>
            <th>type</th>
            <th>operation</th>
        </tr>
        <?php foreach ($users as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['id']); ?></td>
                <th><?php echo Html::encode($value['username']); ?></th>
                <th><?php echo Html::encode($value['nx_name']); ?></th>
                <th><?php echo Html::encode($value['password']); ?></th>
                <th><?php echo Html::encode(\callcenter\models\AdminNxUser::$status_map[$value['status']]); ?></th>
                <th><?php echo Html::encode(\callcenter\models\AdminNxUser::$type_map[$value['type']]); ?></th>
                <td>
                    <a href="<?php echo Url::to(['admin-user/nx-edit', 'id' => $value['id'],'history_url' => $url]); ?>">Edit</a>&nbsp;&nbsp;
                    <a href="<?php echo Url::to(['admin-user/nx-del', 'id' => $value['id'],'history_url' => $url]); ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>