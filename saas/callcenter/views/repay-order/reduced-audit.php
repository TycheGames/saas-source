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
use callcenter\models\CollectionReduceApply;
/**
 * @var backend\components\View $this
 */
?>
<?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15">audit for reduction</th></tr>
        <tr>
            <td class="td24">本金(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['principal']/100); ?></td>
            <td class="td24">利息(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['interests']/100); ?></td>
            <td class="td24">服务费(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['costFee']/100); ?></td>
            <td class="td24">逾期罚息(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['overdueFee']/100); ?></td>
        </tr>
        <tr>
            <td class="td24">总应还还金额(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['totalMoney']/100); ?></td>
            <td class="td24">实际已还金额(元)：</td>
            <td><?php echo Html::encode($repaymentInfo['trueTotalMoney']/100); ?></td>
            <td class="td24">延期减免金额：</td>
            <td style="color:red;"><?php echo Html::encode($repaymentInfo['delayReduceAmount'] / 100); ?></td>
            <td class="td24">本次可减免金额：</td>
            <td style="color:red;"><?php echo Html::encode($repaymentInfo['scheduledPaymentAmount'] / 100); ?></td>
        </tr>
        <tr>
            <td class="td24">减免备注：</td>
            <td><?php echo Html::encode($collectionReduceApply['apply_remark']); ?></td>
        </tr>
        <tr>
            <td class="td24">审批操作：</td>
            <td>  <?php echo Html::radioList('audit_operation', true, ['1' => 'pass','2' => 'not pass']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="hidden" name="view_type" value="loan"/>
                <input type="submit" value="提交" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>