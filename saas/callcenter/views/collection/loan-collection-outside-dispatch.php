<?php
/**
 * Created by phpdesigner.
 * User: user
 * Date: 2016/10/17
 * Time: 11:00
 */
use callcenter\components\widgets\ActiveForm;
use callcenter\models\AdminUser;
use callcenter\models\loan_collection\LoanCollectionOrder;
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
<script language="javascript" type="text/javascript">
    var outside_other_admin = <?php echo json_encode($outside_other_admin);?>;
</script>
<?php $form = ActiveForm::begin(['id' => 'loan-collection-outside-dispatch']); ?>
<table class="tb tb2 fixpadding">
    <tr>
        <th class="partition" colspan="15">操作订单</th>
    </tr>
    <tr>
        <td class="label" id="collection_order_id">催收ID：</td>
        <td ><?php echo Html::encode($collection_order_id) ?></td>
    </tr>
    <tr>
        <th class="partition" colspan="15">选择指派人</th>
    </tr>
    <tr>
        <td class="label" id="collection_order_id">所属机构：</td>
        <td ><?php echo Html::encode($company['real_title']) ?></td>
    </tr>
    <tr>
        <td class="label" id="collection_order_id">订单阶段等级：</td>
        <td ><?php echo LoanCollectionOrder::$level[$order_level] ?></td>
    </tr>
    <tr>
        <!-- 指派 -->
        <td class="label" >选择指派<?php echo LoanCollectionOrder::$level[$order_level];?>小组</td>

        <td>
            <?= Html::dropDownList(
                'group_game',
                0,
                array_merge(['0' => '全部小组'],AdminUser::$group_games),
                [
                    'onchange' => 'onGroupGameChange($(this).val())'
                ]
            ); ?>
        </td>
    </tr>
    <!-- 指派 -->
    <td class="label" >选择小组成员</td>
    <td>
        <select name="dispatch_uid" id ="dispatch_uid" >
            <?php foreach($outside_other_admin as $item):?>
                <option value="<?=Html::encode($item['id']);?>"><?php echo Html::encode($item['username']);?></option>
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
<script type="text/javascript">
    //方式切换
    function onGroupGameChange(value)
    {
        var trElement = '';
        $.each(outside_other_admin, function(n,admin){
            if(value == 0 || value == admin.group_game){
                trElement += '<option value="'+ admin.id +'">'+ admin.username +'</option>';
            }
        });
        if(trElement == ''){
            trElement = '<option value="">没有成员</option>'
        }
        $('#dispatch_uid').html(trElement);
    }
</script>
