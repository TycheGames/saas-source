<?php
namespace common\models\risk;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 黑名单-联系人
 * This is the model class for table "{{%risk_black_list_deviceid}}".
 * Class RiskBlackListDeviceid
 * @package common\models
 * @property integer    $id 自增ID
 * @property integer    $merchant_id 商户ID
 * @property integer    $user_id 用户ID
 * @property string    $value 设备号
 * @property integer     $source 黑名单来源  1系统   2催收
 * @property integer    $operator_id 操作人ID  0系统
 * @property integer     $created_at 创建时间
 * @property integer     $updated_at 修改时间
 */

class RiskBlackListDeviceid extends ActiveRecord {

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%risk_black_list_deviceid}}';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb(){
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }


}