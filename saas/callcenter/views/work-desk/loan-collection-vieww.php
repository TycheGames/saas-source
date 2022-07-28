<?php

use common\models\enum\Education;
use common\models\enum\Gender;
use common\models\enum\Marital;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use common\models\order\UserLoanOrder;
use common\models\product\ProductSetting;

$BASEURL = Yii::$app->getRequest()->getBaseUrl();
$this->title = 'Collection management system';
?>

<script type="text/javascript" src="<?php echo $this->baseUrl; ?>/js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>

<style>
    .operation-panel .back {
        height: 40px;
        line-height: 40px;
        font-size: 14px;
        text-align: center;
        color: white;
        width: 100px;
        border-radius: 5px;
        margin: 20px 20px;
        background-color: #090;
        display: inline-block;
    }
    .part-left {
        width: 100%;
        float: left;
        /*margin-bottom: 40px;*/
    }
    .loan-collection-records .list-records{
        width: 100%;
        height: 400px;
    }

    .loan-collection-operation {
        margin-top: 30px;
    }

    .operation-panel {
        margin-top: 20px;
    }

    .operation-panel .submit {
        height: 40px;
        line-height: 40px;
        font-size: 14px;
        text-align: center;
        color: white;
        background-color: #090;
        width: 100px;
        border-radius: 5px;
        margin: 20px 20px;
    }
    .table th{
        border:1px solid #ddd;
        background: #f5f5f5 none repeat scroll 0 0;
        font-weight: bold;
        width:100px
    }
    .table td{
        border:1px solid #ddd;
    }

    .base_personinfo tr:first-child td, .base_personinfo tr:nth-child(2) td{
        width: 14%;
    }

    .base_personinfo th {
        min-width: 80px;
        width: 7%;
    }
    .base_personinfo{
        margin:25px;
    }
    .tb2 th{
        /*border:1px solid #ddd;*/
        border-top: none;
        /*background: #f5f5f5 none repeat scroll 0 0;*/
        font-weight: bold;
        width: 150px;
        text-indent: 10px;
    }
    .tb2 td{
        /*border:1px solid #ddd;*/
        border-top: none;
    }
    .tb2 {
        border: none;
    }
    .template{
        display: none;
    }
    .content{
        display: none;
    }
</style>

<style type="text/css">
    .tabDivv{width:100%;height:435px;margin-top:15px;border:1px solid #ccc;}
    .tab_ul{ margin:0px auto;/* padding-left:20px;*/ /* width:228px; */ height:30px; background-color:#F3F3F3;/*border:1px solid #B1C9E4;*/ border-bottom:0;font-family: Microsoft Yahei; border: 1px solid #ddd; border-bottom: none;} 
    .tab_ul li{ float:left; display:inline; /*margin:5px 0 0 5px;*/ margin:0;width:150px; height:30px;border-right: 1px solid #CDCDCD;}
    .tab_ul li a{ display:block;/*width:46px;*/ height:30px; line-height:30px; text-align:center;font-size:13px; color:#000000; text-decoration:none;}
    .tab_ul li:hover{color:#5F0082; /*font-weight:bold;*/}
    .active{/*font-weight: bold;*//*border:1px solid #ccc;*/border-bottom:0;background: #fff;}
    .contactinfo{/*border:1px solid #ccc;*//*height:330px;*/padding:2px 0;width:100%;}
    .contactinfo li,.contactinfo_select li{float:left;display:inline;width:19%;height:30px;line-height: 30px;}
    #calllogtb{
        width:100%;
        height:402px;
        overflow: scroll;
    }
    .contactinfo{
        width:100%;
        height:402px;
        overflow: scroll;
    }
    #calllogtb li{float:left;display:inline;height:30px;line-height: 30px}

    .tip{
        position: relative;
    }
    .tip span{
       display: none;
       position: absolute;
       top: 45%;
       left: 45%;

    }
    .tip:hover span{
        display: block;
        border: 1px solid #cdcdcd;
        background-color: #fbfb98;
        /*width: 600px;*/
        top:10px;
        left: 5px;
        z-index: 999;
        font-family: "Microsoft YaHei",Arial,Helvetica,sans-serif,"宋体";;
        padding: 2px;
    }
    #tabDivv_ul{border:none;}
    .defaul{border:1px solid #ddd;}
    input[name="contactsphone"]{
        display: inline-block;
        vertical-align: middle;
    }
    input[name="contactphone"]{
        margin-top:9px;
    }
</style>
<style type="text/css">
    #record_tab{
        width:98%;
        margin:0 auto;
    }
    #record_div{
        overflow: scroll;
        height:379px;
        position: relative;
    }
</style>
<!--info-->
<div class="loan_person_div">
    <div class="part-left loan-person-info">
        <ul class="tab_ul"> 
            <li><a href="#ul1">Basic information</a></li>
            <li><a href="#ul2" id="huankuan_info">Payment information</a></li>
        </ul> 
    </div>
    <div class="tabDiv"> 
        <div class="baseInfo defaul" id="ul1">
            <table class="tb tb2 fixpadding base_personinfo">
                <tr>
                    <th>Name</th><td><?= Html::encode($personInfo['loanPerson']['name'] ?? '-')?></td>
                    <th>Phone</th><td><a href="javascript:;"  onclick="callPhone(<?=Html::encode($personInfo['loanPerson']['phone'] ?? '-')?>);"><?=Html::encode($personInfo['loanPerson']['phone'] ?? '-')?></a></td>
                    <th>Sex</th><td><?= Html::encode(Gender::$map[$personInfo['loanPerson']['gender']] ?? '-')?></td>
                    <th>Birthday</th><td><?= Html::encode($personInfo['loanPerson']['birthday'] ?? '-')?></td>
                </tr>
                <tr>
                    <th>Pan code</th><td>**********</td>
                    <th>Aadhaar number</th><td>************</td>
                    <th>Educated</th><td><?= Html::encode(Education::$map[$personInfo['userWorkInfos']['educated']] ?? '-') ?></td>
                    <th>Marital</th><td><?= Html::encode(Marital::$map[$personInfo['userBasicInfo']['marital_status']] ?? '-')?></td>
                </tr>
                <tr>
                    <th>Residential address</th>
                    <td>
                        <?= Html::encode($personInfo['userWorkInfos']['residential_address1'] ?? '-' )?>
                        <?= Html::encode($personInfo['userWorkInfos']['residential_address2'] ?? '-') ?>
                    </td>
                    <th>Company name</th><td><?= Html::encode($personInfo['userWorkInfos']['company_name'] ?? '-')?></td>
                    <th>Company address</th><td><?= Html::encode($personInfo['userWorkInfos']['company_address1'] ?? '-')?> <?= Html::encode($personInfo['userWorkInfos']['company_address2'] ?? '-')?></td>
                    <th>Company phone</th><td><?= Html::encode($personInfo['userWorkInfos']['company_phone'] ?? '-')?></td>
                </tr>
                <tr>
                    <th>Loan Product</th>
                    <?php
                        $fromApp = $userLoanOrder->clientInfoLog['package_name'] ?? '--';
                        if($userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
                            $userLoanOrder->clientInfoLog['package_name'];
                            $sourceFrom = explode('_', $userLoanOrder->clientInfoLog['app_market'])[1] ?? '--';
                            $productName = ProductSetting::getLoanExportProductName($fromApp, $sourceFrom);
                            $fromApp = $sourceFrom;
                        }else{
                            $productName = $userLoanOrder->productSetting['product_name'] ?? '--';
                        }
                    ?>
                    <td><?= $productName; ?></td>
                    <th>From APP</th>
                    <td><?= $fromApp;?>
                    </td>
                    <!--此处有删除-->
                    <th>Permanent address</th>
                    <?php if($delayData['delaySwitch']):?>
                        <td><?=Html::encode($personInfo['permanentAddress'] ?? '--') ?></td>
                        <th>Minimum amount for applying partial deferral</th>
                        <td colspan="2"><?=Html::encode($delayData['delayMoney'] ?? '--') ?></td>
                    <?php else:;?>
                        <td colspan="4"><?=Html::encode($personInfo['permanentAddress'] ?? '--' )?></td>
                    <?php endif;?>
                </tr>
                <?php if($extendData['extendSwitch'] || $userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES):?>
                    <tr>
                        <?php if($extendData['extendSwitch']):?>
                            <th>Minimum amount for applying extend</th>
                            <td><?=Html::encode($extendData['extendMoney'] ?? '--') ?></td>
                        <?php endif;?>
                         <?php if($userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES):?>
                            <th>APP url</th>
                            <td>
                                <?php
                                    $fromApp = explode('_',$userLoanOrder->clientInfoLog['app_market'])[1] ?? '--';
                                    echo Yii::$app->params['appUrl'][$fromApp] ?? '';
                                ?>
                            </td>
                        <?php endif;?>
                    </tr>
                <?php endif;?>
            </table>
        </div>
        <div class="repayInfo defaul" id="ul2">
            <table class="tb tb2 fixpadding base_personinfo">
                <tr>
                    <th>Loan order id</th><td name="repay_order_id">loading</td>
                    <th>Loan time</th><td name="repay_loan_time">loading</td>
                    <th>Loan term</th><td name="repay_loan_term">loading</td>
                    <th>Expire time of repay</th><td name="repay_plan_fee_time">loading</td>
                </tr>
                <tr>
                    <th>Total money</th><td name="repay_total_money">loading</td>
                    <th>Principal</th><td name="repay_amount">loading</td>
                    <th>Interests</th><td name="interests_money">loading</td>
                    <th>Overdue fee</th><td name="repay_overdue_fee">loading</td>
                </tr>
                <tr>
                    <th>Repaid amount</th><td name="repay_true_total_money">loading</td>
                    <th>Coupon money</th><td name="repay_coupon_money">loading</td>
                    <th>Delay reduce money</th><td name="delay_reduce_amount">loading</td>
                    <th>Remain amount</th><td name="repay_surplus_money">loading</td>
                </tr>
                <tr>
                    <th>Overdue day</th><td name="repay_overdue_day">loading</td>
                    <th>Cost fee</th><td name="repay_cost_fee">loading</td>
                    <?php if(!isset($personInfo['userBankAccounts']['account'])) :?>
                    <th>Bank account</th><td>No record</td>
                    <?php else :?>
                    <th>Bank account</th>
                        <td><?= Html::encode($personInfo['userBankAccounts']['bank_name']) ?>&nbsp;

                        </td>
                    <?php endif;?>
                    <th>Payment status</th><td name="repay_ulor_status">loading</td>
                </tr>
            </table>
            <?php if($historyOrder):?>
                <div>
                    <label style="margin-left: 40px;font-weight: bold">Historical loan record</label>
                    <table id='historyTable' border="1px;" width="1000px" style="margin-bottom: 10px;margin-left: 140px"  cellpadding="11px">
                        <tr>
                            <th style="font-weight: bold; text-align: center">Loan order id</th>
                            <th style="font-weight: bold;text-align: center">Borrowing time</th>
                            <th style="font-weight: bold;text-align: center">Borrowing balance</th>
                            <th style="font-weight: bold;text-align: center">Repayment time</th>
                            <th style="font-weight: bold;text-align: center">Repayment amount</th>
                            <th style="font-weight: bold;text-align: center">Is overdue</th>
                            <th style="font-weight: bold;text-align: center">Overdue days</th>
                            <th style="font-weight: bold;text-align: center">Order level</th>
                        </tr>
                        <?php foreach ($historyOrder as $order) :?>
                            <?php $color = $order['is_overdue']?'red':'';?>
                            <tr>
                                <td><?php echo Html::encode($order['order_id'])?></td>
                                <td><?php echo Html::encode(date('Y-m-d H:i',$order['created_at']))?></td>
                                <td><?php echo Html::encode($order['principal']/100)?></td>
                                <td><?php echo Html::encode(date('Y-m-d H:i',$order['closing_time']))?></td>
                                <td><?php echo Html::encode($order['true_total_money']/100)?></td>
                                <td style="color: <?php echo $color?>"><?php echo Html::encode($order['is_overdue']>0?'yes':'no')?></td>
                                <td style="color: <?php echo $color?>"><?php echo Html::encode($order['overdue_day'])?></td>
                                <td>
                                    <?php if(0 < $order['overdue_day'] &&  $order['overdue_day'] <= 11) :?>
                                        s1
                                    <?php endif;?>
                                    <?php if(11 < $order['overdue_day'] &&  $order['overdue_day'] <= 30) :?>
                                        s2
                                    <?php endif;?>
                                    <?php if(30 < $order['overdue_day'] &&  $order['overdue_day'] < 60) :?>
                                        m1-m2
                                    <?php endif;?>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
<!-- 增加联系人div 可以多选 -->
<div class="tabDivv">
    <div class="part-left ">
        <ul class="tab_ul" id="tabDivv_ul">
            <li><a href="#ul6" data-type="csjl" class="sel_con_type">collection records</a></li>
            <li><a href="#ul7" data-type="lxr" class="sel_con_type">contacts</a></li>
            <li id="txl_li"><a href="#ul8" data-type="txl" class="sel_con_type">address book</a></li>
            <input type="hidden" name="select_con_type" value="csjl">
        </ul>
    </div>
    <div class="tabDiv">
        <div class="loan-collection-records defaul" id="ul6" style="border-bottom: none;">
            <input type="hidden" class="isset_loan_collection_record" value="<?php if (empty($loan_collection_record)){echo 0;}else{echo 1;} ?>" >
            <div class="csjl_show">
                <?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin:5px 20px 0px;'],'id'=>'csjl_filter']); ?>
                Name of collector：<input type="text" value="<?php echo Html::encode(Yii::$app->getRequest()->get('collection_name', '')); ?>" name="collection_name" class="txt" style="width:150px;" placeholder="Names can be repeated">&nbsp;
                Order level：<?php echo Html::dropDownList('order_level', Html::encode(Yii::$app->getRequest()->get('order_level', 0)), LoanCollectionOrder::$level,array('prompt' => '-All level-')); ?>&nbsp;
                Collection status：<?php echo Html::dropDownList('status', Html::encode(Yii::$app->getRequest()->get('status', 0)), LoanCollectionOrder::$status_list,array('prompt' => '-All status-')); ?>&nbsp;
                Operate type：<?php echo Html::dropDownList('operate_type', Html::encode(Yii::$app->getRequest()->get('operate_type', 0)), LoanCollectionRecord::$label_operate_type,array('prompt' => '-All status-')); ?>&nbsp;
                <input type="submit" name="csjl_filter_submit" value="Search" class="btn">
                <?php $form = ActiveForm::end(); ?>
                <div id="record_div">
                <table id="record_tab">
                    <tr class="header">
                        <th style="text-align: center;min-width:30px;">Choose</th>
                        <th style="text-align: center;min-width: 37px;">Contacts</th>
                        <th style="min-width: 25px;">Relation</th>
                        <th style="width: 115px;">Phone</th>
                        <th style="min-width: 55px;">Order level</th>
                        <th style="min-width: 34px;">Operate type</th>
                        <th style="min-width: 75px;width:130px;">Promise repay time</th>
                        <th style="min-width: 180px;">Send content</th>
                        <th style="min-width: 35px;">Contact status</th>
                        <th style="min-width: 35px;">Contact result</th>
                        <th >Remark</th>
                        <th style="min-width: 35px;">Collection status</th>
                        <th style="min-width: 75px; max-width: 155px;">Collection time</th>
                        <th style="width: 60px;text-align: center;">Name of collector</th>
                    </tr>
                    <tbody id="csjltab">
                    <?php if(!empty($loan_collection_record)):?>
                        <?php $i=0;?>
                    <?php foreach ($loan_collection_record as $key=>$val): ?>
                        <?php
                            $i++;
                            if ($i == 14) break;
                        ?>
                        <?php if(count($val)>1):?>
                            <?php if($i%2==0):?>
                            <tr style="cursor:pointer;height:35px;background: #F5F5F5;" onclick="openShutManager(this,<?=Html::encode($key);?>)">
                            <?php else:?>
                            <tr style="cursor:pointer;height:35px;" onclick="openShutManager(this,<?=Html::encode($key);?>)">
                            <?php endif;?>
                                <td style="width:30px;text-align: left;"><span class="tubiao<?=Html::encode($key);?>">[+]</span></td>

                                <td style="text-align: center;">batch</td>
                                <td>batch</td>
                                <td>batch</td>
                                <td><?php echo Html::encode(isset(LoanCollectionOrder::$level[$val[0]['order_level']])?LoanCollectionOrder::$level[$val[0]['order_level']]:"--"); ?></td>
                                <td><?php echo Html::encode(isset(LoanCollectionRecord::$label_operate_type[$val[0]['operate_type']])?LoanCollectionRecord::$label_operate_type[$val[0]['operate_type']]:"--");  ?></td>
                                <td><?php echo Html::encode(empty($val[0]['promise_repayment_time']) ? '--' : date('Y-m-d H:i:s',$val[0]['promise_repayment_time'])); ?></td>
                                <td  >
                                    <?php
                                    if (mb_strlen($val[0]['content'])>65) {
                                        $content = htmlspecialchars($val[0]['content']);
                                        echo "<div class='tip'>".mb_substr($val[0]['content'],0,65).'...'."<span>{$content}</span></div>";
                                    }else{
                                        echo Html::encode(empty($val[0]['content']) ? '--' : $val[0]['content']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo Html::encode(!empty($val[0]['is_connect'])?LoanCollectionRecord::$is_connect[$val[0]['is_connect']]:"--"); ?></td>
                                <td><?php echo Html::encode(!empty($val[0]['risk_control'])?LoanCollectionRecord::$risk_controls[$val[0]['risk_control']]:"--"); ?></td>
                                <td>
                                    <div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;"><?=Html::encode($val[0]['remark']);?></div><span ><?=Html::encode($val[0]['remark']);?></span></div>
                                </td>
                                <td><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$val[0]['order_state']])?LoanCollectionOrder::$status_list[$val[0]['order_state']]:"--");  ?></td>
                                <td><?php echo Html::encode(!empty($val[0]['operate_at'])?date("Y-m-d H:i:s",$val[0]['operate_at']):"--"); ?></td>
                                <td style="text-align: center;"><?php echo Html::encode(isset($val[0]['operator_name']) ? $val[0]['operator_name']:"--");?></td>
                            </tr>
                        <?php endif;?>

                        <?php foreach($val as $value):?>
                            <?php if($i%2 ==0):?>
                                <?php if(count($val)>1):?>
                                <tr style="height:35px;display: none;background: #F5F5F5;" class="box<?=Html::encode($key);?>">
                                <?php else:?>
                                <tr style="height:35px;background: #F5F5F5;" >
                                <?php endif;?>
                            <?php else:?>
                                <?php if(count($val)>1):?>
                                <tr style="height:35px;display: none;" class="box<?=Html::encode($key);?>">
                                <?php else:?>
                                <tr style="height:35px;" >
                                <?php endif;?>
                            <?php endif;?>
                                <td style="width:30px;text-align: right;"><input class="contactphone" type="checkbox" name="contactedphone" leix="csjl" value="<?=Html::encode($value['contact_phone']);?>" data-selected="no"></td>
                                <td style="text-align: center;"><?php echo Html::encode($value['contact_name']); ?></td>
                                <td><?php echo Html::encode($value['relation']); ?></td>
                                <td><a href="javascript:;" onclick="callPhone(<?php echo Html::encode($value['contact_phone']); ?>)"><?php echo Html::encode($value['contact_phone']); ?></a></td>
                                <td><?php echo Html::encode(isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:"--"); ?></td>
                                <td><?php echo Html::encode(isset(LoanCollectionRecord::$label_operate_type[$value['operate_type']])?LoanCollectionRecord::$label_operate_type[$value['operate_type']]:"--");  ?></td>
                                <td><?php echo Html::encode(empty($value['promise_repayment_time']) ? '--' : date('Y-m-d H:i:s',$value['promise_repayment_time'])); ?></td>
                                <td  >
                                    <?php
                                    if (mb_strlen($value['content'])>65) {
                                        $content = htmlspecialchars($value['content']);
                                        echo "<div class='tip'>".mb_substr($value['content'],0,65).'...'."<span>{$content}</span></div>";
                                    }else{
                                        echo Html::encode(empty($value['content']) ? '--' : $value['content']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo Html::encode(!empty($value['is_connect'])?LoanCollectionRecord::$is_connect[$value['is_connect']]:"--"); ?></td>
                                <td><?php echo Html::encode(!empty($value['risk_control'])?LoanCollectionRecord::$risk_controls[$value['risk_control']]:"--"); ?></td>
                                <td>
                                    <div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;"><?=Html::encode($value['remark']);?></div><span ><?=Html::encode($value['remark']);?></span></div>
                                </td>
                                <td><?php echo Html::encode(isset(LoanCollectionOrder::$status_list[$value['order_state']])?LoanCollectionOrder::$status_list[$value['order_state']]:"--");  ?></td>
                                <td><?php echo Html::encode(!empty($value['operate_at'])?date("Y-m-d H:i:s",$value['operate_at']):"--"); ?></td>
                                <td style="text-align: center);"><?php echo  Html::encode(isset($value['operator_name']) ? $value['operator_name']:"--");?></td>
                            </tr>
                        <?php endforeach;?>
                    <?php endforeach; ?>
                    <?php endif;?>
                    </tbody>
                </table>
                <?php if(!empty($loan_collection_record) && count($loan_collection_record)>11):?>
                    <div id="loading" style="text-align: center;position: absolute;left:49%;">
                        <img src="<?=Url::to('@web/image/loading.gif');?>" alt="Loading…" />
                    </div>
                <?php endif;?>
                </div>
            </div>
            <div class="csjl_hide">
                <div class="no-result">No record</div>
            </div>
        </div>
        <div class="calllog defaul" id="ul7" style="border-bottom: none;">

                <ul class="contactinfo">

                </ul>
        </div>
        <div class="contact defaul" id="ul8" style="border-bottom: none;">
                <div id="has_call_log">
                    <ul id="calllogtb">

                    </ul>
                </div>

                <div class="no-result" id="no_call_log">No record&nbsp;&nbsp;&nbsp;<a href="javascript:;" id="getContactList">loading data</a></div>
        </div>

        <div class="contact defaul" id="ul13" style="border-bottom: none;">
                <div id="has_call_log2">
                    <ul id="calllogtb2"  style="margin: 40px;">


                    </ul>
                </div>
        </div>
    </div>
</div>

<!--  -->
<div class="loan_person_div sms_phone_cuishou">
    <div class="part-left loan-collection-operate">
        <ul class="tab_ul">
            <li><a href="#ul5">Collection operation</a></li>

        </ul>
    </div>
    <div class="tabDiv">
        <div class="loan-collection-operation defaul" id="ul5">
            <div class="operation-panel">

                <table class="tb tb2 fixpadding">
                <tr>
                    <th>Operation type</th>
                    <td>
                    <?= Html::dropDownList(
                                'collection_type',
                                LoanCollectionRecord::OPERATE_TYPE_CALL,
                                LoanCollectionRecord::$label_operate_type,
                                [
                                    'onchange' => 'onCollectionTypeChange($(this).val())'
                                ]
                            )
                        ?>
                    </td>
                </tr>
                <tr id="is_connects" style="height: 50px;">
                    <th>Connect status</th>
                    <td id="connects"><?php echo Html::radioList('is_connect','', LoanCollectionRecord::$is_connect
                        ); ?>
                    </td>
                </tr>
                <tr id="risk" style="border-top: 1px dashed #ccc;" >
                </tr>
                <tr class="cn_tr">
                    <th>Promise repay time</th>
                    <td>
                        <?= Html::textInput('promise_repayment_time','',['style'=>"width:180px;",'id'=>"promise_repayment_time",'onfocus'=>"WdatePicker({startDate:'%y/%M/%d %H:%m',dateFmt:'yyyy-MM-dd H:m:00',alwaysUseStartDate:true,readOnly:true})"]); ?>
                    </td>
                </tr>
                <tr class="yh_tr">
                    <th>User Amount(Rupee)</th>
                    <td>
                        <?= Html::textInput('user_amount','',['style'=>"width:180px;",'id'=>"user_amount"]); ?>
                    </td>
                </tr>
                <tr class="yh_tr">
                    <th>User Utr</th>
                    <td>
                        <?= Html::textInput('user_utr','',['style'=>"width:180px;",'id'=>"user_utr"]); ?>
                    </td>
                </tr>
                <tr class="yh_tr">
                    <th>User Pic</th>
                    <td> <?= Html::fileInput('user_pic[]','', ['id' => 'user_pic','multiple' => true]); ?></td>
                </tr>
                <tr class="template" >
                    <th>SMS template</th>
                    <td><?php
                        echo Html::dropDownList(
                            'template',
                            '',
                            $smsTemplateList['name'],
                            [
                                'prompt'=>'-select template-',
                                'onchange' => 'onTemplateChange($(this).val())',
                            ]
                        )
                        ?>
                    </td>
                </tr>
                <tr class="content">
                    <th style="color:red;font-weight: bold">Send Content</th>
                    <td><?= Html::textarea('content', 'Please select an SMS template', ['style' => 'background-color:white', 'rows' => 6, 'cols' => 50,'readonly'=>true])?></td>
                </tr>

<!--                <tr class="cuishou_phone">-->
<!--                    <th >请致电</th>-->
<!--                    <td><input type="text" name="call_this_phone" placeholder="填写你要求借款人联系的电话号码 替换$phone" style="width:340px;"><font color="red">借款人回电用</font></td>-->
<!--                </tr>-->

                <tr class="remark">
                    <th>Remark</th>
                    <td><?= Html::textarea('remark','', ['style' => 'background-color:white', 'rows' => 3, 'cols' => 50])?></td>
                </tr>
                </table>
                <button class="submit" style="cursor: pointer;border:none;">collection</button>
                <!--<a href="javascript:history.go(-1)" class="back" style="cursor: pointer;border:none;">返回</a>-->
                <a href="javascript:history.go(-1)" class="back" style="background: #333;border:none;">go back</a>
            </div>
        </div>
    </div>

</div>
<?php
    //处理电话号码
    $level_id = implode(',',array_keys(LoanCollectionOrder::$level));
    $level_title = implode(',',LoanCollectionOrder::$level);
    $status_id = implode(',',array_keys(LoanCollectionOrder::$status_list));
    $status_title = implode(',',LoanCollectionOrder::$status_list);
    $label_operate_type_id = implode(',',array_keys(LoanCollectionRecord::$label_operate_type));
    $label_operate_type_title = implode(',',LoanCollectionRecord::$label_operate_type);
    $risk_id = implode(',',array_keys(LoanCollectionRecord::$risk_controls));
    $risk_title = implode(',',LoanCollectionRecord::$risk_controls);
    $connect_id = implode(',',array_keys(LoanCollectionRecord::$is_connect));
    $connect_title = implode(',',LoanCollectionRecord::$is_connect);
?>
<!-- 需要刷新催收记录的地方有：点击催收按钮、筛选条件催收记录、滚动刷新 -->
<script type="text/javascript">
    //点击身份证放大
    $('.gallery-pic').click(function(){
        $.openPhotoGallery(this);
    });
    $(window).resize(function(){
        $('#J_pg').height($(window).height());
        $('#J_pg').width($(window).width());
    });
    //点击申请后的操作
    $('a[name=num_apply]').click(function(evt){
        var e = window.event || evt;
        e.preventDefault();
        var url = $(this).attr('href');
        $.get(url,function(response){
            if (response.success == 1) {
                $('span[name=num_apply_span]').html('');
                $('span[name=num_apply_span]').html(response.msg);
            }else{
                $('span[name=num_apply_span]').css('color','red');
                $('span[name=num_apply_span]').val(response.msg);
            }
        },'json');
    });

    //选项卡
    $('a[href^=#ul]').click(function(){
        $(this).parent('li').addClass('active').siblings('li').removeClass('active');
        $(''+$(this).attr('href')+'').show().siblings('div[class$=defaul]').hide();
        return false;
    });
    window.onload = function(){
        $('ul[class=tab_ul]>li:first-of-type').addClass('active');
        $('div[class=tabDiv]>div:first-of-type').show().siblings('div').hide();
        var get_record = $('.isset_loan_collection_record').val();
        if (get_record == 1) {
            $('.csjl_show').show();
            $('.csjl_hide').hide();
        }else{
            $('.csjl_show').hide();
            $('.csjl_hide').show();
        }
    };

    //时间转换
    UnixToDate = function(unixTime) {
        unixTime = parseInt(unixTime);
        var time = new Date(unixTime * 1000);
        var ymdhis = "";
        ymdhis += time.getFullYear() + "-";
        ymdhis += (time.getMonth()+1) + "-";
        ymdhis += time.getDate();
        ymdhis += " " + time.getHours() + ":";
        ymdhis += time.getMinutes() + ":";
        ymdhis += time.getSeconds();
        return ymdhis;
    }

    var GetLength = function (str) {
        ///<summary>获得字符串实际长度，中文2，英文1</summary>
        ///<param name="str">要获得长度的字符串</param>
        var realLength = 0, len = str.length, charCode = -1;
        for (var i = 0; i < len; i++) {
            charCode = str.charCodeAt(i);
            if (charCode >= 0 && charCode <= 128) realLength += 1;
            else realLength += 2;
        }
        return realLength;
    };

    //js截取字符串，中英文都能用
    //如果给定的字符串大于指定长度，截取指定长度返回，否者返回源字符串。
    //字符串，长度

    /**
     * js截取字符串，中英文都能用
     * @param str：需要截取的字符串
     * @param len: 需要截取的长度
     */
    function cutstr(str, len) {
        var str_length = 0;
        var str_len = 0;
        str_cut = new String();
        str_len = str.length;
        for (var i = 0; i < str_len; i++) {
            a = str.charAt(i);
            str_length++;
            if (escape(a).length > 4) {
                //中文字符的长度经编码之后大于4
                str_length++;
            }
            str_cut = str_cut.concat(a);
            if (str_length >= len) {
                str_cut = str_cut.concat("...");
                return str_cut;
            }
        }
        //如果给定字符串小于指定长度，则返回源字符串；
        if (str_length < len) {
            return str;
        }
    }

    var lev = "<?=$level_id;?>";
    var lev_t = "<?=$level_title;?>";
    var levv = lev.split(',');
    var levvt = lev_t.split(',');
    var level = [];
    for (var i = 0; i < levv.length; i++) {
        level[levv[i]] = levvt[i];
    }
    var sta = "<?=$status_id;?>";
    var sta_t = "<?=$status_title;?>";
    var staa = sta.split(',');
    var staat = sta_t.split(',');
    var cui_status = [];
    for (var i = 0; i < staa.length; i++) {
        cui_status[staa[i]] = staat[i];
    }
    var lot = "<?=$label_operate_type_id;?>";
    var lot_t = "<?=$label_operate_type_title;?>";
    var lott = lot.split(',');
    var lottt = lot_t.split(',');
    var label_operate_type = [];
    for (var i = 0; i < lott.length; i++) {
        label_operate_type[lott[i]] = lottt[i];
    }
    var risk_one = "<?=$risk_id;?>";
    var risk_two = "<?=$risk_title;?>";
    var risk_ones = risk_one.split(',');
    var risk_twos = risk_two.split(',');
    var risk_control_type = [];
    for (var i = 0; i < risk_ones.length; i++) {
        risk_control_type[risk_ones[i]] = risk_twos[i];
    }

    var connect_one = "<?=$connect_id;?>";
    var connect_two = "<?=$connect_title;?>";
    var connect_ones = connect_one.split(',');
    var connect_twos = connect_two.split(',');
    var connect_control_type = [];
    for (var i = 0; i < connect_ones.length; i++) {
        connect_control_type[connect_ones[i]] = connect_twos[i];
    }

    //点击催收按钮触发 用于区别颜色
    var ch_color2 = 0;
    //点击催收按钮触发
    $(document).ready(function() {
        $('.loan-collection-operation .submit').click(function() {
            var formdata =new FormData();
            var submit_button = $(this);
            var phones = [];
            var cstype = $('input[name=select_con_type]').val();  //
            var risk_control = $("input[name='risk_control']:checked").val();   //沟通结果
            var is_connect = $("input[name='is_connect']:checked").val();       //是否接通
            var remark = $('textarea[name=remark]').val();//
            var promise_repayment_time=$('#promise_repayment_time').val();//
            var user_amount=$('#user_amount').val();//
            var user_utr=$('#user_utr').val();//
            //var user_pic[] = $('#user_pic').get(0).files;//

            $.each($('#user_pic').get(0).files, function(i,item){
                formdata.append("fileList[]",item);
            });
            var orderId = <?= $orderId ?>;  //
            var collectionType = $('select[name=collection_type]').val();
            if (cstype == 'txl') {
                $('input[name=contactphone]').each(function(){
                    if ($(this).prop('checked')) {
                        formdata.append("contact_phone[]",$(this).val());
                    }
                });
            }else if (cstype == 'lxr') {
                $('input[name=contactsphone]').each(function(){
                    if ($(this).prop('checked')) {
                        formdata.append("contact_phone[]",$(this).val());
                    }
                });
            }else if (cstype == 'csjl') {
                $('input[name=contactedphone]').each(function(){
                    if ($(this).prop('checked')) {
                        formdata.append("contact_phone[]",$(this).val());
                    }
                });
            }
            var templateId = -1;
            var content = '';
            if($('select[name=collection_type]').val()==<?= LoanCollectionRecord::OPERATE_TYPE_CALL?>)
            {
                if (formdata.getAll('contact_phone[]').length == 0) {
                    alert('please select contacts！');
                    return false;
                }
                if (!is_connect) {
                    alert('Please select whether or not to connect！');
                    return false;
                }
                if(!risk_control)
                {
                    alert('Please select the communication situation！');
                    return false;
                }
            }else if(collectionType ==<?= LoanCollectionRecord::OPERATE_TYPE_SMS ?>) {
                templateId = $('select[name=template]').val(); //
                if (templateId =='') {
                    alert('Please select the SMS template');
                    return false;
                }
                if (formdata.getAll('contact_phone[]').length > 35) {
                    alert('Up to 35 people can be selected！');
                    return false;
                }
                if (!confirm('send confirmation？')) {
                    return false;
                }
                risk_control = 0;
                is_connect = 0;
                content = $('textarea[name=content]').val();  //
            }

            submit_button.css('background','#777777');
            submit_button.attr('disabled','disabled');
            var ccc = [];
            formdata.append("cuishoutype",cstype);
            formdata.append("order_id",'<?=$orderId;?>');
            formdata.append("operate_type",collectionType);
            formdata.append("template_id",templateId);
            formdata.append("content",content);
            formdata.append("remark",remark);
            formdata.append("is_connect",is_connect);
            formdata.append("risk_control",risk_control);
            formdata.append("promise_repayment_time",promise_repayment_time);
            formdata.append("user_amount",user_amount);
            formdata.append("user_utr",user_utr);
            $.ajax({
                type: 'POST',
                url: "<?= Url::to(['work-desk/collect-loan'])?>",
                cache:false,
                traditional: true,
                contentType: false,
                processData: false,
                data: formdata,
                success: function(ret) {
                    alert(ret.msg);
                    if(ret.code != 0){
                        location.reload(true)
                        return;
                    }
                    submit_button.removeAttr('disabled');
                    submit_button.css('background','#090');
                    //刷新催收记录列表
                    //$('.list-records').attr('src', $('.list-records').attr('src'));
                    var url = "<?= Url::to(['work-desk/collection-view']);?>";
                    var data = {
                        order_id:orderId,
                        page_type:2
                    };
                    $.get(url,data,function(responses){
                        if (responses.success == 2) {
                            //重新加载催收记录
                            var record = responses.loan_collection_record;
                            var z = 0;
                            $.each(record,function(i,n){
                                ccc[z] = n;
                                z++;
                            });
                            $('.csjl_hide').hide();
                            $('.csjl_show').show();
                            var real_color = '';
                            if (ch_color2%2 == 0) {
                                real_color = '#F5F5F5;';
                            }

                            var trElement = '';
                            var a_record = ccc[ccc.length - 1];
                            if (a_record.length >1) {
                                var first = a_record[0];
                                trElement += '<tr style="cursor:pointer;height:35px;background:'+real_color+'" onclick="openShutManager(this,'+i+')">';
                                trElement += '<td style="width:30px;text-align: left;"><span class="tubiao'+i+'">[+]</span></td>';
                                trElement += '<td style="text-align: center;">batch</td>';
                                trElement += '<td>batch</td>';
                                trElement += '<td>batch</td>';
                                trElement += '<td>'+(level[parseInt(first.order_level)]?level[parseInt(first.order_level)]:'--')+'</td>';
                                trElement += '<td>'+(label_operate_type[parseInt(first.operate_type)]?label_operate_type[parseInt(first.operate_type)]:+'--')+'</td>';
                                trElement += '<td>'+(first.promise_repayment_time != 0 ?UnixToDate(parseInt(first.promise_repayment_time)):'--')+'</td>';
                                if(GetLength(first.content)>65){
                                    var cutcontent = cutstr(first.content, 65);
                                    trElement += '<td><div class="tip">'+cutcontent+'<span>'+first.content+'</span></div></td>';
                                }else{
                                    trElement += '<td><span>'+first.content+'</span></td>';
                                }
                                trElement += '<td>'+(connect_control_type[parseInt(first.is_connect)]?connect_control_type[parseInt(first.is_connect)]:+'--')+'</td>';
                                trElement += '<td>'+(risk_control_type[parseInt(first.risk_control)]?risk_control_type[parseInt(first.risk_control)]:+'--')+'</td>';
                                trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+first.remark+'</div><span>'+first.remark+'</span></div></td>';
                                trElement += '<td>'+(cui_status[parseInt(first.order_state)]?cui_status[parseInt(first.order_state)]:'--')+'</td>';
                                trElement += '<td>'+(first.operate_at?UnixToDate(parseInt(first.operate_at)):'--')+'</td>';
                                trElement += '<td style="text-align: center;">'+(first.operator_name ? first.operator_name :'--')+'</td>';
                                trElement += '</tr>';
                            }
                            $.each(a_record, function(n,one_record){
                                if (a_record.length >1) {
                                    trElement += '<tr style="height:35px;display:none;background:'+real_color+'" class="box'+i+'">';
                                }else{
                                    trElement += '<tr style="height:35px;background:'+real_color+'" class="box'+i+'">';
                                }
                                trElement += '<td style="width:30px;text-align:right;"><input class="contactphone" type="checkbox" name="contactedphone" leix="csjl" value="'+one_record.contact_phone+'" data-selected="no"></td>';
                                trElement += '<td style="text-align: center;">'+one_record.contact_name+'</td>';
                                trElement += '<td>'+one_record.relation+'</td>';
                                trElement += '<td>'+one_record.contact_phone+'</td>';
                                trElement += '<td>'+(level[parseInt(one_record.order_level)]?level[parseInt(one_record.order_level)]:'--')+'</td>';
                                trElement += '<td>'+(label_operate_type[parseInt(one_record.operate_type)]?label_operate_type[parseInt(one_record.operate_type)]:+'--')+'</td>';
                                trElement += '<td>'+(one_record.promise_repayment_time != 0 ?UnixToDate(parseInt(one_record.promise_repayment_time)):'--')+'</td>';
                                if(GetLength(one_record.content)>65){
                                    var cutcontent = cutstr(one_record.content, 65);
                                    trElement += '<td><div class="tip">'+cutcontent+'<span>'+one_record.content+'</span></div></td>';
                                }else{
                                    trElement += '<td><span>'+one_record.content+'</span></td>';
                                }
                                trElement += '<td>'+(connect_control_type[parseInt(one_record.is_connect)]?connect_control_type[parseInt(one_record.is_connect)]:+'--')+'</td>';
                                trElement += '<td>'+(risk_control_type[parseInt(one_record.risk_control)]?risk_control_type[parseInt(one_record.risk_control)]:+'--')+'</td>';
                                trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+one_record.remark+'</div><span>'+one_record.remark+'</span></div></td>';
                                trElement += '<td>'+(cui_status[parseInt(one_record.order_state)]?cui_status[parseInt(one_record.order_state)]:'--')+'</td>';
                                trElement += '<td>'+(one_record.operate_at?UnixToDate(parseInt(one_record.operate_at)):'--')+'</td>';
                                trElement += '<td style="text-align: center;">'+(one_record.operator_name ? one_record.operator_name :'--')+'</td>';
                                trElement += '</tr>';
                            })

                            $('#csjltab').prepend(trElement);
                            ch_color2++;
                        }
                    },'json');
                    location.reload(true)
                },
                dataType: 'json'
            });
        });

    });
    //催收记录滚动加载///////////
    var page = 1, index = 1;
    var order_id = "<?=$_GET['order_id'];?>";
    var record = [];
    var pageSize = 6;
    var changeColor = 0;    //显示不同的行的颜色 以示区别
    $(document).ready(function() {
        var win = $('#record_div');
        var tbody = $('#csjltab');
        //var sub_type = $('#sub_type').html();
        var ccc = [];
        win.scroll(function() {
            if (tbody.height() - win.scrollTop() < 346) {
                if (index == 1) {
                    $.ajax({
                        url: "index.php?r=work-desk/collection-view&order_id="+order_id+"&page_type=2",
                        dataType: 'json',
                        success: function(responses) {
                            $('#loading').hide();
                            record = responses.loan_collection_record;
                            var z = 0;
                            $.each(record,function(i,n){
                                ccc[z] = n;
                                z++;
                            });
                        }
                    });
                }

                if(ccc.length <= 0) {
                    index = 2;
                    return false;
                }

                var nowpage = 14+(page-1)*pageSize;
                for (var i = ccc.length - nowpage; i > ccc.length - nowpage-pageSize; i--) {
                    if (i<0) {
                        break;
                    }
                    var real_color = '';
                    if (changeColor%2 == 0) {
                        real_color = '#F5F5F5;';
                    }
                    var trElement = '';
                    var a_record = ccc[i];
                    if (a_record.length >1) {
                        var first = a_record[0];
                        trElement += '<tr style="cursor:pointer;height:35px;background:'+real_color+'" onclick="openShutManager(this,'+i+')">';
                        trElement += '<td style="width:30px;text-align: left;"><span class="tubiao'+i+'">[+]</span></td>';
                        trElement += '<td style="text-align: center;">batch</td>';
                        trElement += '<td>batch</td>';
                        trElement += '<td>batch</td>';
                        trElement += '<td>'+(level[parseInt(first.order_level)]?level[parseInt(first.order_level)]:'--')+'</td>';
                        trElement += '<td>'+(label_operate_type[parseInt(first.operate_type)]?label_operate_type[parseInt(first.operate_type)]:+'--')+'</td>';
                        trElement += '<td>'+(first.promise_repayment_time != 0 ?UnixToDate(parseInt(first.promise_repayment_time)):'--')+'</td>';
                        if(GetLength(first.content)>65){
                            var cutcontent = cutstr(first.content, 65);
                            trElement += '<td><div class="tip">'+cutcontent+'<span>'+first.content+'</span></div></td>';
                        }else{
                            trElement += '<td><span>'+first.content+'</span></td>';
                        }
                        trElement += '<td>'+(connect_control_type[parseInt(first.is_connect)]?connect_control_type[parseInt(first.is_connect)]:+'--')+'</td>';
                        trElement += '<td>'+(risk_control_type[parseInt(first.risk_control)]?risk_control_type[parseInt(first.risk_control)]:+'--')+'</td>';
                        trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+first.remark+'</div><span>'+first.remark+'</span></div></td>';
                        trElement += '<td>'+(cui_status[parseInt(first.order_state)]?cui_status[parseInt(first.order_state)]:'--')+'</td>';
                        trElement += '<td>'+(first.operate_at?UnixToDate(parseInt(first.operate_at)):'--')+'</td>';
                        trElement += '<td style="text-align:center;">'+(first.operator_name ? first.operator_name :'--')+'</td>';
                        trElement += '</tr>';
                    }
                    $.each(a_record, function(n,one_record){
                        if (a_record.length >1) {
                            trElement += '<tr style="height:35px;display:none;background:'+real_color+'" class="box'+i+'">';
                        }else{
                            trElement += '<tr style="height:35px;background:'+real_color+'" class="box'+i+'">';
                        }
                        trElement += '<td style="width:30px;text-align:right;"><input class="contactphone" type="checkbox" name="contactedphone" leix="csjl" value="'+one_record.contact_phone+'" data-selected="no"></td>';
                        trElement += '<td style="text-align: center;">'+one_record.contact_name+'</td>';
                        trElement += '<td>'+one_record.relation+'</td>';
                        trElement += '<td>'+'<a href="javascript:;" onclick="callPhone('+one_record.contact_phone+')">'+one_record.contact_phone+'</a>'+'</td>';
                        trElement += '<td>'+(level[parseInt(one_record.order_level)]?level[parseInt(one_record.order_level)]:'--')+'</td>';
                        trElement += '<td>'+(label_operate_type[parseInt(one_record.operate_type)]?label_operate_type[parseInt(one_record.operate_type)]:+'--')+'</td>';
                        trElement += '<td>'+(one_record.promise_repayment_time != 0 ?UnixToDate(parseInt(one_record.promise_repayment_time)):'--')+'</td>';
                        if(GetLength(one_record.content)>65){
                            var cutcontent = cutstr(one_record.content, 65);
                            trElement += '<td><div class="tip">'+cutcontent+'<span>'+one_record.content+'</span></div></td>';
                        }else{
                            trElement += '<td><span>'+one_record.content+'</span></td>';
                        }
                        trElement += '<td>'+(connect_control_type[parseInt(one_record.is_connect)]?connect_control_type[parseInt(one_record.is_connect)]:+'--')+'</td>';
                        trElement += '<td>'+(risk_control_type[parseInt(one_record.risk_control)]?risk_control_type[parseInt(one_record.risk_control)]:+'--')+'</td>';
                        trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+one_record.remark+'</div><span>'+one_record.remark+'</span></div></td>';
                        trElement += '<td>'+(cui_status[parseInt(one_record.order_state)]?cui_status[parseInt(one_record.order_state)]:'--')+'</td>';
                        trElement += '<td>'+(one_record.operate_at?UnixToDate(parseInt(one_record.operate_at)):'--')+'</td>';
                        trElement += '<td style="text-align: center;">'+(one_record.operator_name ? one_record.operator_name :'--')+'</td>';
                        trElement += '</tr>';
                    })
                    changeColor++;
                    $('#csjltab').append(trElement);
                }
                page++;
            }
        });
    });
    //展开折叠催收记录
    var display_tu = 0;
    function openShutManager(obj,cla){
        display_tu++;
        if (display_tu%2 == 1 ) {
            $('.tubiao'+cla).html('[-]');
        }else{
            $('.tubiao'+cla).html('[+]');
        }
        $(obj).siblings("tr[class=box"+cla+"]").toggle();
    }
    ///////////////////


    //点击催收记录筛选
    $('#csjl_filter').submit(function(evt){
        var e = window.event || evt;
        e.preventDefault();

        var order_id = "<?=$_GET['order_id'];?>";
        var collection_name = $('input[name=collection_name]').val();
        var order_level = $('select[name=order_level]').val();
        var statuss = $('select[name=status]').val();
        var operate_type = $('select[name=operate_type]').val();
        var sub_type = $('#sub_type').html();
        var url = "<?= Url::to(['work-desk/collection-view'])?>";
        var data = {
            order_id:order_id,
            collection_name:collection_name,
            order_level:order_level,
            status:statuss,
            operate_tp:operate_type,
            filter:1
        };
        var ccc = [];
        $.get(url,data,function(responses){
            if (responses.success == 2) {
                record = responses.loan_collection_record;


                var z = 0;
                $.each(record,function(i,n){
                    ccc[z] = n;
                    z++;
                });
                if (record.length == 0) {
                    $('#loading').hide();
                    $('#csjltab').html('暂无记录');
                }
                var trElement = '';
                var ch_color = 1;
                for (var i = ccc.length - 1; i >= 0; i--) {
                    $('#csjltab').html('');
                    var real_color = '';
                    if (ch_color%2 == 0) {
                        real_color = '#F5F5F5;';
                    }
                    var a_record = ccc[i];
                    if (a_record.length >1) {
                        var first = a_record[0];
                        trElement += '<tr style="cursor:pointer;height:35px;background:'+real_color+'" onclick="openShutManager(this,'+i+')">';
                        trElement += '<td style="width:30px;text-align: left;"><span class="tubiao'+i+'">[+]</span></td>';
                        trElement += '<td style="text-align: center;">batch</td>';
                        trElement += '<td>batch</td>';
                        trElement += '<td>batch</td>';
                        trElement += '<td>'+(level[parseInt(first.order_level)]?level[parseInt(first.order_level)]:'--')+'</td>';
                        trElement += '<td>'+(label_operate_type[parseInt(first.operate_type)]?label_operate_type[parseInt(first.operate_type)]:+'--')+'</td>';
                        trElement += '<td>'+(first.promise_repayment_time != 0 ?UnixToDate(parseInt(first.promise_repayment_time)):'--')+'</td>';
                        if(GetLength(first.content)>65){
                            var cutcontent = cutstr(first.content, 65);
                            trElement += '<td><div class="tip">'+cutcontent+'<span>'+first.content+'</span></div></td>';
                        }else{
                            trElement += '<td><span>'+first.content+'</span></td>';
                        }
                        trElement += '<td>'+(connect_control_type[parseInt(first.is_connect)]?connect_control_type[parseInt(first.is_connect)]:+'--')+'</td>';
                        trElement += '<td>'+(risk_control_type[parseInt(first.risk_control)]?risk_control_type[parseInt(first.risk_control)]:+'--')+'</td>';
                        trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+first.remark+'</div><span>'+first.remark+'</span></div></td>';
                        trElement += '<td>'+(cui_status[parseInt(first.order_state)]?cui_status[parseInt(first.order_state)]:'--')+'</td>';
                        trElement += '<td>'+(first.operate_at?UnixToDate(parseInt(first.operate_at)):'--')+'</td>';
                        trElement += '<td style="text-align: center;">'+(first.operator_name ? first.operator_name :'--')+'</td>';
                        trElement += '</tr>';
                    }
                    $.each(a_record, function(n,one_record){
                        if (a_record.length >1) {
                            trElement += '<tr style="height:35px;display:none;background:'+real_color+'" class="box'+i+'">';
                        }else{
                            trElement += '<tr style="height:35px;background:'+real_color+'" class="box'+i+'">';
                        }
                        trElement += '<td style="width:30px;text-align:right;"><input class="contactphone" type="checkbox" name="contactedphone" leix="csjl" value="'+one_record.contact_phone+'" data-selected="no"></td>';
                        trElement += '<td style="text-align: center;">'+one_record.contact_name+'</td>';
                        trElement += '<td>'+one_record.relation+'</td>';
                        trElement += '<td>'+'<a href="javascript:;" onclick="callPhone('+one_record.contact_phone+')">'+one_record.contact_phone+'</a>'+'</td>';
                        trElement += '<td>'+(level[parseInt(one_record.order_level)]?level[parseInt(one_record.order_level)]:'--')+'</td>';
                        trElement += '<td>'+(label_operate_type[parseInt(one_record.operate_type)]?label_operate_type[parseInt(one_record.operate_type)]:+'--')+'</td>';
                        trElement += '<td>'+(one_record.promise_repayment_time != 0 ?UnixToDate(parseInt(one_record.promise_repayment_time)):'--')+'</td>';
                        if(GetLength(one_record.content)>65){
                            var cutcontent = cutstr(one_record.content, 65);
                            trElement += '<td><div class="tip">'+cutcontent+'<span>'+one_record.content+'</span></div></td>';
                        }else{
                            trElement += '<td><span>'+one_record.content+'</span></td>';
                        }
                        trElement += '<td>'+(connect_control_type[parseInt(one_record.is_connect)]?connect_control_type[parseInt(one_record.is_connect)]:+'--')+'</td>';
                        trElement += '<td>'+(risk_control_type[parseInt(one_record.risk_control)]?risk_control_type[parseInt(one_record.risk_control)]:+'--')+'</td>';
                        trElement += '<td ><div class="tip"><div style="white-space:nowrap; width:30em; overflow:hidden; text-overflow:ellipsis;">'+one_record.remark+'</div><span>'+one_record.remark+'</span></div></td>';
                        trElement += '<td>'+(cui_status[parseInt(one_record.order_state)]?cui_status[parseInt(one_record.order_state)]:'--')+'</td>';
                        trElement += '<td>'+(one_record.operate_at?UnixToDate(parseInt(one_record.operate_at)):'--')+'</td>';
                        trElement += '<td style="text-align: center;">'+(one_record.operator_name ? one_record.operator_name :'--')+'</td>';
                        trElement += '</tr>';
                    })
                    ch_color++;
                    $('#csjltab').append(trElement);
                }
                page = 999;
            }else{
                $('#loading').hide();
                $('#csjltab').html('暂无记录');
            }
        },'json');
    });
    var contact_log_list=[]; //通话记录
    var contact_phone_list=[];  //通讯录

    //ajax动态请求数据
    $(document).ready(function(){
        var order_id = "<?=$orderId;?>";
        var which_info = 'base_info';
        var url = "<?= Url::to(['work-desk/collection-view']); ?>";
        var data={
            order_id:order_id,
            base_info:which_info
        }
        $.get(url,data,function(response){
            //还款信息处理
            var repayInfo = response.repayInfo;

            $('td[name=repay_order_id]').html(repayInfo['order_id']);
            $('td[name=repay_amount]').html((repayInfo['amount']/100).toFixed(2));
            $('td[name=repay_total_money]').html((repayInfo['total_money']/100).toFixed(2));
            $('td[name=repay_coupon_money]').html((repayInfo['coupon_money']/100).toFixed(2));
            $('td[name=delay_reduce_amount]').html((repayInfo['delay_reduce_amount']/100).toFixed(2));
            $('td[name=repay_cost_fee]').html((repayInfo['cost_fee']/100).toFixed(2));
            $('td[name=repay_overdue_day]').html(repayInfo['overdue_day']);
            $('td[name=repay_overdue_fee]').html((repayInfo['overdue_fee']/100).toFixed(2));
            $('td[name=repay_loan_time]').html(repayInfo['loan_time']);
            $('td[name=repay_plan_fee_time]').html(repayInfo['plan_repayment_time']);
            $('td[name=repay_from_app]').html(repayInfo['from_app']);
            $('td[name=repay_true_total_money]').html((repayInfo['true_total_money']/100).toFixed(2));
            $('td[name=repay_surplus_money]').html((repayInfo['surplus_money']/100).toFixed(2));
            $('td[name=repay_loan_term]').html(repayInfo['loan_term']);
            $('td[name=repay_ulor_status]').html(repayInfo['ulor_status']);
            $('td[name=interests_money]').html((repayInfo['interests']/100).toFixed(2));

        },'json');
    });
    //ajax获取通讯录
    $(document).ready(function(){
        getContactList();
    });
    //刷新获取通讯录
    $('#getContactList').click(function(){
        getContactList();
    });
    function getContactList(){
        var order_id = "<?=$orderId;?>";
        var which_info = 'base_info';
        var url = "<?= Url::to(['work-desk/collection-view']); ?>";
        var data={
            order_id:order_id,
            contact_list:which_info
        }
        $('#has_call_log').hide();
        $('#no_call_log').show();
        $('#getContactList').html('loading ...');
        $.get(url,data,function(response){
            //通讯录处理
            contact_phone_list = response.all_loan_mobile_contacts;
            var contact_list = response.loan_mobile_contacts_list[0];
            if (contact_list.length>0) {
                $('#calllogtb').html('');
                //var contact_page = response.loan_mobile_contacts_list[1];
                var trElement = '';
                for (var i = 0; i < contact_list.length; i++) {
                    var info = contact_list[i];
                    trElement += '<li style="width:5%;border-bottom:1px solid #f5f5f5;"><input type="checkbox" leix="txl" name="contactphone" value="'+info.mobile+'" data-selected="no" data-index="'+i+'"></li>';
                    trElement += '<li style="width:14%;border-bottom:1px solid #f5f5f5;">'+info.name+'</li>';
                    trElement += '<li style="width:14%;border-bottom:1px solid #f5f5f5;">'+'<a href="javascript:;" onclick="callPhone('+info.mobile+')">'+info.mobile+'</a>'+'</li>';
                }
                $('#calllogtb').html(trElement);
                $('#has_call_log').show();
                $('#no_call_log').hide();
            }else{
                $('#has_call_log').hide();
                $('#no_call_log').show();
            }

            $('#getContactList').html('refresh');
        },'json');
    }

    //获取联系人
    $(document).ready(function(){
        var order_id = "<?=$orderId;?>";
        var which_info = 'base_info';
        var url = "<?= Url::to(['work-desk/collection-view']); ?>";
        var data={
            order_id:order_id,
            get_lxr:which_info
        }
        $.get(url,data,function(response){
            contact_log_list = response.jxl_contact;
            jxl_contact = response.jxl_contact;
            //还款信息处理
            var liElement = '';
            var contactDropList = [];
            for (var i =0; i<jxl_contact.length; i++) {
                var contact = jxl_contact[i];
                contactDropList[i] = contact['name']+"&nbsp;&nbsp;"+contact['relation']+"&nbsp;&nbsp;"+'<a href="javascript:;" onclick="callPhone('+contact['phone']+')">'+contact['phone']+'</a>'+"&nbsp;&nbsp;";

            }
            var count = 0;
            var distinct_arr = [];
            for (var i =0; i<jxl_contact.length; i++) {
                var one_contact = jxl_contact[i];
                if ($.inArray(one_contact['phone'],distinct_arr) ==-1) {
                    distinct_arr[i] = one_contact['phone'];
                    liElement += '<li style="width:33%;border-bottom:1px solid #f5f5f5;"><label><input class="contactphone" type="checkbox" name="contactsphone" leix="lxr" data-selected="no" data-index="'+count+'" value="'+one_contact['phone']+'">'+contactDropList[i]+'</label></li>';
                }
            }
            $('.contactinfo').html('');
            $('.contactinfo').html(liElement);
        },'json');
        //通讯记录查看完成




    });
    function matchingName(){
        if (contact_phone_list.length !=0 && contact_log_list.length !=0) {
            var jxl_contact = contact_log_list;
            var liElement = '';
            //替换名字
            for (var i = 0; i < jxl_contact.length; i++) {
                var one_jxl = contact_log_list[i];
                for (var j = 0; j < contact_phone_list.length; j++) {
                    var one_phone = contact_phone_list[j];
                    if (one_phone['mobile'] == one_jxl['phone']) {
                        one_jxl['name'] = one_phone['name'];
                        break;
                    }
                }
            }
            //还款信息处理
            var contactDropList = [];
            for (var i =0; i<jxl_contact.length; i++) {
                var contact = jxl_contact[i];
                contactDropList[i] = contact['name']+"&nbsp;&nbsp;"+contact['relation']+"&nbsp;&nbsp;"+contact['phone']+"&nbsp;&nbsp;";
            }

            var count = 0;
            //如果是级别3 申请查看 或逾期级别4 就直接显示
            var distinct_arr = [];
            for (var i =0; i<jxl_contact.length; i++) {
                var one_contact = jxl_contact[i];
                if ($.inArray(one_contact['phone'],distinct_arr) ==-1) {
                    distinct_arr[i] = one_contact['phone'];
                    var urgent = '';
                    if(one_contact['contact_type']==<?= LoanCollectionRecord::CONTACT_TYPE_URGENT;?>){
                        urgent = '(紧急联系人)';
                    }
                    liElement += '<li style="width:33%;border-bottom:1px solid #f5f5f5;"><label><input class="contactphone" type="checkbox" name="contactsphone" leix="lxr" data-selected="no" data-index="'+count+'" value="'+one_contact['phone']+'">'+contactDropList[i]+urgent+'</label></li>';
                }
            }
            $('.contactinfo').html('');
            $('.contactinfo').html(liElement);
        }else{
            setTimeout("matchingName()", 1000);
        }
    }
</script>

<script type="text/javascript">
    var smsTemplates = eval('(<?= json_encode($smsTemplateList['content'], true) ?>)');
    var connect_success_json = eval('(<?php echo json_encode(LoanCollectionRecord::$risk_connect_success_control);?>)');
    var connect_fail_json =  eval('(<?php echo json_encode(LoanCollectionRecord::$risk_connect_fail_control);?>)');

    //催收方式切换
    function onCollectionTypeChange(value)
    {
        if(value == <?= LoanCollectionRecord::OPERATE_TYPE_SMS ?>)
        {
            var templateId = $('select[name=template]').val();
            onTemplateChange(templateId);
            $('.operation-panel .template').show();
            $('.operation-panel .content').show();
            $('#is_connects').hide();
            $('#risk').hide();
            $('.cn_tr').hide();
            $('.yh_tr').hide();
        } else {
            $('.operation-panel .template').hide();
            $('.operation-panel .content').hide();
            $('.cuishou_phone').hide();
            $('#is_connects').show();
            $('#risk').show();
            $('textarea[name=content]').val('');
            $('input[name=call_this_phone]').val('');
            $('input[name=contactphone]').each(function(){
                if ($(this).data('selected') == 'yes') {
                    $(this).removeAttr('disabled');
                }
            });
            $('input[name=contactsphone]').each(function(){
                if ($(this).data('selected') == 'yes') {
                    $(this).removeAttr('disabled');
                }
                var mobile = $(this).val();
            });
            $('input[name=contactedphone]').each(function(){
                if ($(this).data('selected') == 'yes') {
                    $(this).removeAttr('disabled');
                }
                var mobile = $(this).val();
            });
            checkPromiseTime();
        }
    }
    $('.sel_con_type').click(function(){
        //点某一个，另一个的所有复选框取消选中
        var type = $(this).data('type');
        $('input[name=select_con_type]').val(type);
        if (type == 'lxr') {
            $('input[leix=txl]').each(function(){
                $(this).prop('checked',false);
            });
            $('input[leix=csjl]').each(function(){
                $(this).prop('checked',false);
            });
        }else if(type == 'txl'){
            $('input[leix=lxr]').each(function(){
                $(this).prop('checked',false);
            });
            $('input[leix=csjl]').each(function(){
                $(this).prop('checked',false);
            });
        }else if(type == 'csjl'){
            $('input[leix=lxr]').each(function(){
                $(this).prop('checked',false);
            });
            $('input[leix=txl]').each(function(){
                $(this).prop('checked',false);
            });
        }
    })
    function onTemplateChange(templateId)
    {
        if(templateId == '')
        {
            $('textarea[name=content]').val('Please select an SMS template');
            //$('textarea[name=content]').removeAttr('readonly');
            $('.cuishou_phone').hide();
        } else {
            $('textarea[name=content]').val(smsTemplates[templateId]);
            //$('textarea[name=content]').val('');
            //请求ajax获得短信内容
            //var url = "index.php?r=work-desk/get-sms-fill";
            //var data = {
            //    user_name:"<?//=$personInfo['loanPerson']['name']?>//",
            //    user_sex:"<?//= $personInfo['loanPerson']['gender']?>//",
            //    user_phone:"<?//= $personInfo['loanPerson']['phone']?>//",
            //    card_id:"<?//= $personInfo['loanPerson']['pan_code']?>//",        //s身份证
            //    //total_money:$('td[name=repay_total_money]').html(),    //总额
            //    total_money:$('td[name=repay_surplus_money]').html(),    //总额
            //    overdue_days:$('td[name=repay_overdue_day]').html(),   //逾期天数
            //    from:$('td[name=repay_from_app]').html(),  //借款来源
            //    //area:"<?////= isset($personInfo['userWorkInfo']['residential_address']) ? $personInfo['userWorkInfo']['residential_address'] : ''?>////",
            //    sms_type:templateId,
            //    order_id:"<?php //echo $userLoanOrder->id?>//",
            //};
            //$.get(url,data,function(response){
            //    if (response.error == 0) {
            //        $('textarea[name=content]').val(response.content);
            //        $('textarea[name=content]').attr('readonly','true');
            //    }
            //},'json');
            //if ($.inArray(templateId,need_phone) == -1) {
            //    $('.cuishou_phone').hide();
            //}else{
            //    $('.cuishou_phone').show();
            //}
        }
    }

    function checkPromiseTime(){
        //console.log($('#risk input:radio:checked').val());
        if ($('#risk input:radio:checked').val() == '<?=LoanCollectionRecord::RISK_CONTROL_PROMISED_PAYMENT ?>') {
            $('.cn_tr').show();
        } else {
            $('.cn_tr').hide();
        }
        if ($('#risk input:radio:checked').val() == '<?=LoanCollectionRecord::RISK_CONTROL_USER_PAYMENT ?>') {
            $('.yh_tr').show();
        } else {
            $('.yh_tr').hide();
        }
    }

    $(function() {
        checkPromiseTime();
        $("#risk,#is_connects").change(checkPromiseTime);
        $('input[name=is_connect]').change(
            function () {
                var htmlStr = '<th>collection result</th><td>';
                if ($(this).val() == "1") {
                    $.each(connect_success_json, function(i,item){
                        htmlStr += '<label><input type="radio" name="risk_control" value="'+ i +'">'+ item +'</label>';
                    });
                }else{
                    $.each(connect_fail_json, function(i,item){
                        htmlStr += '<label><input type="radio" name="risk_control" value="'+ i +'">'+ item +'</label>';
                    });
                }
                htmlStr += '</td>'
                $('#risk').html(htmlStr);
            }
        );
        $('.cuishou_phone').hide();
    })

    function callPhone(phone) {
        var data = {};
        data.phone = phone;
        data.order_id = '<?php echo $orderId;?>';
        var nx_phone = "<?= $nx_phone;?>";
        if(nx_phone == 0) {
            return false;
        }
        $.ajax({
            url: "<?= Url::toRoute(['work-desk/call-phone', 't' => time()]); ?>",
            type: 'get',
            dataType: 'json',
            data: data,
            async:false,
            success: function(data){
                if (data.code == 0) {
                    window.location.href = "sip:"+phone+","+data.orderid;
                } else {
                    alert(data.message);
                }
            },
            error: function(){
                alert('Please log in nx phone');
            }
        });
    }

    function copyUrl(link)
    {
        var save = function (e){
            e.clipboardData.setData('text/plain',link);//下面会说到clipboardData对象
            e.preventDefault();//阻止默认行为
        };
        document.addEventListener('copy',save);
        document.execCommand("copy");//使文档处于可编辑状态，否则无效
        alert("copy success!");
    }

</script>