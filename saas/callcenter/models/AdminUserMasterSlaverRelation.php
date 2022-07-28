<?php
namespace callcenter\models;

use common\helpers\RedisQueue;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * TeamRoleMasterSlaveRelation model
 *
 * @property integer $id
 * @property integer $admin_id
 * @property integer $slave_admin_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class AdminUserMasterSlaverRelation extends ActiveRecord
{
    //状态
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 0;


    public static $status_map = [
        self::STATUS_ENABLE => '启用',
        self::STATUS_DISABLE => '禁用',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_master_slave_relation}}';
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
            TimestampBehavior::class,
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $todayDate = date('Y-m-d');
        if($this->slave_admin_id > 0){
            //设置副手时  重新更新副手权限标识
            if(CollectorClassSchedule::find()
                ->where([
                    'admin_id' => $this->admin_id,
                    'status' => CollectorClassSchedule::STATUS_OPEN,
                    'date' => $todayDate,
                    'type' => CollectorClassSchedule::$absence_type_back_today_list
                ])->exists()){

                $tomorrowTime = strtotime('today') + 86400;
                $cacheKey = sprintf('%s:%s:%s', RedisQueue::TEAM_LEADER_SLAVER_CACHE, $todayDate, $this->slave_admin_id);

                RedisQueue::set([
                    'expire' => $tomorrowTime - time(),
                    'key'    => $cacheKey,
                    'value'  => $this->admin_id
                ]);
            }
        }

        if(!$insert) {
            if(isset($changedAttributes['slave_admin_id'])){
                $cacheKey = sprintf('%s:%s:%s', RedisQueue::TEAM_LEADER_SLAVER_CACHE, $todayDate, $changedAttributes['slave_admin_id']);
                RedisQueue::del(['key' => $cacheKey]);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id'], 'required'],
            [['admin_id', 'slave_admin_id'], 'integer'],
            ['slave_admin_id','validateSlave'],
        ];
    }

    public static $inheritPermissionAttributes = [
        'role','outside','group','group_game'
    ];

    public static function slaveInheritMasterPermission(AdminUser $slaveUser,AdminUser $masterUser){
        $slaveUser->master_user_id = $masterUser->id;
        foreach (self::$inheritPermissionAttributes as $key){
            $slaveUser->$key = $masterUser->$key;
        }
        return $slaveUser;
    }

    public function validateSlave(){
        if($this->slave_admin_id > 0){
            if(self::find()->where(['slave_admin_id' => $this->slave_admin_id])->exists()){
                $this->addError('slave_admin_id', 'this person can\'t bind!');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'admin_id',
            'slave_admin_id' => 'slave_admin_id',
            'created_at' => 'created time',
            'updated_at' => 'updated time',
        ];
    }

}