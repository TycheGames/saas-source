<?php
/**
 * author wolfbian
 * date 2016-09-24
 */
use yii\helpers\Html;
use common\helpers\CommonHelper;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use common\models\coupon\UserCouponInfo;
use backend\models\Merchant;

$this->showsubmenu(Yii::T('common', 'Operations Center'), array(
    array(Yii::T('common', 'Coupon receipt list'), Url::toRoute('user-coupon/list'), 1),
    array(Yii::T('common', 'Coupon compensate'), Url::toRoute('user-coupon/insert-for-loan'), 0),
));
?>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<?php $form = ActiveForm::begin(['id' => 'searchform', 'method' => 'get', 'action'=>Url::to(['user-coupon/list']), 'options' => ['style' => 'margin-bottom:5px;']]); ?>
<?php echo Yii::T('common', 'userId') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('id', '')); ?>" name="id" class="txt">&nbsp;
<?php echo Yii::T('common', 'phone') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('phone', '')); ?>" name="phone" class="txt">&nbsp;
<?php echo Yii::T('common', 'type') ?>：<?php echo Html::dropDownList('use_case', Html::encode(Yii::$app->getRequest()->get('use_case', '')), CommonHelper::getListT(UserCouponInfo::$use_case),
        array(
            'prompt' => Yii::T('common', 'All types'),
        )); ?>
<?php echo Yii::T('common', 'status') ?>：<?php echo Html::dropDownList('is_use', Html::encode(Yii::$app->getRequest()->get('is_use', '')), CommonHelper::getListT(UserCouponInfo::$status_arr),
    array(
        'prompt' => Yii::T('common', 'All status'),
    )); ?>
<?php echo Yii::T('common', 'Coupon template') ?>：<?php echo Html::dropDownList('coupon_id', Html::encode(Yii::$app->getRequest()->get('coupon_id', '')), CommonHelper::getListT($tmp_list),
    array(
        'prompt' => Yii::T('common', 'All template'),
    )); ?></br>
<?php echo Yii::T('common', 'Release time') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_created_at', '')); ?>"  name="start_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('end_created_at', '')); ?>"  name="end_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'usage time') ?>：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('start_use_time', '')); ?>"  name="start_use_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})">&nbsp;&nbsp;
<?php echo Yii::T('common', 'to') ?><input type="text" value="<?php echo Html::encode( Yii::$app->getRequest()->get('end_use_time', '')); ?>"  name="end_use_time" onfocus="WdatePicker({startDate:'%y-%M-%d %H:%m:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true,readOnly:true})">&nbsp;
     <input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
    <label><input type="checkbox" name="is_summary" value="1" <?php if(Yii::$app->request->get('is_summary', '0')==1):?> checked <?php endif; ?> /> <?php echo Yii::T('common', 'Show summary (checked, query becomes slower)') ?></label>
<?php ActiveForm::end(); ?>

<?php if(!empty($dataSt)): ?>
    <table frame="above" align="right">
        <tr>
            <td align="center" style="color: red;"><?php echo Yii::T('common', 'Total') ?><?= Html::encode($dataSt['num']); ?><?php echo Yii::T('common', 'article') ?>，<?php echo Yii::T('common', 'Used') ?><?= $dataSt['use_num']; ?><?php echo Yii::T('common', 'article') ?></td>
        </tr>
    </table>
<?php endif; ?>

<?php ActiveForm::begin(['id' => 'listform']); ?>
        <table class="tb tb2 fixpadding">
            <tr class="header">
                <?php if($isNotMerchantAdmin):?>
                    <th><?php echo Yii::T('common', 'Merchant name') ?></th>
                <?php endif;?>
                <th><?php echo Yii::T('common', 'Coupon batch number') ?></th>
                <th><?php echo Yii::T('common', 'userId') ?></th>
                <th><?php echo Yii::T('common', 'phone') ?></th>
                <th><?php echo Yii::T('common', 'Coupon template') ?> ID/ <?php echo Yii::T('common', 'Template title') ?></th>
                <th><?php echo Yii::T('common', 'Coupon quota') ?></th>
                <th><?php echo Yii::T('common', 'status of use') ?></th>
                <th><?php echo Yii::T('common', 'usage time') ?></th>
                <th><?php echo Yii::T('common', 'Release time') ?></th>
                <th><?php echo Yii::T('common', 'Expiration date') ?></th>
            </tr>
            <?php foreach ($data_list as $key => $value): ?>
                <tr class="hover">
                    <?php if($isNotMerchantAdmin):?>
                        <td><?= Html::encode(Merchant::getMerchantId()[$value->merchant_id]); ?></td>
                    <?php endif;?>
                    <td><?php echo Html::encode($value->coupon_code); ?></td>
                    <td><?php echo Html::encode(CommonHelper::idEncryption($value->user_id, 'user')); ?></td>
                    <td><?php echo Html::encode($value->phone); ?></td>
                    <td><?php echo Html::encode($temp_data[$key]['coupon_title']); ?></td>
                    <td><?php echo Html::encode($value->amount/100); ?></td>
                    <td><?php echo Html::encode(UserCouponInfo::$status_arr[$value->is_use]); ?></td>
                    <td><?php echo Html::encode((!empty($value->use_time)) ? date("Y-m-d H:i:s", $value->use_time) : ''); ?></td>
                    <td><?php echo Html::encode((!empty($value->created_at)) ? date("Y-m-d H:i:s", $value->created_at) : ''); ?></td>
                    <td><?php echo Html::encode($value->expire_str); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
<?php ActiveForm::end(); ?>

<?php if (empty($data_list)): ?>
    <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
<?php endif; ?>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>