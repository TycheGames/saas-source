<?php

use common\models\fund\LoanFund;
use yii\helpers\Url;
use backend\components\widgets\LinkPager;
use common\models\fund\LoanFundDayQuota;
use backend\components\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::T('common', 'Daily quota');
$this->params['breadcrumbs'][] = $this->title;
echo $this->render('/loan-fund/submenus',['route'=>Yii::$app->controller->route ,'isNotMerchantAdmin' => empty($isNotMerchantAdmin) ? false : $isNotMerchantAdmin]);
?>
<?php if($isNotMerchantAdmin):?>
    <?php $form = ActiveForm::begin(['method' => "get", 'action'=>Url::to(['loan-fund/day-quota-list']), 'options' => ['style' => 'margin-top: 10px;margin-bottom:10px;'],  ]); ?>
    <?php echo Yii::T('common', 'merchant') ?>：<?= Html::dropDownList('merchant_id', Html::encode(\yii::$app->request->get('merchant_id')), \backend\models\Merchant::getMerchantId(false) ,['prompt' => 'all']); ?>&nbsp;
    <input type="submit" name="submit" value="过滤"  class="btn" />
    <?php $form = ActiveForm::end(); ?>
<?php endif;?>
<div class="">
    
    <table class="tb tb2 fixpadding">
            <tr class="header">
                <th><?php echo Yii::T('common', 'management') ?></th>
                <th><?php echo Yii::T('common', 'type') ?></th>
                <th><?php echo Yii::T('common', 'date') ?></th>
                <th><?php echo Yii::T('common', 'Total limit') ?></th>
                <th><?php echo Yii::T('common', 'Remaining limit') ?></th>
                <th><?php echo Yii::T('common', 'Loan amount') ?></th>
                <th><?php echo Yii::T('common', 'Creation time') ?></th>
                <th><?php echo Yii::T('common', 'update time') ?></th>
                <th><?php echo Yii::T('common', 'operation') ?></th>
            </tr>
            <?php 
            foreach ($rows as $row): 
                /* @var $model LoanFund */
                ?>
                <tr class="hover">
                    <td><?= Html::encode($fundList[$row->fund_id])?></td>
                    <td><?= Html::encode(LoanFundDayQuota::TYPE_LIST[$row['type']])?></td>
                    <td><?= Html::encode($row['date'])?></td>
                    <td><?=sprintf("%0.2f",$row['quota']/100)?></td>
                    <td><?=sprintf("%0.2f",$row['remaining_quota']/100)?></td>
                    <td><?=sprintf("%0.2f",$row['loan_amount']/100)?></td>
                    <td><?=date('Y-m-d H:i:s', $row['created_at'])?></td>
                    <td><?=date('Y-m-d H:i:s', $row['updated_at'])?></td>
                    <td>
                        <a href="<?=Url::to(['update-day-quota','id'=>$row['id']]);?>" ><?php echo Yii::T('common', 'update') ?></a>
                    </td>
                </tr>
                
            <?php endforeach;?>
    </table>
</div>
<?php echo LinkPager::widget(['pagination' => $pagination]); ?>
</div>
