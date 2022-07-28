<?php
namespace backend\models\remind;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindGroup
 * @package backend\models\remind
 * @property int $id
 * @property int $merchant_id
 * @property string $name
 * @property string $team_leader_id
 * @property int $created_at
 * @property int $updated_at
 */
class RemindGroup extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_group}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['merchant_id','team_leader_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Remind group name',
            'merchant_id' => 'Merchant Id'
        ];
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

    /**
     * @param string $groupId
     * @return array|mixed|string
     */
    public static function allGroupName($groupId = ''){
        $lists = self::find()->asArray()->all();
        $list_arr = [];
        foreach ($lists as $key => $item) {
            $list_arr[$item['id']] = $item['name'];
        }
        return empty($groupId) ? $list_arr : (isset($list_arr[$groupId]) ? $list_arr[$groupId] : '--');
    }
}