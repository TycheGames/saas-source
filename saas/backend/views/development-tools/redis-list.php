
<?php use backend\components\widgets\LinkPager;
use yii\helpers\Html;

 ?>
<style>
table {
    border-collapse:separate;
    border:solid gray 1px;
    border-radius:6px;
    -moz-border-radius:6px;
}

td, th {
    border-left:solid black 1px;
    border-top:solid black 1px;
}

th {
    background-color: blue;
    border-top: none;
}

td:first-child, th:first-child {
     border-left: none;
}
</style>

<table>
<tr>
    <td>名称</td>
    <td>脚本名称</td>
    <td>数量</td>
</tr>
<?php foreach($redisList as $val): ?>
<tr>
    <td><?= Html::encode($val['key']) ?></td>
    <td><?= Html::encode($val['name']) ?></td>
    <td><?= Html::encode($val['length']) ?></td>
</tr>
<?php endforeach; ?>

</table>
