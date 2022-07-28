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
        <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'Deduction') ?></th></tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Amount due') ?>(<?php echo Yii::T('common', 'yuan') ?>)：</td>
            <td><?php echo ($repaymentInfo['scheduledPaymentAmount']/100); ?></td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Principal') ?>(<?php echo Yii::T('common', '元') ?>)：</td>
            <td><?php echo ($repaymentInfo['principal']/100); ?></td>
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Actual amount paid') ?>(<?php echo Yii::T('common', '元') ?>)：</td>
            <td><?php echo $repaymentInfo['trueTotalMoney']/100; ?></td>
            <input name="true_total_money" type="hidden" value="<?php echo Html::encode($repaymentInfo['trueTotalMoney']); ?>">
        </tr>
        <tr>
            <td class="td24"><?php echo Yii::T('common', 'Relief notes') ?>：</td>
            <td><?php echo Html::textarea('remark', '', ['style' => 'width:300px;']); ?></td>
        </tr>
        <tr>
            <td colspan="15">
                <input type="hidden" name="view_type" value="loan"/>
                <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>