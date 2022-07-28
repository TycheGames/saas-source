<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\workOrder\UserApplyComplaint;

$this->shownav('customer', 'menu_complaint_order_list');
$this->showsubmenu('Complaint order', array(
));

?>
<?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
Name：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('name', '')); ?>" name="name" class="txt" style="width:120px;">&nbsp;
Phone：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt" style="width:120px;">&nbsp;
Accept status：<?= Html::dropDownList('accept_status', Html::encode(\yii::$app->request->get('accept_status', '')), UserApplyComplaint::$accept_status_map,['prompt' => 'all']); ?>&nbsp;
<input type="submit" name="search_submit" value="search" class="btn">
<?php $form = ActiveForm::end(); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <th>NO</th>
                <th>User ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Contact Information</th>
                <th>Accept status</th>
                <th>Operation</th>
            </tr>
            <?php
            foreach ($list as $value): ?>
                <tr class="hover">
                    <td><?php echo Html::encode($value['id']); ?></td>
                    <td><?php echo Html::encode($value['user_id']); ?></td>
                    <td><?php echo Html::encode($value['name']); ?></td>
                    <td><?php echo Html::encode($value['phone']); ?></td>
                    <td><?php echo Html::encode($value['contact_information']); ?></td>
                    <td><?php echo Html::encode(UserApplyComplaint::$accept_status_map[$value['accept_status']]); ?></td>
                    <td>
                        <?php if($value['accept_status'] != UserApplyComplaint::ACCEPT_FINISH_STATUS):?>
                        <a target="_blank" href="<?php echo Url::to(['accept-complaint-order', 'id' => $value['id']]);?>">acceptOrder</a>
                        <?php endif;?>
                        <a href="<?php echo Url::to(['accept-complaint-order-detail', 'id' => $value['id']]);?>">detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>
