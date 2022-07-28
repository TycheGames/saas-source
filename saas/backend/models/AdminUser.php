<?php
namespace backend\models;

use common\helpers\RedisQueue;
use common\helpers\Util;
use common\services\message\WeWorkService;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\base\Exception;
/**
 * AdminUser model
 *
 * @property integer $id
 * @property string $username
 * @property string $phone
 * @property string $password
 * @property string $role
 * @property string $created_user
 * @property integer $open_status
 * @property integer $merchant_id
 * @property int $nx_phone
 * @property string $to_view_merchant_id
 * @property string $mark
 * @property integer $created_at
 * @property integer $updated_at
 */
class AdminUser extends ActiveRecord implements IdentityInterface {
    // 超级管理员角色标识
    const SUPER_ROLE        = 'super_admin';
    // 超级管理员固定用户名
    const SUPER_USERNAME    = 'admin';

    const IS_LOAN_COLLECTION = 1;//是催收员

    const OPEN_STATUS_ON = 1;   //启用
    const OPEN_STATUS_OFF = 0; //已删除
    const OPEN_STATUS_LOCK = 3; //已锁定

    const CAN_NOT_NX_PHONE = 0;
    const CAN_NX_PHONE     = 1;

    static $open_status_list = [
        self::OPEN_STATUS_ON => 'open',
        self::OPEN_STATUS_OFF => 'close',
        self::OPEN_STATUS_LOCK => 'lock',
    ];

    static $can_use_nx_phone_map = [
        self::CAN_NOT_NX_PHONE => 'can\'t use nx phone',
        self::CAN_NX_PHONE => 'can login nx phone',
    ];

    static $usable_status = [
        self::OPEN_STATUS_ON,
        self::OPEN_STATUS_LOCK,
    ];

    private static $extra_condition = ['open_status' => [1,3]];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }

    public static function getDb() {
        return Yii::$app->get('db');
    }

    public static function getDb_rd() {
        return Yii::$app->get('db');
    }


    public static function phone($phone = ''){
        return self::findOne(['phone' => $phone,'open_status'=>self::OPEN_STATUS_ON]);
    }

    public static function id($uid = ''){
        return self::findOne(['id' => $uid,'open_status'=>[self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK]]);
    }

    public static function change_phone($newNum='', $uid=0){
        if (empty($uid))
            return false;

        $item = self::id($uid);
        if (empty($item)) {
            throw new \Exception("No account, no change of mobile phone number");
            return false;
        }
        $item->phone = $newNum;

        return $item->save(false);
    }

    public static function change_open_status($uid=0, $status=1){
        if (empty($uid))
            return false;

        $item = self::id($uid);
        if (empty($item)) {
            throw new \Exception("No account, no change of open status");
            return false;
        }
        $item->open_status = $status;

        return $item->save(false);
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['mark','merchant_id', 'nx_phone', 'to_view_merchant_id'], 'safe'],
            [['username', 'phone', 'role', 'password'], 'required'],
            ['username', 'match', 'pattern' => '/^[0-9A-Za-z_]{1,30}$/i', 'message' => 'User names can only be 1-30-bit letters, numbers, or underscores'],
            [['username'], 'unique'],
            ['phone', 'match', 'pattern' => '/^((0091|91|0){0,1}([6-9]{1}[0-9]{9})|(1[0-9]{10}))$/', 'message' => 'Incorrect format of mobile phone number'],
            ['password', 'string', 'length' => [6, 16], 'message' => 'Password 6-16 bits character or number', 'tooShort'=>'Password 6-16 bits character or number', 'tooLong'=>'Password 6-16 bits character or number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'username' => 'username',
            'phone' => 'phone',
            'merchant_id' => 'merchant',
            'nx_phone' => 'nx_phone',
            'role' => 'role',
            'mark' => 'remark/full name',
            'password' => 'password',
            'created_user' => 'creater',
            'created_at' => 'created time',
            'updated_at' => 'updated time',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne(self :: addExtraCondition(['id' => $id]));
    }
    public static function getName($id)
    {
        $info = self::findIdentity($id);
        return empty($info) ? '--': $info['username'];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }


    /**
     * @param $username
     * @return AdminUser
     */
    public static function findByUsername($username) {
        /** @var AdminUser $admin */
        $admin = self::find()->where(['username' => $username, 'open_status'=>[self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK]])->one();
        return $admin;
    }

    public static function findByPhone($phone) {
        return static::findOne(['phone' => $phone,'open_status'=>[self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK]]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * 判断是否是超级管理员
     */
    public function getIsSuperAdmin() {
        $roleArr = explode(',',$this->role);
        return in_array(self::SUPER_ROLE,$roleArr);
    }

    /**
     * 合并查询 查询条件增加open_status 返回数组
     */
    public static function addExtraCondition(Array $condition_arr) {
        if(!empty(self::$extra_condition)) {
            if(!empty($condition_arr)) {
                return array_merge($condition_arr, self::$extra_condition);
            }
            return self::$extra_condition;
        }
        return $condition_arr;
    }

    /**
     * 合并查询 查询条件增加open_status 返回sql string
     */
    public static function addExtraConditionSql() {
        $sql = '';
        $condition_arr = self::addExtraCondition([]);
        if(!empty($condition_arr)) {
            foreach ($condition_arr as $key => $value) {
                $sql .= " AND {$key} = {$value}";
            }
        }
        return $sql;
    }

    /**
     * 判断用户是否是信审人员
     * @param $admin_uid int
     * @return bool
     */
    public static function isCreditOfficer($admin_uid) {
        $admin_user = AdminUser::find()
            ->where([
                'role' => 'order_op',
                'id' => $admin_uid,
                'open_status'=>self::OPEN_STATUS_ON
            ])
            ->asArray()
            ->one();
        return !empty($admin_user);
    }

    /**
     * 密码错误统计与账号锁定
     */
    public static function errorAndLock($user)
    {
        $message = 'The account is locked, please contact the administrator!';
        $key        = 'error_password_backend_'.$user->username;
        $error_num  = RedisQueue::inc([$key, 1]);
        $time       = 12 * 60 * 60;
        RedisQueue::expire([$key, $time]);
        if($error_num > 6)
        {
            //将账号状态改为锁定
            self::change_open_status($user->id, self::OPEN_STATUS_LOCK);
            if (YII_ENV_PROD) {
                $weWorkService = new WeWorkService();
                $weWorkMessage = "运营后台账户名称{$user->username},密码多次错误,账户被锁定.";
                $weWorkService->send($weWorkMessage);
            }
            return $message;
        }
        return false;
    }
}
