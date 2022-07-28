<?php
/**
 * Created by phpdesigner.
 * User: user
 * Date: 2016/10/17
 * Time: 11:00
 */
use callcenter\components\widgets\ActiveForm;
use yii\helpers\Html;
?>
<style>
    td.label {
        width: 170px;
        text-align: right;
        font-weight: 700;
    }
    .txt{ width: 100px;}

    .tb2 .txt, .tb2 .txtnobd {
        width: 200px;
        margin-right: 10px;
    }
    .tb{width:55%;}
</style>

<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'loan-collection-outside-dispatch-company']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <th class="partition" colspan="15">选择公司机构</th>
    </tr>
    <tr>
        <td class="label" id="collection_order_id">订单ID：</td>
        <td ><?php echo Html::encode($collection_order_id) ?></td>
    </tr>
    <tr>
    <!-- 指派 -->
    <td class="label" >指派机构：</td>
    <td>
        <select name="outside" >
        <?php foreach($company_list as $id => $company):?>
            <option value="<?=Html::encode($id);?>"><?php echo Html::encode($company['real_title']);?></option>
        <?php endforeach;?>
        </select>
        </td>
    </tr>
    <tr>
        <td class="label">
            <input type="submit" value="提交" name="submit_btn" class="btn"/>
            <a href="javascript:history.go(-1)" class="btn back" style="cursor: pointer;border:none;">返回</a>
        </td>
    </tr>
</table>
<?php ActiveForm::end(); ?>
