<?php

echo $this->render('/product-setting/submenus');
?>

<div class="loan-fund-create">

    <?= $this->render('setting-form', [
        'model'         => $model,
        'packageSetting' => $packageSetting,
        'periodList' => $periodList,
        'isNotMerchantAdmin' => $isNotMerchantAdmin
    ]) ?>

</div>
