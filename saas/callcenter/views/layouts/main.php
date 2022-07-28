<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=7" />
    <title><?= isset($this->title) ? $this->title : ''?></title>
    <?php echo \yii\helpers\Html::csrfMetaTags(); ?>
    <?php if(isset($_SERVER['BSTYLE']) && $_SERVER['BSTYLE'] == '1'): ?>
        <link href="<?php echo $this->baseUrl; ?>/image/admincp-merchant.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo $this->baseUrl; ?>/css/site-merchant.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <?php else: ?>
        <link href="<?php echo $this->baseUrl; ?>/image/admincp.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo $this->baseUrl; ?>/css/site.css?<?php echo time(); ?>" rel="stylesheet" type="text/css" />
    <?php endif;?>
    <link rel="shortcut icon" href="<?php echo $this->baseUrl; ?>/image/xiao.ico" type="image/x-icon" />
</head>
<body>
<script type="text/JavaScript">
    var admincpfilename = 'index.php', IMGDIR = 'image/', STYLEID = '1', VERHASH = 'dob', IN_ADMINCP = true, ISFRAME = '0', STATICURL='./', SITEURL = '<?php echo $this->baseUrl; ?>', JSPATH = 'js/';
</script>
<script src="<?php echo $this->baseUrl; ?>/js/admin.js?<?php echo time(); ?>" type="text/javascript"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js" type="text/javascript"></script>
<?php
$words = 'saas '.Yii::$app->user->identity->username;
$wordsSize = strlen($words);
$font= "./css/Hollywood Hills Italic.ttf"; //字体所放目录
$angle = 10;//倾斜角度
$size = 30;
$sizeArr = imagettfbbox($size,$angle,$font,$words);

$width = max($sizeArr[0],$sizeArr[2],$sizeArr[4],$sizeArr[6]) - min($sizeArr[0],$sizeArr[2],$sizeArr[4],$sizeArr[6])  + 50;
$height = max($sizeArr[1],$sizeArr[3],$sizeArr[5],$sizeArr[7]) - min($sizeArr[1],$sizeArr[3],$sizeArr[5],$sizeArr[7]) + 50;
$im =imagecreate($width,$height);
$background_color = ImageColorAllocate ($im, 255, 255, 255);
$col = imagecolorallocate($im, 240, 240, 250);


imagettftext($im,$size,$angle,50,$height,$col,$font,$words); //写 TTF 文字到图中
header('Content-Type: image/png');//发送头信息

ob_start ();
//$im是你自己创建的图片资源
imagepng($im);

$image_data = ob_get_contents ();

ob_end_clean ();

//得到这个结果，可以直接用于前端的img标签显示
$image_data_base64 = "data:image/png;base64,". base64_encode ($image_data);
?>
<div id="append_parent"></div>
<div id="ajaxwaitid"></div>
<div class="container" id="cpcontainer">
    <?php echo $content; ?>
</div>
</body>
</html>
<style>
    .container{
        background:url("<?=$image_data_base64 ?>");
    }
</style>