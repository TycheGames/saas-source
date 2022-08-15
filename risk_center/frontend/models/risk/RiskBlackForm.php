<?php

namespace frontend\models\risk;

use yii\base\Model;

class RiskBlackForm extends Model
{

    public $device_ids;
    public $szlm_ids;
    public $aadhaar_md5;
    public $pan_code;
    public $phone;


    public function rules()
    {
        return [
            [['device_ids', 'szlm_ids', 'aadhaar_md5', 'pan_code'], 'safe'],
            [['phone'], 'required'],
        ];
    }
}
