<?php
namespace common\models\user;

use yii\db\ActiveRecord;

/**
 * UserLoginLog model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $data
 * @property int $created_at
 */
class UserConversionData extends ActiveRecord {

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb() {
        return \yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_conversion_data}}';
    }
}
