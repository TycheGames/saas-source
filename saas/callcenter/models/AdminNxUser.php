<?php
namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * AdminNxUser model
 *
 * @property integer $id
 * @property string $collector_id
 * @property string $nx_name
 * @property string $password
 * @property int $status
 * @property int $type
 * @property integer $created_at
 * @property integer $updated_at
 */
class AdminNxUser extends \yii\db\ActiveRecord
{
    //状态
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 0;

    const TYPE_PC = 0;
    const TYPE_ANDROID = 1;
    const TYPE_SDK = 2;

    public static $status_map = [
        self::STATUS_ENABLE => 'ENABLE',
        self::STATUS_DISABLE => 'DISABLE',
    ];

    public static $type_map = [
        self::TYPE_PC => 'PC',
        self::TYPE_ANDROID => '安卓',
        self::TYPE_SDK => 'SDK',
    ];

    /**
     * 重新定义新数组   处理空格
     */
    public static function getNewTmp($tmp){
        $new_tmp = [];
        $new_tmp['collector_id'] = trim($tmp[0]);
        $new_tmp['nx_name'] = trim($tmp[1]);
        $new_tmp['password'] = trim($tmp[2]);
        $new_tmp['status'] = trim($tmp[3]);
        $new_tmp['type'] = trim($tmp[4]);
        return $new_tmp;
    }

    public static function queryNxAdmin($collector_id = '', $type = '' ){
        return self::findOne(['collector_id' => $collector_id, 'type' => $type, 'status'=>self::STATUS_ENABLE]);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_nx_user}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['collector_id', 'nx_name', 'password', 'status', 'type'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'collector_id' => 'collector_id',
            'nx_name' => 'nx_name',
            'password' => 'password',
            'status' => 'status',
            'type' => 'type',
            'created_at' => 'created time',
            'updated_at' => 'updated time',
        ];
    }

}