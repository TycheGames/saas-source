<?php
namespace common\models\risk;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class RiskResultSnapshotGray
 * @package common\models\risk
 * @property int order_id
 * @property int user_id
 * @property string tree_code
 * @property string tree_version
 * @property string result_data
 * @property string base_node
 * @property string guard_node
 * @property string manual_node
 * @property string result
 * @property string txt
 * @property int created_at
 * @property int updated_at
 *
 */
class RiskResultSnapshotGray extends ActiveRecord {


    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%risk_result_snapshot_gray}}';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb(){
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }

}