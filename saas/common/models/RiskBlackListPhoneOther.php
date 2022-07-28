<?php
namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 黑名单-联系人
 * This is the model class for table "{{%risk_black_list_phone}}".
 * Class RiskBlackListPhone
 * @package common\models
 * @property integer    $id 自增ID
 * @property string    $value 手机号
 * @property integer     $created_at 创建时间
 * @property integer     $updated_at 修改时间
 */

class RiskBlackListPhoneOther extends ActiveRecord {

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%risk_black_list_phone}}';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb(){
        return Yii::$app->get('db_risk');
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