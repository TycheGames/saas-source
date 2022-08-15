<?php

namespace frontend\models\risk;

use yii\base\Model;

class LoginLogForm extends Model
{
    public $app_name;
    public $request_id;
    public $phone;
    public $pan_code;
    public $user_id;
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
    public $ip;
    public $client_time;
    public $event_time;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_name', 'request_id', 'phone', 'user_id', 'event_time',
             ], 'required'],
            [['client_type', 'os_version', 'app_version', 'device_name', 'app_market',
              'device_id', 'brand_name', 'bundle_id', 'latitude', 'longitude', 'szlm_query_id', 'screen_width',
              'screen_height', 'ip', 'client_time', 'pan_code'
             ], 'safe'],
        ];
    }



}
