<?php

namespace frontend\models\risk;

use yii\base\Model;

class PictureMetadataForm extends Model
{
    public $number30;
    public $number90;
    public $number_all;
    public $metadata_earliest;
    public $metadata_latest;
    public $metadata_earliest_positioned;
    public $metadata_latest_positioned;
    public $gps_in_india_number;
    public $gps_notin_india_number;
    public $gps_null_number;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number30', 'number90', 'number_all'], 'required'],
            [['number30', 'number90', 'number_all'], 'integer'],
            [[
                'metadata_earliest', 'metadata_latest', 'metadata_earliest_positioned', 'metadata_latest_positioned',
                'gps_in_india_number', 'gps_notin_india_number', 'gps_null_number',
             ], 'safe'],
        ];
    }

}
