<?php

namespace common\models\third_data;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%validation_rule}}".
 *
 * @property int $id
 * @property int $validation_type 认证类型
 * @property int $service_error 服务单位时间发生错误数量
 * @property int $service_time 服务单位时间时长，秒
 * @property int $service_current 当前服务
 * @property int $service_switch 替换服务
 * @property int $is_used 是否启用 1:启用 0:停用
 * @property int $created_at
 * @property int $updated_at
 */
class ValidationRule extends ActiveRecord
{
    const IS_USED = 1;
    const IS_UNUSED = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%validation_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['validation_type', 'service_error', 'service_time', 'service_current', 'service_switch', 'is_used', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'validation_type' => 'Validation Type',
            'service_error'   => 'Service Error',
            'service_time'    => 'Service Time',
            'service_current' => 'Service Current',
            'service_switch'  => 'Service Switch',
            'is_used'         => 'Is Used',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
