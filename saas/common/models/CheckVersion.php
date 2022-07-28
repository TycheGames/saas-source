<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "{{%check_version}}".
 */
class CheckVersion extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_market','rules','status', 'new_version', 'new_features','new_ios_version', 'ard_url', 'ard_size'], 'required', 'message' => '不能为空'],
            ['has_upgrade', 'default', 'value' => 0],
            ['is_force_upgrade', 'default', 'value' => 0],
        ];
    }

    const HAS_UPGRADE_SUCCESS = 1;//要提示升级
    const HAS_UPGRADE_FALSE = 0;//不要提示升级

    const FORCE_UPGRADE_SUCCESS = 1;//要强制升级
    const FORCE_UPGRADE_FALSE = 0;//不要强制升级


    public static $app_url = [
    ];
    /**
     * 报错的提示的包
     */
    public static $err_tip_list = [
    ];
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'app_market'       => 'app_market',
            'rules'            => '匹配版本规则',
            'status'           => '启用状态',
            'has_upgrade'      => 'has_upgrade',
            'is_force_upgrade' => 'is_force_upgrade',
            'new_version'      => 'Android版本号',
            'new_ios_version'  => 'IOS版本号',
            'new_features'     => '新版本描述',
            'ard_url'          => '现在地址',
            'ard_size'         => '大小',
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%check_version}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

}
