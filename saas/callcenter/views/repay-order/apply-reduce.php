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
        <tr><th class="partition" colspan="15">apply for reduction</th></tr>
        <tr>
            <td class="td24">should repay amount(rupee)：</td>
            <td><?php echo Html::encode($repaymentInfo['scheduledPaymentAmount']/100); ?></td>
        </tr>
        <tr>
            <td class="td24">principal(rupee)：</td>
            <td><?php echo Html::encode($repaymentInfo['principal']/100); ?></td>
        </tr>
        <tr>
            <td class="td24">true repaid amount(rupee)：</td>
            <td><?php echo Html::encode($repaymentInfo['trueTotalMoney']/100); ?></td>
            <input name="true_total_money" type="hidden" value="<?php echo Html::encode($repaymentInfo['trueTotalMoney']); ?>">
        </tr>
        <tr>
            <td class="td24">reduction remark ：</td>
            <td><?php echo Html::textarea('reduce_remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="hidden" name="view_type" value="loan"/>
                <input type="submit" value="apply" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>