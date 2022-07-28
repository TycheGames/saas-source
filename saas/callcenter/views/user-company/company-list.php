<?php

use yii\helpers\Html;
use yii\helpers\Url;
use callcenter\components\widgets\LinkPager;
use callcenter\components\widgets\ActiveForm;
use callcenter\models\loan_collection\UserCompany;

$this->shownav('manage', 'menu_company_list');
$this->showsubmenu('', array(
    array('催收公司', Url::toRoute(['user-company/company-lists']),1),
    array('新增催收公司', Url::toRoute(['user-company/company-add']),0),
));
?>

<style>.tb2 th{ font-size: 12px;}</style>



<?php if (!empty($arrMerchant)): ?>
    <?php $form = ActiveForm::begin(['method' => "get",'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'] ]); ?>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    <!-- 商户搜索 -->
    Merchant：<?php echo Html::dropDownList('merchant_id', Html::encode(Yii::$app->getRequest()->get('merchant_id', '')), $arrMerchant, array('prompt' => '-All Merchant-')); ?>&nbsp;

    <input type="submit" name="search_submit" value="search" class="btn">&nbsp;&nbsp;

    <?php $form = ActiveForm::end(); ?>
<?php endif; ?>


<table class="tb tb2 fixpadding">
    <thead>
    <tr class="header">
        <th>ID</th>
        <th>商户</th>
        <th>机构代号</th>
        <th>机构名称</th>
        <th>自营</th>
        <th>创建时间</th>
        <th>人数(可用)</th>
<!--        <th>分配优先级(越高分配越多)</th>-->
        <th>操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($user_collection as $value): ?>
        <tr class="hover"  >
            <td><?php echo Html::encode($value['id']); ?></td>
            <td><?php echo Html::encode(\backend\models\Merchant::getMerchantId()[$value['merchant_id']]); ?></td>
            <td><?php echo Html::encode($value['title']); ?></td>
            <td><?php echo Html::encode($value['real_title']); ?></td>
            <td><?php echo Html::encode($value['system'] == true ? '是' : '否');?></td>
            <td><?php echo Html::encode(date('Y-m-d H:i:s',$value['created_at']));?></td>
            <td><?php echo Html::encode($value['count'].'('.$value['useCount'].')');?></td>
            <td>
                <?php if ($isNotMerchantAdmin): ?>
                    <?php if(isset($value['auto_dispatch']) && $value['auto_dispatch'] == 1):?>
                        <a href="JavaScript:;" onclick="closeAutoDispatch(<?php echo $value['id'];?>)">closeAutoDispatch</a>
                    <?php else:?>
                        <a style="color: red;" href="JavaScript:;" onclick="openAutoDispatch(<?php echo $value['id'];?>)">openAutoDispatch</a>
                    <?php endif;?>
                    <?php if ($value['merchant_id'] == 0): ?>
                        <a href="<?=Url::to(['user-company/company-edit','id'=>$value['id'],'page'=>$pages->page])?>" class="btn_edit">编辑</a>&nbsp;&nbsp;
                        <?php if (!$value['system']): ?>
                            <a href="javascript:;" class="btn_del" data-company="<?= $value['id']?>" style="color:red;">删除</a>
                        <?php endif;?>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?=Url::to(['user-company/company-edit','id'=>$value['id'],'page'=>$pages->page])?>" class="btn_edit">编辑</a>&nbsp;&nbsp;
                    <?php if (!$value['system']): ?>
                        <a href="javascript:;" class="btn_del" data-company="<?= $value['id']?>" style="color:red;">删除</a>
                    <?php endif;?>
                <?php endif; ?>
            </td>

        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($user_collection)): ?>
    <div class="no-result">暂无记录</div>
<?php else:?>
<?php endif; ?>
<?php echo LinkPager::widget(['pagination' => $pages]); ?>

<!-- <a href="<?php echo Url::toRoute(['user-company/company-add','tip'=>0]);?>">新增公司</a> -->
<script>
    $(function(){
        $('.btn_del').click(function(){
            if(!confirmMsg('确认要删除吗?')){
                return false;
            }
            company_id = $(this).attr('data-company');
            console.log(company_id);
            del_company(company_id);
        });
    });

    //删除指定公司：
    function del_company(company){

        var params = {company_id:company};
        $.ajax({
            url:'<?php echo Url::to(["user-company/del-company"])?>',
            async:false,
            data:params,
            dataType:'json',
            success:function(data){
                if (data.code == 0) {
                    alert('删除成功');
                }else{
                    alert(data.msg);
                }
                window.location.reload(true);
            },
            error:function(){
                alert('ajax error');
            }
        });

    }


    function closeAutoDispatch(id){
        if(!confirmMsg('Are you sure close auto dispatch?')){
            return false;
        }
        var url = '<?php echo Url::to(["user-company/update-auto-dispatch"]);?>';
        var params = {id:id,status:0};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert('success');
                window.location.reload(true);
            }else{
                alert(data.message);
            }
        })
    }
    function openAutoDispatch(id){
        if(!confirmMsg('Are you sure open auto dispatch')){
            return false;
        }
        var url = '<?php echo Url::to(["user-company/update-auto-dispatch"]);?>';
        var params = {id:id,status:1};
        $.get(url,params,function(data){
            if(data.code == 0){
                alert('success');
                window.location.reload(true);
            }else{
                alert(data.message);
            }
        })
    }
</script>
