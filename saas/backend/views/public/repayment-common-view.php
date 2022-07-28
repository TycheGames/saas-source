<?php
use yii\helpers\Html;
?>


<style>
    .person {
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
    }
    .table {
        max-width: 100%;
        width: 100%;
        border:1px solid #ddd;
    }
    .table th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid darkgray;
    }
    .tb2 th{
        border:1px solid darkgray;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .tb2 td{
        border:1px solid darkgray;
    }
    .tb2 {
        border:1px solid darkgray;
    }
    .mark {
        font-weight: bold;
        /*background-color:indianred;*/
        color:red;
    }
</style>

<table class="tb tb2 fixpadding">

    <tr><th class="partition" colspan="10">User information</th></tr>
    <tr>
        <td class="td21">User ID：</td>
        <td width="200"><?php echo Html::encode($information['loanPerson']['id']); ?></td>
        <td class="td21">Register Time：</td>
        <td ><?php echo Html::encode(empty($information['loanPerson']['created_at'])?'--':date('Y-m-d',$information['loanPerson']['created_at'])); ?></td>
    </tr>

    <tr>
        <td class="td21">Name：</td>
        <td ><?php echo Html::encode($information['loanPerson']['name']); ?></td>
        <td class="td21">Phone：</td>
        <td ><?php echo Html::encode($information['loanPerson']['phone']); ?></td>
    </tr>

    <tr>
        <td class="td21">Pan Code：</td>
        <td ><?php echo Html::encode($information['loanPerson']['pan_code']); ?></td>
        <td class="td21">Birthday(Pan.)：</td>
        <td ><?php echo Html::encode($information['loanPerson']['birthday'] ?? '--'); ?></td>
    </tr>

    <tr>
        <td class="td21">Company：</td>
        <td ><?php echo Html::encode($information['userWorkInfo']['company_name']); ?></td>
    </tr>
</table>
<table class="tb tb2 fixpadding">

    <tr><th class="partition" colspan="10">Borrowing information</th></tr>
    <tr>
        <td class="td21">Loan Order NO：</td>
        <td width="200"><?php echo Html::encode($information['userLoanOrder']['id']); ?></td>
        <td class="td21">Loan Money（Rub）：</td>
        <td ><?php echo sprintf("%0.2f",($information['userLoanOrder']['amount'] + $information['userLoanOrder']['interests'])/100); ?></td>
    </tr>

    <tr>
        <td class="td21">Apply Time：</td>
        <td ><?php echo date('Y-m-d H:i:s',$information['userLoanOrder']['order_time']); ?></td>
        <td class="td21">Expected repayment time：</td>
        <td ><?php echo date("Y-m-d",$information['userLoanOrderRepayment']['plan_repayment_time']); ?></td>
    </tr>

    <tr>
        <td class="td21">Interest day rate(‱)：</td>
        <td ><?php echo Html::encode($information['userLoanOrder']['day_rate']);?></td>
        <td class="td21">Cost rate：</td>
        <td ><?php echo Html::encode($information['userLoanOrder']['cost_rate']);?></td>
    </tr>

    <tr>
        <td class="td21">Interest（Rub）：</td>
        <td><?php echo sprintf("%0.2f",$information['userLoanOrder']['interests']/100); ?></td>
        <td class="td21">Cost fee（Rub）：</td>
        <td ><?php echo sprintf("%0.2f",$information['userLoanOrder']['cost_fee']/100); ?></td>
    </tr>

</table>
<table class="tb tb2 fixpadding">

    <tr><th class="partition" colspan="10">Bank Card information</th></tr>

    <tr>
        <td class="td21">Bank name：</td>
        <td  width="200"><?php echo Html::encode($information['userBankAccount']['name']);?></td>
        <td class="td21">Bank card number：</td>
        <td ><?php echo Html::encode($information['userBankAccount']['account']); ?></td>
    </tr>

    <tr>
        <td class="td21">Bind card time：</td>
        <td ><?php echo date('Y-m-d',$information['userBankAccount']['created_at']); ?></td>
        <td class="td21">Card status：</td>
        <td ><?php echo Html::encode($information['userBankAccount']['status']); ?></td>
    </tr>
</table>
