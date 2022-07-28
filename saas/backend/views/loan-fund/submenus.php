<?php
use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

if (!empty($isNotMerchantAdmin)) {
    $this->showsubmenu(Yii::T('common', 'Capital'), array(
        [ Yii::T('common', 'Management list'), Url::toRoute(['/loan-fund/index']), $route==='loan-fund/index' ],
        [ Yii::T('common', 'Add management'), Url::toRoute(['/loan-fund/create']), $route==='loan-fund/create' ],
        [ Yii::T('common', 'Daily quota'), Url::toRoute(['/loan-fund/day-quota-list']), $route==='loan-fund/day-quota-list' ],
        [ Yii::T('common', 'Add quota on specified date'), Url::toRoute(['/loan-fund/add-day-quota']), $route==='loan-fund/add-day-quota' ],
        [Yii::T('common', 'set day quota'), Url::toRoute(['/loan-fund/total-fund-day-list']), $route === 'loan-fund/total-fund-day-list']
    ));
} else {
    $this->showsubmenu(Yii::T('common', 'Capital'), array(
        [ Yii::T('common', 'Management list'), Url::toRoute(['/loan-fund/index']), $route==='loan-fund/index' ],
        [Yii::T('common', 'set day quota'), Url::toRoute(['/loan-fund/total-fund-day-list']), $route === 'loan-fund/total-fund-day-list'],
        [ Yii::T('common', 'Add quota on specified date'), Url::toRoute(['/loan-fund/add-day-quota']), $route==='loan-fund/add-day-quota' ]

    ));
}


?>