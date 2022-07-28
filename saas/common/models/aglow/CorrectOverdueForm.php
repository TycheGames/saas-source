<?php


namespace common\models\aglow;

use yii\base\Model;


class CorrectOverdueForm extends Model
{
    public $loan_account_no;
    public $closed_time;
    public $true_disbursement_date;
    public $true_total_principal;
    public $true_total_interests;
    public $true_total_overdue_fee;

}