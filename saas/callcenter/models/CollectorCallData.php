<?php

namespace callcenter\models;

use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * CollectionCallRecords model
 *
 * @property int $id
 * @property string $date
 * @property int $user_id
 * @property string $phone
 * @property string $name
 * @property int $type
 * @property int $phone_type
 * @property int $is_valid
 * @property int $times
 * @property int $duration
 * @property int $created_at
 * @property int $updated_at
 */
class CollectorCallData extends ActiveRecord
{

    const TYPE_ALL = 0;
    const TYPE_ONE_SELF = 1;
    const TYPE_CONTACT = 2;
    const TYPE_ADDRESS_BOOK = 3;

    public static $type_map = [
        self::TYPE_ALL => 'all',
        self::TYPE_ONE_SELF => '本人',
        self::TYPE_CONTACT => '联系人',
        self::TYPE_ADDRESS_BOOK => '通讯录',
    ];

    const VALID = 1;
    const INVALID = 0;

    public static $valid_map = [
        self::INVALID => '无效拨打',
        self::VALID => '有效拨打'
    ];

    const NATIVE = 1;
    const NIUXIN_PC = 2;
    const NIUXIN_APP = 3;
    const NIUXIN_SDK = 4;

    public static $phone_map = [
        self::NATIVE => '本机拨打',
        self::NIUXIN_PC => '牛信pc拨打',
        self::NIUXIN_APP => '牛信APP拨打',
        self::NIUXIN_SDK => '牛信SDK拨打'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collector_call_data}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
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
}