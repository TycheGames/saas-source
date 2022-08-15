<?php

use yii\helpers\Html;


$this->title = Yii::T('common', 'Rule edit');
?>
<div class="rule-add">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_rule-form', ['ruleModel'=>$ruleModel, 'act' => 'edit-node']); ?>

</div>
