<?php

use yii\helpers\Url;
use callcenter\components\widgets\ActiveForm;
use yii\grid\GridView;
use callcenter\assets\AppAsset;
use callcenter\components\widgets\LinkPager;
use yii\helpers\Html;
use callcenter\models\AbsenceApply;
use callcenter\models\CollectorClassSchedule;

AppAsset::register($this);

$this->shownav('manage', 'menu_user_collection_begin','menu_user_class_schedule');
$this->showsubmenu('', array(
    array('Daily Work Plan', Url::toRoute('user-collection/class-schedule'), 0),
    array('Absence Apply', Url::toRoute('user-collection/absence-apply'), 0),
    array('Preliminary review', Url::toRoute('user-collection/audit-apply'), 0),
    array('Final review', Url::toRoute('user-collection/finish-audit-apply'), 1),
));

?>

    <style>.tb2 th{ font-size: 12px;}</style>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['user-collection/finish-audit-apply']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo Html::encode($this->baseUrl) ?>/js/My97DatePicker/WdatePicker.js"></script>
<!--    username：<input type="text" value="--><?php //echo Html::encode(Yii::$app->getRequest()->get('username', '')); ?><!--" name="username" class="txt" style="width:120px;">&nbsp;-->
<!--    nx_name：<input type="text" value="--><?php //echo Html::encode(Yii::$app->getRequest()->get('nx_name', '')); ?><!--" name="nx_name" class="txt" style="width:120px;">&nbsp;-->
    <input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;

<?php $form = ActiveForm::end(); ?>

    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>ID</th>
            <th>date</th>
            <th>outside</th>
            <th>phone</th>
            <th>group</th>
            <th>group_game</th>
            <th>collector</th>
            <th>status</th>
            <th>type</th>
            <th>dispatch to person</th>
            <th>operation</th>
        </tr>
        <?php foreach ($auditApply as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value['id']); ?></td>
                <th><?php echo Html::encode($value['date']); ?></th>
                <th><?php echo Html::encode($companyList[$value['outside']] ?? '-'); ?></th>
                <th><?php echo Html::encode($value['phone'] ?? '-'); ?></th>
                <td ><?php echo Html::encode(\callcenter\models\loan_collection\LoanCollectionOrder::$level[$value['group']] ?? '-'); ?></td>
                <td ><?php echo Html::encode(\callcenter\models\AdminUser::$group_games[$value['group_game']] ?? '-'); ?></td>
                <th><?php echo Html::encode($value['username']); ?></th>
                <th><?php echo Html::encode(AbsenceApply::$status_map[$value['status']]); ?></th>
                <th><?php echo Html::encode(CollectorClassSchedule::$absence_type_map[$value['type']]); ?></th>
                <?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['user-collection/finish-audit','id'=>$value['id']]), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
                <td>
                    <input type="text" value="" name="user_id" class="txt" style="width:200px;">&nbsp;
                </td>
                <td>
                    <?php echo Html::dropDownList('type','', AbsenceApply::$type_map); ?>&nbsp;
                    <input type="submit" name="search_submit" value="submit" class="btn">&nbsp;&nbsp;
                </td>
                <?php $form = ActiveForm::end(); ?>
            </tr>
        <?php endforeach; ?>
    </table>
<?php if (empty($users)): ?>
    <div class="no-result">No record</div>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>