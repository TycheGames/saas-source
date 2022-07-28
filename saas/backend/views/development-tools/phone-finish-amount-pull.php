<?php

use backend\components\widgets\ActiveForm;
use yii\helpers\Html;

$this->showsubmenu('手机号完成金额信息拉取', array(
));
?>


<style>
    .rowform .txt{width:450px;height:25px;font-size:15px}
    .tb2 .txt{
        width: 200px;
        margin-right: 10px;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<?php $form = ActiveForm::begin(); ?>
<table class="tb tb2">
    <tr><td class="td27" colspan="2">完款的日期</td></tr>
    <tr class="noborder">
        <td colspan="2">
            <input type="text" value="<?= Html::encode($startDate) ;?>"  name="start_date" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
            至：  <input type="text" value="<?= Html::encode($endDate) ;?>"  name="end_date" onfocus="WdatePicker({startDate:'%y-%M-%d',dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true,readOnly:true})">&nbsp;
            <div class="help-block"></div>
        </td>
    </tr>
    <tr><td class="td27" colspan="2">商户</td></tr>
    <tr class="noborder">
        <td colspan="2">
            <?php echo Html::dropDownList('merchant_id',Html::encode($merchantId), $merchantNameList,['prompt' => '-all merchant-']); ?>
            <div class="help-block"></div>
        </td>
    </tr>
    <tr><td class="td27" colspan="2">phone批量</td></tr>
    <tr class="noborder">
        <td colspan="2">
            <div style="width:780px;height:400px;margin:5px auto 40px 0;">
                <?php echo Html::textarea('phone', Html::encode($phoneStr) ,['placeholder'=>"9870000000
9870000001
9870000002",'style' => 'width:300px;height:295px;']); ?>
            </div>
            <div class="help-block"></div>
        </td>
    </tr>
    <tr>
        <td colspan="15">
            <input type="submit" value="查询" name="submit_btn" class="btn">
        </td>
    </tr>
    <tr>
        <td colspan="15">
            <?php
                if($result){
                    echo '人数：'.$result['person_count'].';'.'金额：'.(intval($result['total_money']) / 100).';';
                }else{
                    if($result === null){

                    }else{
                        echo 'no data';
                    }
                }
            ?>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>