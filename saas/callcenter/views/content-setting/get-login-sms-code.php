<?php
use backend\components\widgets\ActiveForm;
use yii\helpers\Html;

$this->shownav('system', 'menu_get_login_sms_code');
$this->showsubmenu('获取登录发送OTP');
?>

<style>
    input.txt {width:120px;}
</style>
<?php $form = ActiveForm::begin(); ?>
Phone：<input type="text" value="<?=Html::encode($phone)?>" name="phone" class="txt" />
<input type="submit" value="get code" class="btn">
<?php $form = ActiveForm::end(); ?>
<br>
<span id="result"><?php echo $result?></span>