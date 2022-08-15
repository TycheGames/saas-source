<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class InfoCollectionSuggestion
 * @package common\models
 *
 * @property string $app_name
 * @property int $order_id
 * @property int $user_id
 * @property string $phone
 * @property string $pan_code
 * @property string $szlm_query_id
 * @property int $created_at
 * @property int $updated_at
 */
class InfoCollectionSuggestion extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%info_collection_suggestion}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public function rules()
    {
        return [
            [['app_name', 'order_id', 'user_id', 'pan_code', 'phone'], 'required'],
            [['szlm_query_id'], 'safe'],
        ];
    }

}
