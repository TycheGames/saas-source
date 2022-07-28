<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/1/18
 * Time: 14:15
 */
use yii\helpers\Url;
use yii\helpers\Html;
use backend\components\widgets\LinkPager;
use backend\components\widgets\ActiveForm;
use backend\models\Merchant;

/**
 * @var backend\components\View $this
 */
$this->shownav('system', 'menu_merchant_list');
$this->showsubmenu(Yii::T('common', 'Administrator management'), array(
    array(Yii::T('common', 'Merchant list'), Url::toRoute('merchant-setting/merchant-list'), 1),
    array(Yii::T('common', 'Add merchant'), Url::toRoute('merchant-setting/merchant-add'), 0),
));

?>
<?php $form = ActiveForm::begin(['id' => 'searchform','method'=>'get', 'options' => ['style' => 'margin-bottom:5px;']]); ?>

<?php ActiveForm::end(); ?>

    <table class="tb tb2 fixpadding">
        <tr class="header">
            <th><?php echo Yii::T('common', 'MerchantId') ?></th>
            <th><?php echo Yii::T('common', 'Merchant name') ?></th>
            <th><?php echo Yii::T('common', 'status') ?></th>
            <th><?php echo Yii::T('common', '是否隐藏通讯录') ?></th>
            <th><?php echo Yii::T('common', '是否隐藏紧急联系人') ?></th>
            <th><?php echo Yii::T('common', 'founder') ?></th>
            <th><?php echo Yii::T('common', 'Creation time') ?></th>
            <th><?php echo Yii::T('common', 'operation') ?></th>
        </tr>
        <?php foreach ($merchant as $value): ?>
            <tr class="hover">
                <td><?php echo Html::encode($value->id); ?></td>
                <td><?php echo Html::encode($value->name); ?></td>
                <td><?php echo Html::encode(Yii::T('common', Merchant::$status_arr[$value->status])); ?></td>
                <td><?php echo Html::encode(Merchant::$is_hidden_arr[$value->is_hidden_address_book]); ?></td>
                <td><?php echo Html::encode(Merchant::$is_hidden_arr[$value->is_hidden_contacts]); ?></td>
                <td><?php echo Html::encode($value->operator); ?></td>
                <td><?php echo Html::encode(date('Y-m-d H:i:s', $value->created_at)); ?></td>
                <td>
                    <a href="<?= Url::to(['merchant-edit', 'id' => $value->id]); ?>"><?php echo Yii::T('common', 'edit') ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php echo LinkPager::widget(['pagination' => $pages]); ?>