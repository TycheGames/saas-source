<?php
namespace callcenter\models;

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
 * @property integer $callcenter
 * @property integer $open_status
 * @property integer $can_dispatch
 * @property integer $open_search_label 是否开启订单搜索标签
 * @property integer $login_app 是否能登录催收app
 * @property integer $nx_phone 是否能使用pc牛信
 * @property string $mark
 * @property integer $outside
 * @property integer $group
 * @property integer $group_game
 * @property int $merchant_id
 * @property string $to_view_merchant_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $real_name
 * @property string $job_number
 *
 * @property string $master_user_id 作为副手时 替换上级用户的id
 */
class AdminUser extends ActiveRecord implements IdentityInterface
{
    //作为副手时，所继承的leader id
    public $master_user_id;

	// 超级管理员角色标识
	const SUPER_ROLE		= 'superadmin';
	// 超级管理员固定用户名
	const SUPER_USERNAME	= 'admin';

    const SYSTEM_ROLE		= 'system';

	// 手机验证正则表达式
	const PHONE_PATTERN = '/^1[0-9]{10}$/';

    const IS_LOAN_COLLECTION = 1;//是催收员
    const IS_TELEMARKETING= 2;//是电销人员
    const OPEN_STATUS_ON = 1;   //启用
    const OPEN_STATUS_OFF = 0; //已删除
    const OPEN_STATUS_LOCK = 3; //已锁定

    static $open_status_list = [
        self::OPEN_STATUS_ON => 'open',
        self::OPEN_STATUS_OFF => 'close',
        self::OPEN_STATUS_LOCK => 'lock',
    ];

    static $usable_status = [
        self::OPEN_STATUS_ON,
        self::OPEN_STATUS_LOCK,
    ];

    const CAN_ONT_DISPATCH = 0;
    const CAN_DISPATCH = 1;
    static $can_dispatch_list = [
        self::CAN_ONT_DISPATCH => 'can\'t dispatch',
        self::CAN_DISPATCH => 'can dispatch',
    ];

    const CAN_ONT_SEARCH_LABEL = 0;
    const CAN_SEARCH_LABEL = 1;

    static $can_search_label_map = [
        self::CAN_ONT_SEARCH_LABEL => 'can\'t search label',
        self::CAN_SEARCH_LABEL => 'can search label',
    ];

    const CAN_NOT_LOGIN_APP = 0;
    const CAN_LOGIN_APP = 1;

    static $can_login_app_map = [
        self::CAN_NOT_LOGIN_APP => 'can\'t login app',
        self::CAN_LOGIN_APP => 'can login app',
    ];

    const CAN_NOT_NX_PHONE = 0;
    const CAN_NX_PHONE     = 1;

    static $can_use_nx_phone_map = [
        self::CAN_NOT_NX_PHONE => 'can\'t use nx phone',
        self::CAN_NX_PHONE => 'can login nx phone',
    ];

    const GROUP_GAME_ONE = 1;
    const GROUP_GAME_TWO = 2;
    const GROUP_GAME_THREE = 3;
    const GROUP_GAME_FOUR = 4;
    const GROUP_GAME_FIVE = 5;
    const GROUP_GAME_SIX = 6;
    const GROUP_GAME_SEVEN = 7;
    const GROUP_GAME_EIGHT = 8;
    const GROUP_GAME_NINE = 9;
    const GROUP_GAME_TEN = 10;
    const GROUP_GAME_ELEVEN = 11;
    const GROUP_GAME_TWELVE = 12;
    const GROUP_GAME_THIRTEEN = 13;
    const GROUP_GAME_FOURTEEN = 14;
    const GROUP_GAME_FIFTEEN = 15;
    const GROUP_GAME_SIXTEEN = 16;
    const GROUP_GAME_SEVENTEEN = 17;
    const GROUP_GAME_EIGHTEEN = 18;
    const GROUP_GAME_NINETEEN = 19;
    const GROUP_GAME_TWENTY = 20;
    const GROUP_GAME_TWENTY_ONE = 21;
    const GROUP_GAME_TWENTY_TWO = 22;
    const GROUP_GAME_TWENTY_THREE  = 23;
    const GROUP_GAME_TWENTY_FOUR = 24;
    const GROUP_GAME_TWENTY_FIVE = 25;
    const GROUP_GAME_TWENTY_SIX = 26;
    const GROUP_GAME_TWENTY_SEVEN = 27;
    const GROUP_GAME_TWENTY_EIGHT = 28;
    const GROUP_GAME_TWENTY_NINE = 29;
    const GROUP_GAME_THIRTY = 30;
    const GROUP_GAME_31 = 31;
    const GROUP_GAME_32 = 32;
    const GROUP_GAME_33 = 33;
    const GROUP_GAME_34 = 34;
    const GROUP_GAME_35 = 35;
    const GROUP_GAME_36 = 36;
    const GROUP_GAME_37 = 37;
    const GROUP_GAME_38 = 38;
    const GROUP_GAME_39 = 39;
    const GROUP_GAME_40 = 40;


    public static $group_games = [
        self::GROUP_GAME_ONE=>'team 1',
        self::GROUP_GAME_TWO=>'team 2',
        self::GROUP_GAME_THREE=>'team 3',
        self::GROUP_GAME_FOUR=>'team 4',
        self::GROUP_GAME_FIVE=>'team 5',
        self::GROUP_GAME_SIX=>'team 6',
        self::GROUP_GAME_SEVEN=>'team 7',
        self::GROUP_GAME_EIGHT=>'team 8',
        self::GROUP_GAME_NINE=>'team 9',
        self::GROUP_GAME_TEN=>'team 10',
        self::GROUP_GAME_ELEVEN=>'team 11',
        self::GROUP_GAME_TWELVE=>'team 12',
        self::GROUP_GAME_THIRTEEN=>'team 13',
        self::GROUP_GAME_FOURTEEN=>'team 14',
        self::GROUP_GAME_FIFTEEN=>'team 15',
        self::GROUP_GAME_SIXTEEN=>'team 16',
        self::GROUP_GAME_SEVENTEEN=>'team 17',
        self::GROUP_GAME_EIGHTEEN=>'team 18',
        self::GROUP_GAME_NINETEEN=>'team 19',
        self::GROUP_GAME_TWENTY=>'team 20',
        self::GROUP_GAME_TWENTY_ONE=>'team 21',
        self::GROUP_GAME_TWENTY_TWO=>'team 22',
        self::GROUP_GAME_TWENTY_THREE=>'team 23',
        self::GROUP_GAME_TWENTY_FOUR=>'team 24',
        self::GROUP_GAME_TWENTY_FIVE=>'team 25',
        self::GROUP_GAME_TWENTY_SIX=>'team 26',
        self::GROUP_GAME_TWENTY_SEVEN=>'team 27',
        self::GROUP_GAME_TWENTY_EIGHT=>'team 28',
        self::GROUP_GAME_TWENTY_NINE=>'team 29',
        self::GROUP_GAME_THIRTY=>'team 30',
        self::GROUP_GAME_31 => 'team 31',
        self::GROUP_GAME_32 => 'team 32',
        self::GROUP_GAME_33 => 'team 33',
        self::GROUP_GAME_34 => 'team 34',
        self::GROUP_GAME_35 => 'team 35',
        self::GROUP_GAME_36 => 'team 36',
        self::GROUP_GAME_37 => 'team 37',
        self::GROUP_GAME_38 => 'team 38',
        self::GROUP_GAME_39 => 'team 39',
        self::GROUP_GAME_40 => 'team 40',
    ];

    public function load($data, $formName = null, $strategyOperating = false)
    {
        if(!$strategyOperating){
            unset($data['open_search_label']);
            unset($data['login_app']);
            unset($data['nx_phone']);
            unset($data['real_name']);
            unset($data[$this->formName()]['open_search_label']);
            unset($data[$this->formName()]['login_app']);
            unset($data[$this->formName()]['nx_phone']);
            unset($data[$this->formName()]['real_name']);
            unset($data[$this->formName()]['job_number']);
        }
        return parent::load($data, $formName); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }

    /**
     * 验证催收公司下是否有催收人存在
     */
    public static function HasPerson($companyId){
        $condition = " outside={$companyId} and open_status!=".static::OPEN_STATUS_OFF;
        return self::find()->where($condition)->select('id')->scalar();
    }

  

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }


    public static function phone($num = ''){
        return self::findOne(['phone' => $num,'open_status'=>self::OPEN_STATUS_ON]);
    }

    public static function username($username){
        return self::findOne(['username' => $username,'open_status'=>self::OPEN_STATUS_ON]);
    }

     public static function id($uid = ''){
         return self::findOne(['id' => $uid,'open_status'=>[self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK]]);
    }

    /**
     *返回用户角色
     *@return array
     */
    public static function admin_roles($uid){
        $user = self::id($uid);
        $roles = $user->role;
        $arr = explode(',', $roles);
        if(empty($arr)) return array();
        foreach ($arr as $key => $item) {
            $arr[$key] = trim($item);
        }
        return $arr;
    }

    

    public static function change_phone($newNum='', $uid=0){
        if(empty($uid)) return false;

        $item = self::id($uid);
        if(empty($item)){
            throw new Exception("不存在账户，无法更换手机号");
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
    	return [
            [['mark', 'to_view_merchant_id','outside', 'group','group_game','open_search_label','login_app','nx_phone','job_number'], 'safe'],
    		[['username', 'phone', 'role', 'password',  'merchant_id'], 'required'],
    		['username', 'match', 'pattern' => '/^[0-9A-Za-z_]{1,30}$/i', 'message' => 'User names can only be 1-30 bits of letters, Numbers, or underscores'],
    		['username', 'unique'],
            ['real_name', 'safe'],
            ['role','validateRole'],
            ['phone', 'match', 'pattern' => '/^(([6-9]{1})|(1[0-9]{1}))[0-9]{9}$/', 'message' => 'Wrong format of phone number'],
            ['password', 'match', 'pattern' => '/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{8,16}$/', 'message' => 'Must contain numbers and uppercase and lowercase letters and be 8 to 16 digits long!'],    	];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
    	return [
            'id' => 'Admin ID',
    		'username' => 'Username',
    		'phone' => 'Phone',
            'role' => 'Role',
    		'mark' => 'Remark/Username',
    		'password' => 'Password',
            'created_user' => 'Created user',
            'outside' => 'Company',
            'group' => 'Group',
            'group_game' => 'Team',
            'merchant_id' => 'Merchant',
            'to_view_merchant_id' => 'To View Merchant',
            'open_search_label' => 'Search label',
            'login_app' => 'App',
            'nx_phone' => 'nx_phone',
            'created_at' => 'Created time',
            'updated_at' => 'Updated time',
            'real_name' => 'real name',
            'job_number' => 'job number',
    	];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $user = static::findOne(['id' => $id,'open_status'=> static::$usable_status]);
        if($user){
            $cacheKey = sprintf('%s:%s:%s', RedisQueue::TEAM_LEADER_SLAVER_CACHE, date('Y-m-d'), $id);
            $masterAdminId = RedisQueue::get(['key' => $cacheKey]);
            if($masterAdminId){
                //为副手 且 符合条件时  替换主账号权限
                $masterUser = static::findOne(['id' => $masterAdminId,'open_status'=> static::$usable_status]);
                if($masterUser){
                    return AdminUserMasterSlaverRelation::slaveInheritMasterPermission($user,$masterUser);
                }
            }
        }
        return $user;
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    	throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username,'open_status'=>self::$usable_status]);
    }

    public static function findByPhone($phone)
    {
        return static::findOne(['phone' => $phone,'open_status'=>self::$usable_status]);
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
     * Validates the validateRole.
     */

    public function validateRole() {
       $roleArr = explode(',',$this->role);
       $res = AdminUserRole::find()->select(['groups'])->where(['name' => $roleArr])->asArray()->all();
       $groups = array_column($res,'groups');
       foreach ($groups as $group){
           $checkFields = AdminUserRole::$groups_level_list[$group] ?? [];
           foreach ($checkFields as $field){
               if($this->$field > 0){

               }else{
                   $this->addError($field, 'this role '.$field.' error!');
               }
           }
       }
    }
    /**
     * 判断是否是超级管理员
     */
    public function getIsSuperAdmin()
    {
        return $this->role == self::SUPER_ROLE;
    }

    /**
     * 重新定义新数组   处理空格
     */
    public static function getNewTmp($tmp){
        $new_tmp = [];
        if(isset($tmp[0])){
            $new_tmp['outside'] = trim($tmp[0]);
        }
        if(isset($tmp[1])){
            $new_tmp['group'] = trim($tmp[1]);
        }
        if(isset($tmp[2])){
            $new_tmp['group_game'] = trim($tmp[2]);
        }
        if(isset($tmp[3])){
            $new_tmp['username'] = trim($tmp[3]);
        }
        if(isset($tmp[4])){
            $new_tmp['phone'] = trim($tmp[4]);
        }
        if(isset($tmp[5])){
            $new_tmp['password'] = trim($tmp[5]);
        }
        if(isset($tmp[6])){
            $new_tmp['open_search_label'] = trim($tmp[6]);
        }
        if(isset($tmp[7])){
            $new_tmp['login_app'] = trim($tmp[7]);
        }
        if(isset($tmp[8])){
            $new_tmp['nx_phone'] = trim($tmp[8]);
        }
        if(isset($tmp[9])){
            $new_tmp['real_name'] = trim($tmp[9]);
        }
        if(isset($tmp[10])){
            $new_tmp['job_number'] = trim($tmp[10]);
        }
        return $new_tmp;
    }

    /**
     * 密码错误统计与账号锁定
     */
    public static function errorAndLock($user)
    {
        $message = 'The account is locked, please contact the administrator!';
        $key        = 'error_password_callcenter_'.$user->username;
        $error_num  = RedisQueue::inc([$key, 1]);
        $time       = 12 * 60 * 60;
        RedisQueue::expire([$key, $time]);
        if($error_num > 6)
        {
            //将账号状态改为锁定
            self::change_open_status($user->id, self::OPEN_STATUS_LOCK);
            if (YII_ENV_PROD) {
                $weWorkService = new WeWorkService();
                $weWorkMessage = "催收后台账户名称{$user->username},密码多次错误,账户被锁定.";
                $weWorkService->send($weWorkMessage);
            }
            return $message;
        }
        return false;
    }


    /**
     * @name AdminUser 检查催收员可否操作该商户的订单
     * @params int $merchantId 订单商户
     * @return bool
     */
    public function checkCollectorOrderMerchant($merchantId){
        if ($this->merchant_id > 0) {
            if($merchantId == $this->merchant_id){
                return true;
            }
        } else {
            if (!empty($this->to_view_merchant_id)) {
                $merchantIds = explode(',', $this->to_view_merchant_id);
                if(in_array($merchantId,$merchantIds)){
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * 取小组成员
     * @param string $user_id
     */
    public static function getTeamMember($user_id)
    {
        $user = self::findIdentity($user_id);
        $roleGroup = AdminUserRole::getGroupByRoles($user->role);
        $team = [];
        $teamMember = array();
        if(AdminUserRole::TYPE_SMALL_TEAM_MANAGER == $roleGroup){
            $teamMember = self::find()->where(['open_status' => [self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK], 'outside' =>$user->outside, 'group'=>$user->group, 'group_game'=>$user->group_game, 'role' => 'collection'])->asArray()->all();
        }else{
            $adminManagerRelation = AdminManagerRelation::find()
                ->select(['group','group_game'])
                ->where(['admin_id' => $user_id])
                ->asArray()
                ->all();
            $orWhereArr = [];
            foreach ($adminManagerRelation as $val){
                $orWhereArr[] = ['group' => $val['group'], 'group_game' => $val['group_game']];
            }
            if($orWhereArr){
                $orWhereArr = array_merge(['OR'],$orWhereArr);
                $teamMember = self::find()->where(['open_status' => [self::OPEN_STATUS_ON,self::OPEN_STATUS_LOCK], 'outside' =>$user->outside])->andWhere($orWhereArr)->asArray()->all();
            }
        }
        foreach($teamMember as $v)
        {
            if($v['id'] == $user_id){
                continue;
            }
            $team[$v['id']] = $v['username'];
        }
        return $team;
    }
}
