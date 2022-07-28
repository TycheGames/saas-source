<?php

use common\models\tab_bar_icon\TabBarIcon;

/**
 * @var yii\web\View $this
 * @var TabBarIcon $model
 */

echo $this->render('/tab-bar-icon/submenus');
?>

<div class="loan-fund-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
