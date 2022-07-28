<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use backend\models\Merchant;
/**
 * @var backend\components\View $this
 */
$this->showsubmenu(Yii::T('common', 'Total repayment amount data'), array(
));
?>
<style>
    table th{text-align: center}
    table td{text-align: center}
</style>
<title><?php echo Yii::T('common', 'Total repayment amount data')?></title>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.channel-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all') ?>'});
        $('.merchant-select').SumoSelect({ placeholder:'<?php echo Yii::T('common', 'Default all merchant') ?>'});
    });
</script>
<?php $form = ActiveForm::begin(['id' => 'search_form','method'=>'get', 'action'=>Url::to(['data-stats/total-repayment-amount']), 'options' => ['style' => 'margin-top:5px;']]); ?>
<?php echo Yii::T('common', 'Repayment date') ?>：
<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('begin_created_at')) ? date("Y-m-d", time()-7*86400) : Yii::$app->request->get('begin_created_at')); ?>"  name="begin_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php echo Yii::T('common', 'to') ?>：<input type="text" value="<?= Html::encode(empty(Yii::$app->request->get('end_created_at')) ? date("Y-m-d", time()+86400) : Yii::$app->request->get('end_created_at')); ?>"  name="end_created_at" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:false,readOnly:true})">&nbsp;
<?php if($isNotMerchantAdmin): ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?php  echo \yii\helpers\Html::dropDownList('merchant_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('merchant_id', [])),
        Merchant::getMerchantId(false),['class' => 'form-control merchant-select', 'multiple' => 'multiple', 'id' => 'merchant']); ?>&nbsp;
<?php endif;?>
<?php echo Yii::T('common', 'management') ?>：<?php echo Html::dropDownList('fund_id', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('fund_id', [])), $fundList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'fund_id']); ?>&nbsp;
<?php echo Yii::T('common', 'mediaSource') ?>：<?php  echo Html::dropDownList('media_source', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('media_source', [])),
    $mediaSourceList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'media_source']); ?>&nbsp;
<?php echo Yii::T('common', 'packageName') ?>：<?php  echo Html::dropDownList('package_name', ArrayHelper::htmlEncode(Yii::$app->getRequest()->get('package_name', [])),
    $packageNameList,['class' => 'form-control channel-select', 'multiple' => 'multiple', 'id' => 'package_name']); ?>&nbsp;
<input type="submit" name="search_submit" value="<?php echo Yii::T('common', 'search') ?>" class="btn">
&nbsp;&nbsp;<input type="submit" name="submitcsv" value="<?php echo Yii::T('common', 'export') ?>csv" onclick="$(this).val('export');return true;" class="btn">
&nbsp;(<?php echo Yii::T('common', 'Update every 5 minutes') ?>)
<?php ActiveForm::end(); ?>

<form name="listform" method="post">
    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th style="text-align:center;border-right:1px solid #A9A9A9;"></th>
            <th colspan="3" style="text-align:center;border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'All users') ?></th>
            <th colspan="3" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'New users') ?></th>
            <th colspan="3" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Old users') ?></th>
            <th colspan="3" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All new self new') ?></th>
            <th colspan="3" style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'All old self new') ?></th>
            <th colspan="3" style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'All old self old') ?></th>
        </tr>
        <tr class="header">
            <!-- 借款信息 -->
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Repayment date') ?></th>

            <!-- 所有用户 -->
            <th><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>

            <!-- 新用户 -->
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>

            <!-- 老用户 -->
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>

            <!-- 全新本新 -->
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>

            <!-- 全老本新 -->
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:red"><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="text-align:center;color:red;border-right:1px solid red;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>

            <!-- 全老本老 -->
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Expire number') ?><br/><?php echo Yii::T('common', 'Expire money') ?></th>
            <th style="text-align:center;color:blue"><?php echo Yii::T('common', 'Have a single repayment number') ?><br/><?php echo Yii::T('common', 'Cumulative repayment amount') ?></th>
            <th style="text-align:center;color:blue;border-right:1px solid blue;"><?php echo Yii::T('common', 'Singular with delay number') ?><br/><?php echo Yii::T('common', 'Delay money') ?></th>
        </tr>
        <tr class="hover">
            <!-- 借款信息 -->
            <td style="border-right:1px solid #A9A9A9;"><?php echo Yii::T('common', 'Summary information') ?></td>
            <!-- 所有用户 -->
            <td class="td25">
                <?php echo isset($total_info['expire_num_0']) ? $total_info['expire_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_0'])?number_format(floor($total_info['expire_money_0'])/100):0; ?>
            </td><!--到期金额-->
            <td class="td25">
                <?php echo isset($total_info['repay_num_0']) ? $total_info['repay_num_0'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_0'])?number_format(floor($total_info['repay_money_0'])/100):0; ?>
            </td><!--累计已还-->
            <td class="td25" style="border-right:1px solid #A9A9A9;">
                <?php echo empty($total_info['delay_num_0']) ? '-' : $total_info['delay_num_0']; ?><br/>
                <?php echo empty($total_info['delay_money_0']) ? '-' : number_format($total_info['delay_money_0']/100); ?>
            </td>

            <!-- 新用户 -->
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['expire_num_1']) ? $total_info['expire_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_1'])?number_format(floor($total_info['expire_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['repay_num_1']) ? $total_info['repay_num_1'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_1'])?number_format(floor($total_info['repay_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                <?php echo empty($total_info['delay_num_1']) ? '-' : $total_info['delay_num_1']; ?><br/>
                <?php echo empty($total_info['delay_money_1']) ? '-' : number_format($total_info['delay_money_1']/100); ?>
            </td>

            <!-- 老用户 -->
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['expire_num_2']) ? $total_info['expire_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_2'])?number_format(floor($total_info['expire_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['repay_num_2']) ? $total_info['repay_num_2'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_2'])?number_format(floor($total_info['repay_money_2'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                <?php echo empty($total_info['delay_num_2']) ? '-' : $total_info['delay_num_2']; ?><br/>
                <?php echo empty($total_info['delay_money_2']) ? '-' : number_format($total_info['delay_money_2']/100); ?>
            </td>

            <!-- 全新本新 -->
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['expire_num_3']) ? $total_info['expire_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_3'])?number_format(floor($total_info['expire_money_3'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['repay_num_3']) ? $total_info['repay_num_3'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_3'])?number_format(floor($total_info['repay_money_3'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                <?php echo empty($total_info['delay_num_3']) ? '-' : $total_info['delay_num_3']; ?><br/>
                <?php echo empty($total_info['delay_money_3']) ? '-' : number_format($total_info['delay_money_3']/100); ?>
            </td>

            <!-- 全老本新 -->
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['expire_num_4']) ? $total_info['expire_num_4'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_4'])?number_format(floor($total_info['expire_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red">
                <?php echo isset($total_info['repay_num_4']) ? $total_info['repay_num_4'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_4'])?number_format(floor($total_info['repay_money_4'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                <?php echo empty($total_info['delay_num_4']) ? '-' : $total_info['delay_num_4']; ?><br/>
                <?php echo empty($total_info['delay_money_4']) ? '-' : number_format($total_info['delay_money_4']/100); ?>
            </td>

            <!-- 全老本老 -->
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['expire_num_5']) ? $total_info['expire_num_5'] : 0; ?><br/>
                <?php echo isset($total_info['expire_money_5'])?number_format(floor($total_info['expire_money_1'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue">
                <?php echo isset($total_info['repay_num_5']) ? $total_info['repay_num_5'] : 0; ?><br/>
                <?php echo isset($total_info['repay_money_5'])?number_format(floor($total_info['repay_money_5'])/100):0; ?>
            </td>
            <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                <?php echo empty($total_info['delay_num_5']) ? '-' : $total_info['delay_num_5']; ?><br/>
                <?php echo empty($total_info['delay_money_5']) ? '-' : number_format($total_info['delay_money_5']/100); ?>
            </td>
        </tr>
        <?php foreach ($info as $key=> $value): ?>
            <tr class="hover" style="<?php echo date('w', $value['unix_time_key']) == 0 || date('w', $value['unix_time_key']) == 6 ?'background:#F5F9FD':'';?>">
                <!-- 借款信息 -->
                <td class="td25" style="border-right:1px solid #A9A9A9;"><?php echo date('n-j',strtotime($key)); ?></td>
                <!-- 所有用户 -->
                <td class="td25">
                    <?php echo isset($value['expire_num_0']) ? $value['expire_num_0'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_0'])?number_format(floor($value['expire_money_0'])/100):0; ?>
                </td>
                <td class="td25">
                    <?php echo isset($value['repay_num_0']) ? $value['repay_num_0'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_0'])?number_format(floor($value['repay_money_0'])/100):0; ?>
                </td>
                <td class="td25" style="border-right:1px solid #A9A9A9;">
                    <?php echo empty($value['delay_num_0']) ? '-' : $value['delay_num_0']; ?><br/>
                    <?php echo empty($value['delay_money_0']) ? '-' : number_format($value['delay_money_0']/100); ?>
                </td>

                <!-- 新用户 -->
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['expire_num_1']) ? $value['expire_num_1'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_1'])?number_format(floor($value['expire_money_1'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['repay_num_1']) ? $value['repay_num_1'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_1'])?number_format(floor($value['repay_money_1'])/100):0; ?>
                </td>

                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                    <?php echo empty($value['delay_num_1']) ? '-' : $value['delay_num_1']; ?><br/>
                    <?php echo empty($value['delay_money_1']) ? '-' : number_format($value['delay_money_1']/100); ?>
                </td>

                <!-- 老用户 -->
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['expire_num_2']) ? $value['expire_num_2'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_2'])?number_format(floor($value['expire_money_2'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['repay_num_2']) ? $value['repay_num_2'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_2'])?number_format(floor($value['repay_money_2'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                    <?php echo empty($value['delay_num_2']) ? '-' : $value['delay_num_2']; ?><br/>
                    <?php echo empty($value['delay_money_2']) ? '-' : number_format($value['delay_money_2']/100); ?>
                </td>

                <!-- 全新本新 -->
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['expire_num_3']) ? $value['expire_num_3'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_3'])?number_format(floor($value['expire_money_3'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['repay_num_3']) ? $value['repay_num_3'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_3'])?number_format(floor($value['repay_money_3'])/100):0; ?>
                </td>

                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                    <?php echo empty($value['delay_num_3']) ? '-' : $value['delay_num_3']; ?><br/>
                    <?php echo empty($value['delay_money_3']) ? '-' : number_format($value['delay_money_3']/100); ?>
                </td>

                <!-- 全老本新 -->
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['expire_num_4']) ? $value['expire_num_4'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_4'])?number_format(floor($value['expire_money_4'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red">
                    <?php echo isset($value['repay_num_4']) ? $value['repay_num_4'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_4'])?number_format(floor($value['repay_money_4'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:red;border-right:1px solid red;">
                    <?php echo empty($value['delay_num_4']) ? '-' : $value['delay_num_4']; ?><br/>
                    <?php echo empty($value['delay_money_4']) ? '-' : number_format($value['delay_money_4']/100); ?>
                </td>

                <!-- 全老本老 -->
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['expire_num_5']) ? $value['expire_num_5'] : 0; ?><br/>
                    <?php echo isset($value['expire_money_5'])?number_format(floor($value['expire_money_5'])/100):0; ?>
                </td>
                <td class="td25" style="text-align:center;color:blue">
                    <?php echo isset($value['repay_num_5']) ? $value['repay_num_5'] : 0; ?><br/>
                    <?php echo isset($value['repay_money_5'])?number_format(floor($value['repay_money_5'])/100):0; ?>
                </td>

                <td class="td25" style="text-align:center;color:blue;border-right:1px solid blue;">
                    <?php echo empty($value['delay_num_5']) ? '-' : $value['delay_num_5']; ?><br/>
                    <?php echo empty($value['delay_money_5']) ? '-' : number_format($value['delay_money_5']/100); ?>
                </td>
            </tr>
        <?php endforeach; ?>

    </table>
    <?php if (empty($info)): ?>
        <div class="no-result"><?php echo Yii::T('common', 'No record') ?></div>
    <?php endif; ?>
</form>