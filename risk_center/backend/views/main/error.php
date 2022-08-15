<?php

use yii\helpers\Html;
use yii\helpers\VarDumper;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = empty($name) ? '系统错误' : $name;
$message = $message ?? '';
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger" style="margin-top: 10px; margin-bottom: 10px;">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        <?php if('The admin role you belong to does not have this privilege' == $exception->getMessage()):?>
            <?= $exception->getMessage();?>
        <?php else:?>
            <?= $exception->getMessage();?>
            系统出错了。
        <?php endif;?>
        <a href="mailto:<?= isset(\yii::$app->params['adminEmail']) ? \yii::$app->params['adminEmail'] : ''  ?>?subject=<?= \yii::$app->id ?>_error">联系相关开发</a>
        <?php if(!YII_ENV_PROD) :?>
            <p><?= VarDumper::dump($exception, 5, true); ?></p>
        <?php endif;?>
    </p>

</div>
