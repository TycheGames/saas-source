<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class CollectionTask
 * @package common\models
 * @property int $id
 * @property int $admin_user_id
 * @property int $type
 * @property string $text
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class CollectionTask extends ActiveRecord
{
   const STATUS_DEFAULT = 0;
   const STATUS_SUCCESS = 1;
   const STATUS_FAIL = -1;

   public static $status_map = [
       self::STATUS_DEFAULT => '默认',
       self::STATUS_SUCCESS => '已处理',
       self::STATUS_FAIL => '驳回',
   ];

   public static $type_map = [
       1 => '批量回收指定催收员的催收订单',
       4 => '批量删除指定催收员'
   ];

    public static $old_type_map = [
        1 => '批量回收指定催收员的催收订单',
        2 => '回收 S1 账龄的催收订单',
        10 => '回收 S2 账龄的催收订单',
        11 => '回收 M1 账龄的催收订单',
        12 => '回收 M2 账龄的催收订单',
        13 => '回收 M3 账龄的催收订单',
        14 => '回收 M3+ 账龄的催收订单',
        3 => '批量加0指定催收员的手机号',
        4 => '批量删除指定催收员'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collection_task}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

}