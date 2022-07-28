<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 15:53
 */
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use yii\helpers\Url;
use yii\helpers\Html;

?>
<?php
echo $this->render('/public/repayment-common-view', array(
    'information' => $information,
));
?>
<table class="tb tb2 fixpadding">
    <tr><th class="partition" colspan="10">Payment information</th></tr>
    <tr>
        <td class="td21">Repay Order NO：</td>
        <td width="200"><?php echo Html::encode($information['userLoanOrderRepayment']['id']); ?></td>
        <td class="td21">Expected total amount of repayment：</td>
        <td ><?php echo sprintf("%0.2f",($information['userLoanOrderRepayment']['principal']+$information['userLoanOrderRepayment']['interests'])/100); ?></td>
    </tr>

    <tr>
        <td class="td21">Expected repayment interest：</td>
        <td ><?php echo sprintf("%0.2f",($information['userLoanOrderRepayment']['interests'])/100); ?></td>
        <td class="td21">Expected repayment principal：</td>
        <td ><?php echo sprintf("%0.2f",$information['userLoanOrderRepayment']['principal']/100); ?></td>
    </tr>

    <tr>
        <td class="td21">Expected repayment time：</td>
        <td ><?php echo date('Y-m-d',$information['userLoanOrderRepayment']['plan_repayment_time']); ?></td>
        <td class="td21">Actual repayment time ：</td>
        <td ><?php echo empty($information['userLoanOrderRepayment']['true_repayment_time'])?'':date('Y-m-d',$information['userLoanOrderRepayment']['true_repayment_time']); ?></td>
    </tr>

    <tr>
        <td class="td21">Actual total amount of repayments：</td>
        <td ><?php echo sprintf("%0.2f",$information['userLoanOrderRepayment']['true_total_money']/100); ?></td>
        <td class="td21">Application for repayment time：</td>
        <td><?php echo empty($information['userLoanOrderRepayment']['apply_repayment_time'])?'':date('Y-m-d',$information['userLoanOrderRepayment']['apply_repayment_time']);?></td>
    </tr>

    <tr>
        <td class="td21">virtual account：</td>
        <td ><?php echo Html::encode($virtualAccount['va_account']); ?></td>
        <td class="td21">virtual ifsc：</td>
        <td><?php echo Html::encode($virtualAccount['va_ifsc']);?></td>
        <td class="td21">virtual account beneficiary：</td>
        <td><?php echo Html::encode($virtualAccount['va_name']);?></td>
    </tr>
    <tr>
        <td class="td21">upi address：</td>
        <td ><?php echo Html::encode($virtualAccount['address']); ?></td>

    </tr>
</table>