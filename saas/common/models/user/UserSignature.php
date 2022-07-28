<?php
namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * UserCaptcha model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $document_id
 * @property string $content
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserSignature extends ActiveRecord
{

    const STATUS_DEFAULT = 0;
    /**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%user_signature}}';
	}

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
	public static function getDb()
	{
		return Yii::$app->get('db');
	}


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


}