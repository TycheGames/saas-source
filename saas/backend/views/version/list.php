<?php
/**
 * author wolfbian
 * date 2016-09-24
 */
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;

$this->shownav('system', 'menu_check_version_config');
$this->showsubmenu(Yii::T('common', 'App update management'), array(
    array(Yii::T('common', 'List of version update rules'), Url::toRoute('list'), 1),
    array(Yii::T('common', 'Add version update rule'), Url::toRoute('add'), 0),
));
?>

<?php ActiveForm::begin(['id' => 'listform']); ?>
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th>app_market</th>
            <th><?php echo Yii::T('common', 'Matching version') ?></th>
            <th><?php echo Yii::T('common', 'Whether to remind the upgrade') ?></th>
            <th><?php echo Yii::T('common', 'Whether to force upgrade') ?></th>
            <th><?php echo Yii::T('common', 'IOS latest version') ?></th>
            <th><?php echo Yii::T('common', 'Android latest version') ?></th>
            <th><?php echo Yii::T('common', 'Whether to enable') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($list as $value): ?>
            <tr class="hover">
                <td><?= Html::encode($value['app_market']) ?></td>
                <td><?= Html::encode($value['rules']) ?></td>
                <td><?php if($value['has_upgrade'] == 1){echo Yii::T('common', 'upgrade') ;}elseif($value['has_upgrade'] == 0){ echo  Yii::T('common', 'Don\'t upgrade');} ?></td>
                <td><?php if($value['is_force_upgrade'] == 1){echo  Yii::T('common', 'Force upgrade');}elseif($value['is_force_upgrade'] == 0){ echo Yii::T('common', 'Do not force upgrade');} ?></td>
                <td><?= Html::encode($value['new_ios_version']); ?></td>
                <td><?= Html::encode($value['new_version']); ?></td>
                <td><?php if($value['status'] == 1){echo Yii::T('common', 'Enable');}else{ echo Yii::T('common', 'Disable');}; ?></td>
                <td>
                    <a href="<?= Url::to(['edit', 'id' => $value['id']]);?>"><?php echo Yii::T('common', 'edit') ?></a>
                    <a class="delItem" href="javascript:void(0)" tip="<?= Url::to(['del', 'id' => $value['id']]);?>"><?php echo Yii::T('common', 'del') ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php ActiveForm::end(); ?>

<?php if (empty($list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>
<script>
    $('.delItem').click(function(){
        var url = $(this).attr('tip');
        if(confirm("<?php echo Yii::T('common', 'Are you sure you want to delete this banner?') ?>")) {
            window.location.href = url;
        }
    })
</script>
