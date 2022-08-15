<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/5/26
 * Time: 18:27
 */
use yii\helpers\Url;
use yii\helpers\Html;
/**
 * @var backend\components\View $this
 */
$this->showsubmenu(Yii::T('common', 'Role management'), array(
    array('List', Url::toRoute('back-end-admin-user/role-list'), 1),
));
?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>ID</th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'username') ?></th>
                <th><?php echo Yii::T('common', 'Note Name') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'Add time') ?></th>
            </tr>
            <?php foreach ($result as $k=>$value): ?>
                <tr>
                    <td><?php echo Html::encode($k+1); ?></td>
                    <td><?php echo Html::encode($value['id'])?></td>
                    <td><?php echo Html::encode($value['username']); ?></td>
                    <td><?php echo Html::encode($value['mark']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode(date('Y-m-d H:i:s',$value['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($result)): ?>
            <div class="no-result" style="color:red;font-size: 18px"><?php echo Yii::T('common', 'No members under this role') ?></div>
         <?php else: ?>
            <div class="no-result" style="color:red;font-size: 18px"><?php echo 'There are '.$count.' members in this role.'?></div>
        <?php endif; ?>
