<?php

use callcenter\models\loan_collection\LoanCollectionOrder;
use yii\helpers\Html;

?>
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="<?php echo $this->baseUrl ?>/bootstrap/css/bootstrap.min.css">

<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>


<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $this->baseUrl ?>/bootstrap/js/bootstrap.min.js"></script>

<?php $this->shownav('index', 'menu_home'); ?>

<div class="panel panel-default first">
    <div class="panel-heading">
        <span>current task</span><span style="font-size: 0.5em;margin-left: 5px;"></span>
    </div>
    <div class="panel-body">
      
        <div class="row col-sm-4">
            <h3>In the collection</h3>
            <p>order count：<span style="color:red"><?= Html::encode($mission[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]??'--');?></span></p>

        </div>
        <div class="row col-sm-4">
            <h3>promise repayment</h3>
            <p>order count：<span style="color:red"><?= Html::encode($mission[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]??'--');?></span></p>
        </div>
         <div class="row col-sm-4">
            <h3>collection success</h3>
            <p>order count：<span style="color:red"><?= Html::encode($mission[LoanCollectionOrder::STATUS_COLLECTION_FINISH]??'--');?></span></p>

        </div>
        
    </div>
</div>