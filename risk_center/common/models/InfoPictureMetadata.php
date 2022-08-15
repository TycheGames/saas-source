<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%info_picture_metadata}}".
 *
 * @property int $id
 * @property string $app_name
 * @property int $user_id
 * @property int $order_id
 * @property int $number30 最近30天内图片数量
 * @property int $number90 最近90天内图片数量
 * @property int $number_all 当前的全部图片数量
 * @property string $metadata_earliest 最早的图片信息
 * @property string $metadata_latest 最晚的图片信息
 * @property string $metadata_earliest_positioned 最早有定位的图片信息
 * @property string $metadata_latest_positioned 最晚有定位的图片信息
 * @property int $gps_in_india_number 图片地址在印度的数量
 * @property int $gps_notin_india_number 图片地址不在印度的数量
 * @property int $gps_null_number 图片地址为空的数量
 * @property int $created_at
 * @property int $updated_at
 */
class InfoPictureMetadata extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%info_picture_metadata}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'number30', 'number90', 'number_all'], 'integer'],
            [['app_name', 'metadata_earliest', 'metadata_latest', 'metadata_earliest_positioned', 'metadata_latest_positioned'], 'string'],
            [[
                 'gps_in_india_number', 'gps_notin_india_number', 'gps_null_number',
             ], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                           => 'ID',
            'app_name'                     => 'App Name',
            'user_id'                      => 'User ID',
            'order_id'                     => 'Order ID',
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
