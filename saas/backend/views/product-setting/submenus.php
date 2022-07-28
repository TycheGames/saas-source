<?php

use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

$this->showsubmenu(Yii::T('common', 'productManagement'), array(
    array('list', Url::toRoute('product-setting/setting-list'), $route === 'product-setting/setting-list'),
    array('Add product', Url::toRoute(['product-setting/setting-add']), $route === 'product-setting/setting-add'),
));
