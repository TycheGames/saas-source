<?php

use yii\helpers\Url;
$this->showsubmenu(Yii::T('common', 'Operations Center'), array(
    array(Yii::T('common', 'Coupon pick up list'), Url::toRoute('user-coupon/list'), 0),
    array(Yii::T('common', 'Coupon compensate'), Url::toRoute('user-coupon/insert-for-loan'), 1),
));

echo $this->render('_user-form-simple', [
	'model' => $model,
	'userRedPacketSlows' => $userRedPacketSlows,
]); ?>