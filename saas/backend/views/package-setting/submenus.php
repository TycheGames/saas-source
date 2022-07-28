<?php

use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

$this->showsubmenu('包管理', [
    ['List', Url::toRoute(['/package-setting/index']), $route === 'package-setting/index'],
    ['Add', Url::toRoute(['/package-setting/add']), $route === 'package-setting/add']
]);

?>