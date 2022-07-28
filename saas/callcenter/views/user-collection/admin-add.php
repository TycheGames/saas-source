<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_admin_list');
$this->showsubmenu('', array(
    array('admin list ', Url::toRoute('user-collection/admin-list'), 0),
    array('add admin', Url::toRoute(['user-collection/admin-add']),1),
));
?>

<?php echo $this->render('_adminform', [
    'model' => $model
]); ?>