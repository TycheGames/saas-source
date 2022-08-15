<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/8/1
 * Time: 11:03
 */

use backend\components\widgets\ActiveForm;
use yii\helpers\Url;
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
    </style>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
    <script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/jquery.min.js"></script>
<?php $form = ActiveForm::begin(['id' => 'push-redis']); ?>
    <table class="tb tb2 fixpadding">
        <tr><th class="partition" colspan="15"><?php echo Yii::T('common', 'Re-enter the risk control queue') ?></th></tr>
        <tr>
            <td class="label" ><?php echo Yii::T('common', 'queue') ?></td>
            <td>
                <?php echo Html::dropDownList('list_type', Html::encode(Yii::$app->getRequest()->post('list_type', 0)), $list); ?>
            </td>
        </tr>
        <tr>
            <td class="label" >ids</td>
            <td>
                <textarea name="ids" placeholder="111
222
333" rows="10"></textarea>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="15">
                <input type="submit" value="<?php echo Yii::T('common', 'submit') ?>" name="submit_btn" class="btn"/>
            </td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>