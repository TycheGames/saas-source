<?php

use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use common\models\fund\LoanFund;
use  backend\models\Merchant;
use backend\components\widgets\ActiveForm;
use yii\helpers\Html;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::T('common', 'Funding channels');
$this->params['breadcrumbs'][] = $this->title;
echo $this->render('/loan-fund/submenus', ['isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);
?>
<?php if($isNotMerchantAdmin):?>
<?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['loan-fund/index']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?= Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId(false) ,['prompt' => 'all']); ?>&nbsp;
    <input type="submit" name="submit" value="过滤"  class="btn" />
<?php $form = ActiveForm::end(); ?>
<?php endif;?>

<div class="loan-fund-index">

    <table class="tb tb2 fixpadding" id="order_list">
            <tr class="header">
                <th><?php echo Yii::T('common', 'name') ?></th>
                <?php if($isNotMerchantAdmin):?>
                <th><?php echo Yii::T('common', 'Merchant name') ?></th>
                <th><?php echo Yii::T('common', 'Payment account') ?></th>
                <th><?php echo Yii::T('common', 'Small loan account') ?></th>
                <th><?php echo Yii::T('common', '放款组') ?></th>
                <?php endif;?>
                <th><?php echo Yii::T('common', 'Daily default limit') ?></th>
                <th><?php echo Yii::T('common', 'Today\'s remaining credit') ?></th>
                <th><?php echo Yii::T('common', 'status') ?></th>
                <th><?php echo Yii::T('common', '是否放款') ?></th>
                <th><?php echo Yii::T('common', '是否导流订单') ?></th>
                <th><?php echo Yii::T('common', 'is old customer') ?></th>
                <?php if (!empty($isNotMerchantAdmin)): ?>
                    <th><?php echo Yii::T('common', '全老本老:全老本新:全新本新 占比') ?></th>
                <?php endif; ?>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
                <th><?php echo Yii::T('common', 'priority') ?></th>
                <th><?php echo Yii::T('common', 'operation') ?></th>
            </tr>
            <?php 
            $models = $dataProvider->getModels();

            foreach ($models as $model): 
                /* @var $model LoanFund */
                ?>
                <tr class="hover">
                    <td><?=Html::encode($model->name)?></td>
                    <?php if($isNotMerchantAdmin):?>
                        <th><?= Html::encode(Merchant::getMerchantId()[$model->merchant_id]); ?></th>
                        <th><?=Html::encode($payAccountList[$model->pay_account_id]); ?></th>
                        <th><?=Html::encode($loanAccountList[$model->loan_account_id] ?? '无'); ?></th>
                        <th><?=Html::encode($model->payout_group) ;?></th>
                    <?php endif;?>
                    <td><?=$model->day_quota_default/100?><?php echo Yii::T('common', 'yuan') ?></td>

                    <td><?=$model->getTodayRemainingQuota() /100 ;?><?php echo Yii::T('common', 'yuan') ?></td>
                    <td><?=LoanFund::STATUS_LIST[$model->status]?></td>
                    <td><?=LoanFund::$open_loan_map[$model->open_loan]?></td>
                    <td><?=LoanFund::$is_export_map[$model->is_export] ?? '-' ?></td>
                    <td><?= LoanFund::IS_EXPORT_YES == $model->is_export ? '-' : LoanFund::$is_old_costomer_map[$model->is_old_customer]?></td>
                    <?php if (!empty($isNotMerchantAdmin)): ?>
                        <td><?= Html::encode(LoanFund::IS_EXPORT_NO == $model->is_export ? '-' :
                                $model->old_customer_proportion . ':' . $model->all_old_customer_proportion . ':' . $model->getAllNewSelfNewPr()) ;?></td>
                    <?php endif; ?>
                    <td><?=date('Y-m-d H:i:s', $model->created_at)?></td>
                    <td><?=Html::encode($model->score)?></td>
                    <td>
                        <a href="<?php echo Url::to(['update', 'id' => $model->id]);?>"><?php echo Yii::T('common', 'update') ?></a>
                        <?php if($isNotMerchantAdmin):?>
                            <a href="javascript:;" onclick="hiddenFund(<?php echo $model->id;?>)"><?php echo Yii::T('common', 'hidden') ?></a>

                        <?php endif;?>
                    </td>
                </tr>
                
            <?php endforeach;?>
    </table>
</div>
<?php echo LinkPager::widget(['pagination' => $dataProvider->pagination]); ?>
</div>

<script type="text/JavaScript">
    function hiddenFund(id) {
        var url = '<?php echo Url::to(["loan-fund/hidden"]);?>';
        var params = {id:id};
        $.get(url,params,function(data){
            if(!confirmMsg('是否隐藏')){
                return;
            }
            if(data.code == 0){
                alert('操作成功');
                location.reload(true);
            }else{
                alert(data.msg);
            }
        })
    }
</script>