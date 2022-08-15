<?php

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_credit_report_cibil}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $pan_code
 * @property int $retry_num
 * @property int $score
 * @property int $name
 * @property string $data 数据
 * @property int $status 状态
 * @property int $is_request 是否请求第三方
 * @property int $query_time 请求时间
 * @property int $created_at
 * @property int $updated_at
 */
class UserCreditReportCibil extends ActiveRecord
{

    const STATUS_SUCCESS = 1;
    const STATUS_DEFAULT = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_credit_report_cibil}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'user_id'      => 'User ID',
            'data'         => 'Report Data',
            'created_at'   => 'Created At',
            'updated_at'   => 'Updated At',
        ];
    }
}
