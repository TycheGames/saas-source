<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2017/8/3
 * Time: 9:56
 */
namespace callcenter\models;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
class CollectionAdminOperateLog extends ActiveRecord {
    public static function tableName() {
        return '{{%collection_admin_operate_log}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }
}
