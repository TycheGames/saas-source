<?php

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_picture_metadata_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $order_uuid
 * @property int $is_external 0：内部 1：导流
 * @property int $number30 最近30天内图片数量
 * @property int $number90 最近90天内图片数量
 * @property int $number_all 当前的全部图片数量
 * @property string $metadata_earliest 最早的图片信息
 * @property string $metadata_latest 最晚的图片信息
 * @property string $metadata_earliest_positioned 最早有定位的图片信息
 * @property string $metadata_latest_positioned 最晚有定位的图片信息
 * @property int $created_at
 * @property int $updated_at
 */
class UserPictureMetadataLog extends ActiveRecord
{
    const IS_EXPORT_YES = 1; //是外部订单
    const IS_EXPORT_NO = 0; //非外部订单

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_picture_metadata_log}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'number30', 'number90', 'number_all', 'is_external', 'created_at', 'updated_at'], 'integer'],
            [['metadata_earliest', 'metadata_latest', 'metadata_earliest_positioned', 'metadata_latest_positioned'], 'string'],
            [['order_uuid'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                           => 'ID',
            'user_id'                      => 'User ID',
            'order_id'                     => 'Order ID',
            'order_uuid'                   => 'Order Uuid',
            'is_external'                  => 'Is External',
            'number30'                     => 'Number30',
            'number90'                     => 'Number90',
            'number_all'                   => 'Number All',
            'metadata_earliest'            => 'Metadata Earliest',
            'metadata_latest'              => 'Metadata Latest',
            'metadata_earliest_positioned' => 'Metadata Earliest Positioned',
            'metadata_latest_positioned'   => 'Metadata Latest Positioned',
            'created_at'                   => 'Created At',
            'updated_at'                   => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
