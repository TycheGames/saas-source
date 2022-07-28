<?php

use common\models\personal_center\PersonalCenter;

/**
 * @var yii\web\View $this
 * @var PersonalCenter $model
 */

echo $this->render('/personal-center/submenus');
?>
<div class="loan-fund-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
