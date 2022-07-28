<?php
use yii\helpers\Url;
use callcenter\components\widgets\ActiveForm;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Collection system login</title>
	<link href="<?php echo $this->baseUrl; ?>/image/admincp.css?t=2014120301" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="<?php echo $this->baseUrl; ?>/image/xiao.ico" type="image/x-icon" />
	<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
    <script src="<?= $webUrl;?>"></script>
</head>
<body>
<script type="text/JavaScript">
	if (self.parent.frames.length != 0) {
		self.parent.location=document.location;
	}

    function sendCaptcha() {
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= $webSecret;?>', {action: 'submit'}).then(function(token) {
                $('#send-captcha').val('Pending').attr('disabled', 'disabled');
                var data = {};
                data.username = $('#LoginForm_username').val();
                data._csrf = "<?= yii::$app->request->getCsrfToken() ;?>";
                data.token = token;
                $.ajax({
                    url: "<?= Url::toRoute(['main/phone-captcha']); ?>",
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function(data){
                        if (data.code == 0) {
                            $('#send-captcha').val('Success').attr('disabled', 'disabled');
                        } else {
                            $('#send-captcha').val('Get OTP').attr('disabled', 'enabled');
                            alert(data.message);
                        }
                    },
                    error: function(){
                        $('#send-captcha').val('Get OTP').attr('disabled', 'enabled');
                        alert('OTP failed');
                    }
                });
            });
        });
    }
</script>
<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
<table class="logintb">
	<tr>
		<td class="login" style="width:210px;">
			<h1>collection system</h1>
		</td>
		<td>
			<p style="color:red;"><?php if ($model->hasErrors()) { $_err = $model->getFirstErrors(); echo array_shift($_err); } ?></p>
			<p class="logintitle">Username：</p>
			<p class="loginform"><input type="text" class="txt" placeholder="username" name="LoginForm[username]" id="LoginForm_username" value="<?php echo \yii\helpers\Html::encode($model->username); ?>"></p>
            <p class="logintitle">OTP：</p>
            <p class="loginform" style="height:30px;width:200px;">
                <input type="text" name="LoginForm[phoneCaptcha]" value="<?php echo \yii\helpers\Html::encode($model->phoneCaptcha); ?>" class="txt" id="loginform-phoneCaptcha" style="width:60px;"/>
                <input id="send-captcha" class="btn" type="button" value="get code" onclick="sendCaptcha();" />
            </p>
            <input id="token" type="hidden" name="LoginForm[token]"/>
            <p class="loginnofloat"><input type="submit" class="btn" value="login" name="submit_btn"></p>
		</td>
	</tr>
</table>
<?php ActiveForm::end(); ?>
<?php
 if(!strpos($_SERVER["HTTP_USER_AGENT"],"Chrome")){

     echo '<h1 style="color:red;">please use chrome</h1>';
 }
?>
<table class="logintb">
	<tr>
		<td colspan="2" class="footer">
			<div class="copyright">
				<p></p>
				<p></p>
			</div>
		</td>
	</tr>
</table>
<script>
    grecaptcha.ready(function() {
        grecaptcha.execute('<?= $webSecret;?>', {action: 'submit'}).then(function(token) {
            $('#token').val(token);
        });
    });
</script>
</body>
</html>
