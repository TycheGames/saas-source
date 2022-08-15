<?php

namespace frontend\models\risk;

use yii\base\Model;

class ClientInfoForm extends Model
{
    public $client_type;
    public $os_version;
    public $app_version;
    public $device_name;
    public $app_market;
    public $device_id;
    public $brand_name;
    public $bundle_id;
    public $latitude;
    public $longitude;
    public $szlm_query_id;
    public $screen_width;
    public $screen_height;
    public $package_name;
    public $ip;
    public $client_time;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_type', 'os_version', 'app_version', 'device_name', 'app_market', 'device_id',
                'brand_name',  'bundle_id', 'latitude', 'longitude', 'szlm_query_id',
                'screen_width', 'screen_height', 'package_name', 'ip', 'client_time'
                ], 'safe'],
        ];
    }

}
