<?php

use callcenter\components\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Html;

$this->showsubmenu('', array(
    array('催收公司', Url::toRoute(['user-company/company-lists']),0),
    array('编辑公司', '',1),
));
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
</style>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'company-edit-form']); ?>
<input type="hidden" name="UserCompany[id]" value="<?= Html::encode($user_company['id'])?>">
<table class="tb tb2 fixpadding">
    <tr>
        <td class="label">机构代号：</td>
        <td ><?php echo $form->field($user_company, 'title'); ?></td>
    </tr>
    <tr>
        <td class="label">机构名称：</td>
        <td ><?php echo $form->field($user_company, 'real_title'); ?></td>
    </tr>
<!--    <tr>-->
<!--        <td class="label">优先级(越高分配越多)：</td>-->
<!--        <td>--><?php //echo $form->field($user_company, 't3_priority'); ?><!--</td>-->
<!--    </tr>-->
    <tr>
        <td class="label">自营：</td>
        <td ><?php echo $form->field($user_company, 'system')->radioList(['1'=>'是','0'=>'否']); ?></td>
    </tr>
<!--    --><?php //if(empty($ip_list)):?>
<!--        <tr>-->
<!--            <td class="label">IP：</td>-->
<!--            <td  class="ip_list"><input type="text" name="ips[]"><a class="btn_add" href="javascript:;">&nbsp;新增IP</a></td>-->
<!--        </tr>-->
<!--    --><?php //else:?>
<!--        --><?php //foreach ($ip_list as $key => $item):?>
<!--            <tr>-->
<!--                <td class="label">IP：</td>-->
<!--                <td  class="ip_list"><input type="text" value="--><?//=$item['ip']?><!--" name="ips[]"><a class="btn_add" href="javascript:;">&nbsp;新增IP</a></td>-->
<!--            </tr>-->
<!--        --><?php //endforeach;?>
<!--    --><?php //endif;?>
</table>
<input type="hidden" name="page" value="<?=$page?>">
<input type="submit" value="提交" name="submit_btn" class="btn">
<a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
<?php ActiveForm::end(); ?>