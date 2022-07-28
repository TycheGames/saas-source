<?php


namespace common\models\aglow;

use yii\base\Model;


class FeesUpdateForm extends Model
{
    public $loan_account_no, $fees_list;
    public $closed_time;
    public $true_total_principal;
    public $true_total_interests;
    public $true_total_overdue_fee;

}