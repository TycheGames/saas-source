<?php

use yii\helpers\Url;

/**
 * @var callcenter\components\View $this
 */
$this->shownav('manage', 'menu_admin_list');
$this->showsubmenu('', array(
    array('monitor list ', Url::toRoute('user-collection/admin-list'), 0),
    array('edit monitor', Url::toRoute(['user-collection/admin-edit']),1),
));
?>

<?php echo $this->render('_adminform', [
    'model' => $model
]); ?>