<?php

$this->shownav('index', 'menu_home');
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

Welcome, <?= date('y-m-d H:i:s'); ?>