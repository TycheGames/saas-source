<?php
use yii\helpers\Url;
use callcenter\components\widgets\ActiveForm;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Collection system login</title>
	<link href="<?php echo $this->baseUrl; ?>/image/admincp-merchant.css?t=2014120301" rel="stylesheet" type="text/css" />
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
<div class="login-wrapper">
    <h1 class="title">MANAGEMENT SYSTEM</h1>

    <table class="logintb">
        <tr>
            <td class="login-form-wrapper">
                <h3>Please Log In</h3>
                <p><?php if ($model->hasErrors()) { $_err = $model->getFirstErrors(); echo array_shift($_err); } ?></p>
                <div class="login-item">
                    <span class="label">
                        Username：
                    </span>
                    <input type="text" class="txt user input" name="LoginForm[username]" id="LoginForm_username" value="<?= $model->username; ?>" />
                </div>
                <div class="login-item">
                    <span class="label">
                       OTP：
                    </span>

                    <div class="input">
                        <input type="text" name="LoginForm[phoneCaptcha]" value="<?php echo $model->phoneCaptcha; ?>" class="txt number otp" id="loginform-phoneCaptcha" />
                        <input id="send-captcha" class="btn send-otp" type="button" value="Get OTP" onclick="sendCaptcha();" />
                    </div>
                </div>
                <input id="token" type="hidden" name="LoginForm[token]"/>
                <div class="login-item">
                    <input id="submit" type="submit" class="btn submit" value="LOGIN" name="submit_btn">
                </div>
            </td>
        </tr>
    </table>
</div>
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
