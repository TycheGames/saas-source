<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/14
 * Time: 16:09
 */

namespace callcenter\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class AdminMessage
 * @package callcenter\models
 * @property int $id
 * @property int $admin_id
 * @property string $content
 * @property int $status
 */
class AdminMessage extends ActiveRecord
{
    const STATUS_NEW = 1;
    const STATUS_READ = 0;

    public static $status_map = [
        self::STATUS_NEW => 'new',
        self::STATUS_READ => 'have read'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_message}}';
    }

    public static function getDb()
    {
        return \Yii::$app->get('db_assist');
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