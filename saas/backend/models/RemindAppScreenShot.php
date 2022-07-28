<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%remind_app_screen_shot}}".
 *
 * @property int $id
 * @property int $user_id 操作人id
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class RemindAppScreenShot extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_app_screen_shot}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_read_1');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
