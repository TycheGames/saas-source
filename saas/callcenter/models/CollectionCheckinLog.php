<?php
namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * CollectionCheckinLog model
 *
 * @property integer $id
 * @property string $user_id
 * @property string $type
 * @property int $address_type
 * @property string $date
 * @property integer $created_at
 * @property integer $updated_at
 */
class CollectionCheckinLog extends ActiveRecord
{

    const TYPE_START_WORK = 1;
    const TYPE_OFF_WORK = 2;

    public static $type_map = [
        self::TYPE_START_WORK => 'start',
        self::TYPE_OFF_WORK => 'off'
    ];

    const TYPE_DEFAULT = 0;
    const TYPE_ADDRESS_COMPANY = 1;
    const TYPE_ADDRESS_HOME = 2;

    public static $address_type_map = [
        self::TYPE_DEFAULT => 'none',
        self::TYPE_ADDRESS_COMPANY => 'company',
        self::TYPE_ADDRESS_HOME => 'home'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collection_checkin_log}}';
    }


    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * 判断催收是否上班打卡
     * @param $userId
     * @return bool
     */
    public static function checkStartWork($userId)
    {
        $log = self::find()->where([
            'user_id' => $userId,
            'type' => CollectionCheckinLog::TYPE_START_WORK,
            'date' => date('Y-m-d')
        ])->exists();
        return $log;
    }

}
