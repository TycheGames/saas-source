<?php

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_photo_url}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $url
 * @property int $created_at
 * @property int $updated_at
 */
class UserPhotoUrl extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_photo_url}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'url'], 'required'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
