<?php

/**
 * @var array $arrCreditAccountId
 * @var array $arrMerchantId
 */

echo $this->render('/personal-center/submenus');
?>
<div class="loan-fund-update">

    <?= $this->render('_form', [
        'model'              => $model,
        'arrCreditAccountId' => $arrCreditAccountId,
        'arrMerchantId'      => $arrMerchantId,
    ]) ?>

</div>
