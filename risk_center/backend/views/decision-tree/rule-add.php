<?php

use yii\helpers\Html;


$this->title = Yii::T('common', 'New features');
?>
<div class="rule-add">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_rule-form', ['ruleModel'=>$ruleModel, 'act' => 'add']); ?>

</div>
