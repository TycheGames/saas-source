<?php

use yii\helpers\Url;
$this->showsubmenu(Yii::T('common', 'Operations Center'), array(
    array(Yii::T('common', 'Coupon template list'), Url::toRoute('user-coupon/list-slow'), 0),
    array(Yii::T('common', 'Coupon template add'), Url::toRoute('user-coupon/add-slow'), 1),
));
echo $this->render('_form-slow', [
	'model' => $model,
]); ?>