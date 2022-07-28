<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('system', 'menu_sms_template_list');
$this->showsubmenu('模板设置', array(
    array('列表', Url::toRoute('content-setting/sms-template-list'), 0),
    array('添加', Url::toRoute('content-setting/sms-template-add'), 1),
));

echo $this->render('_sms-template-form', [
	'model' => $model,
    'arrPackage' => $arrPackage,
    'companys' => $companys
]);