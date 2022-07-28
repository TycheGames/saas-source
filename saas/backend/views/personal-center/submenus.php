<?php

use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

$this->showsubmenu('个人中心', [
    ['List', Url::toRoute(['/personal-center/index']), $route === 'personal-center/index'],
    ['Add', Url::toRoute(['/personal-center/add']), $route === 'personal-center/add']
]);

?>