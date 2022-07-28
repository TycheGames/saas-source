<?php
/**
 * @var array $arrCreditAccountId
 * @var array $arrMerchantId
 */
?>

<div class="loan-fund-create">

    <?= $this->render('_form', [
        'model'              => $model,
        'arrCreditAccountId' => $arrCreditAccountId,
        'arrMerchantId'      => $arrMerchantId,
    ]) ?>

</div>