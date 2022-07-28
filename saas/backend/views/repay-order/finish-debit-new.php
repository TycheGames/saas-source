<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/5/16
 * Time: 11:29
 */
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\order\UserRepaymentLog;
/**
 * @var backend\components\View $this
 */
?>
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15">手动还款</th></tr>
        <tr>
            <td class="td24">应还金额(元)：</td>
            <td><?php echo ($repaymentInfo['scheduledPaymentAmount']/100); ?></td>
        </tr>
        <tr>
            <td class="td24">实际已还金额(元)：</td>
            <td><?php echo $repaymentInfo['trueTotalMoney']/100; ?></td>
            <input name="true_total_money" type="hidden" value="<?php echo $repaymentInfo['trueTotalMoney']; ?>">
        </tr>
        <tr class="dis_class">
            <td class="td24" id="money_td">本次还款金额(元)：</td>
            <td><?php echo Html::textInput('money',''); ?></td>
        </tr>
        <tr>
            <td class="td24">流水号(Transaction ID)：</td>
            <td><?php echo Html::textarea('uuid', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td class="td24">还款备注：</td>
            <td><?php echo Html::textarea('remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="hidden" name="view_type" value="loan"/>
                <input type="submit" value="提交" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>