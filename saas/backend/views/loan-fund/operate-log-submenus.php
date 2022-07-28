<?php

use yii\helpers\Url;

$route = Yii::$app->requestedRoute;

$this->showsubmenu(Yii::T('common', 'Fund operate log'), array(
    [ Yii::T('common', 'Fund operate log'), Url::toRoute(['/loan-fund/fund-log']), $route==='loan-fund/fund-log' ],
    [ Yii::T('common', 'date specified quotas log'), Url::toRoute(['/loan-fund/date-specified-quotas-log']), $route==='loan-fund/date-specified-quotas-log' ],
    [Yii::T('common', 'day quota log'), Url::toRoute(['/loan-fund/day-quota-log']), $route === 'loan-fund/day-quota-log']
));

?>