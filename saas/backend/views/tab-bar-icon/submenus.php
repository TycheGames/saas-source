<?php

use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

$this->showsubmenu('Tab bar icon管理', [
    ['List', Url::toRoute(['/tab-bar-icon/index']), $route === 'tab-bar-icon/index'],
    ['Add', Url::toRoute(['/tab-bar-icon/add']), $route === 'tab-bar-icon/add']
]);

?>