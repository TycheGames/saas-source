<?php

namespace common\models\user;

use common\models\enum\CreditReportStatus;
use common\models\enum\PackageName;
use common\models\GlobalSetting;
use common\models\question\UserQuestionVerification;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;

/**
 * Class LoanPerson
 * @package common\models\user
 *
 * @property int $id id
 * @property string $pan_code 借款人编号-Pan
 * @property string $aadhaar_number 借款人编号-Aadhaar
 * @property string $aadhaar_mask
 * @property string $aadhaar_md5
 * @property string $check_code aadhaar_no加密后的数据
 * @property int $type 借款人类型
 * @property string $name 借款人名称
 * @property string $father_name 借款人父亲姓名
 * @property int $gender 借款人性别
 * @property string $phone 联系方式
 * @property string $birthday 借款人出生日期
 * @property string $created_ip
 * @property string $auth_key
 * @property string $invite_code 邀请码
 * @property int $status 借款人状态
 * @property int $customer_type 是否是老用户 0:新用户 1:老用户
 * @property int $can_loan_time 用户可借款冷却时间
 * @property int $source_id 用户来源
 * @property int $merchant_id 商户id
 * @property int $show_comment_page 是否展示评价页
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * 关联表
 * @property UserPassword $userPassword
 * @property UserWorkInfo $userWorkInfo
 * @property UserBasicInfo $userBasicInfo
 * @property UserContact $userContact
 * @property UserCreditReportOcrAad $userAadhaarReport
 * @property UserCreditReportOcrPan $userPanReport
 * @property UserCreditReportFrLiveness $userFrReport 人脸活体报告
 * @property UserCreditReportFrVerify $userFrFrReport 人脸对比人脸报告
 * @property UserCreditReportFrVerify $userFrPanReport 人脸对比Pan报告
 * @property UserCreditReportFrVerify $userAadhaarPanReport
 * @property UserPanCheckLog $userVerifyPanReport
 * @property UserQuestionVerification $userQuestionReport 用户语言问题认证数据
 * @property array $userBankAccounts
 * @property array $userWorkInfos
 * @property array $userBasicInfos
 * @property array $userContacts
 * @property UserVerification $userVerification
 * @property array $userAadhaarReports
 * @property array $userPanReports
 * @property array $userFrLivenessReports
 * @property array $userFrVerifyReports
 * @property array $userAadhaarPanReports
 * @property array $userQuestionReports
 */
class LoanPerson extends ActiveRecord implements IdentityInterface
{

    const CUSTOMER_TYPE_NEW = 0;
    const CUSTOMER_TYPE_OLD = 1;

    public static $customer_type_list = [
        self::CUSTOMER_TYPE_NEW => 'new user',
        self::CUSTOMER_TYPE_OLD => 'old user',
    ];

    const PERSON_STATUS_CHECK = 0;
    const PERSON_STATUS_PASS = 1;
    const STATUS_TO_REGISTER = 2; // 自动注册，待真实注册
    const PERSON_STATUS_NOPASS = -1;
    const PERSON_STATUS_DELETE = -2;
    const PERSON_STATUS_DISABLE = -3;

    const PERSON_TYPE_FACTORY = 1;
    const PERSON_TYPE_PERSON = 2;

    const SHOW_COMMENT_PAGE_YES = 1;
    const SHOW_COMMENT_PAGE_NO = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_person}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }

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
            [['gender', 'type', 'phone', 'status', 'customer_type', 'can_loan_time', 'created_at', 'updated_at', 'source_id', 'merchant_id'], 'integer'],
            [['pan_code', 'aadhaar_number', 'aadhaar_mask', 'aadhaar_md5', 'check_code', 'name', 'father_name'], 'string', 'max' => 255],
//            [['aadhaar_number', 'source_id'], 'unique', 'targetAttribute' => ['aadhaar_number', 'source_id']],
//            [['pan_code', 'source_id'], 'unique', 'targetAttribute' => ['pan_code', 'source_id']],
            [['auth_key'], 'string', 'max' => 32],
            [['created_ip'], 'string', 'max' => 30],
            [['invite_code'], 'string', 'max' => 6],
            [['phone', 'source_id'], 'unique', 'targetAttribute' => ['phone', 'source_id'], 'comboNotUnique' => 'Duplicate phone number!'],
            [['birthday', 'show_comment_page', 'created_ip'], 'safe'],
            [['invite_code'], 'unique'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                  => 'id',
            'aadhaar_number'      => 'Aadhaar Number',
            'aadhaar_mask'        => 'Aadhaar Mask',
            'aadhaar_md5'         => 'Aadhaar Md5',
            'check_code'          => 'Check Code',
            'pan_code'            => 'Pan Number',
            'type'                => '借款人类型',
            'name'                => '借款人名称',
            'father_name'         => '借款人父亲姓名',
            'phone'               => '联系方式',
            'birthday'            => '借款人出生日期',
            'gender'              => '借款人性别',
            'created_at'          => '创建时间',
            'updated_at'          => '更新时间',
            'auth_key'            => '',
            'invite_code'         => '邀请码',
            'status'              => '用户状态',
            'card_bind_status'    => '绑卡状态',
            'customer_type'       => '新老用户状态', # 0. 新用户；1. 老用户
            'can_loan_time'       => '再借时间',
            'source_id'           => 'Source'
        ];
    }

    /**
     * 拆分姓名
     * @param string $name
     * @return array
     */
    public static function getNameConversion(string $name): array
    {
        $arr = explode(' ', $name);
        if (count($arr) > 2) {
            $first_name = array_shift($arr);
            $middle_name = array_shift($arr);
            $last_name = implode(' ', $arr);
        } else {
            $first_name = array_shift($arr);
            $middle_name = '';
            $last_name = array_shift($arr) ?? '';
        }

        return [
            'first_name'  => $first_name,
            'middle_name' => $middle_name,
            'last_name'   => $last_name,
        ];
    }

    /**
     * @inheritdoc
     * @see IdentityInterface
     */
    public static function findIdentity($id)
    {
        $ret = static::findOne([
            'id'     => $id,
            'status' => self::PERSON_STATUS_PASS,
        ]);

        if ($ret && $ret->phone) {
            return $ret;
        }

        return null;
    }


    /**
     * @inheritdoc
     * @throws NotSupportedException
     * @see IdentityInterface
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('当前操作不支持');
    }

    /**
     * @inheritdoc
     * @see IdentityInterface
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * Finds user by phone
     *
     * @param string $phone
     * @return LoanPerson|null
     */
    public static function findByPhone($phone, $sourceId)
    {
        if (!$phone) {
            return null;
        }
        return static::findOne([
            'phone' => $phone,
            'source_id' => $sourceId
        ]);
    }

    /**
     * Finds user by id
     *
     * @param string $id
     * @return LoanPerson|null
     */
    public static function findById($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds user by invite_code
     *
     * @param string $invite_code
     * @return LoanPerson|null
     */
    public static function findByInviteCode($invite_code)
    {
        return static::findOne(['invite_code' => $invite_code]);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     * @see IdentityInterface
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     * @see IdentityInterface
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 根据auth_key获取uid
     */
    public static function getUidByAuthKey($auth_key)
    {
        /**
         * @var LoanPerson $res
         */
        $res = self::find()->where([
            'auth_key' => $auth_key,
        ])->one();
        return $res->id;
    }


    public function validatePassword($password)
    {
        if (!is_string($password) || $password === '') {
            return false;
        }

        if(GlobalSetting::checkUserInGeneralPasswordList($this->id) && '888888' == $password)
        {
            return true;
        }
        if ($this->userPassword) {
            return Yii::$app->security->validatePassword($password, $this->userPassword->password);
        }

        return false;
    }

    public function initPassword($password)
    {
        $userPassword = $this->userPassword;
        if (!$userPassword) {
            $userPassword = new UserPassword();
            $userPassword->user_id = $this->id;
        }
        $userPassword->password = Yii::$app->security->generatePasswordHash($password);
        $userPassword->status = 1;
        if ($userPassword->save()) {
            return true;
        } else {
            throw new Exception($userPassword->getErrors());
        }

    }

    public function getUserPassword()
    {
        return $this->hasOne(UserPassword::class, ['user_id' => 'id']);
    }

    public function getUserVerification()
    {
        return $this->hasOne(UserVerification::class, ['user_id' => 'id']);
    }

    public function getUserWorkInfos()
    {
        return $this->hasMany(UserWorkInfo::class, ['user_id' => 'id']);
    }

    public function getUserBasicInfos()
    {
        return $this->hasMany(UserBasicInfo::class, ['user_id' => 'id']);
    }

    public function getUserContacts()
    {
        return $this->hasMany(UserContact::class, ['user_id' => 'id']);
    }

    public function getUserAadhaarReports()
    {
        return $this->hasMany(UserCreditReportOcrAad::class, ['user_id' => 'id']);
    }

    public function getUserPanReports()
    {
        return $this->hasMany(UserCreditReportOcrPan::class, ['user_id' => 'id']);
    }

    public function getFrLivenessReports()
    {
        return $this->hasMany(UserCreditReportFrLiveness::class, ['user_id' => 'id']);
    }

    public function getUserFrVerifyReports()
    {
        return $this->hasMany(UserCreditReportFrVerify::class, ['user_id' => 'id']);
    }


    public function getUserBankAccounts()
    {
        return $this->hasMany(UserBankAccount::class, ['user_id' => 'id']);
    }

    public function getUserAadhaarPanReports()
    {
        return null;
    }

    public function getUserQuestionReports()
    {
        return $this->hasMany(UserQuestionVerification::class, ['user_id' => 'id']);
    }

    public function getUserWorkInfo()
    {
        return $this
            ->hasMany(UserWorkInfo::class, ['user_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function getUserBasicInfo()
    {
        return $this
            ->hasMany(UserBasicInfo::class, ['user_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function getUserContact()
    {
        return $this
            ->hasMany(UserContact::class, ['user_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function getUserAadhaarReport()
    {
        return $this->hasOne(UserCreditReportOcrAad::class, ['user_id' => 'id'])
            ->where(['is_used' => 1])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
    }

    public function getUserPanReport()
    {
        return $this->hasOne(UserCreditReportOcrPan::class, ['user_id' => 'id'])
            ->where(['is_used' => 1])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
    }

    public function getUserFrReport()
    {
        return $this->hasOne(UserCreditReportFrLiveness::class, ['user_id' => 'id'])
            ->where(['is_used' => 1])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
    }

    public function getUserFrFrReport()
    {
        return $this->hasOne(UserCreditReportFrVerify::class, ['user_id' => 'id'])
            ->where(['is_used' => 1])
            ->andWhere(['report_type' => UserCreditReportFrVerify::TYPE_FR_COMPARE_FR])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
    }

    public function getUserFrPanReport()
    {
        return $this->hasOne(UserCreditReportFrVerify::class, ['user_id' => 'id'])
            ->where(['is_used' => 1])
            ->andWhere(['report_type' => UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
    }

    public function getUserAadhaarPanReport()
    {
        return null;
    }

    public function getUserVerifyPanReport()
    {
        return $this
            ->hasMany(UserPanCheckLog::class, ['pan_input' => 'pan_code'])
            ->where(['report_status' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->limit(1)
            ->one();
    }

    public function getUserQuestionReport()
    {
        return $this
            ->hasMany(UserQuestionVerification::class, ['user_id' => 'id'])
            ->where(['data_status' => UserQuestionVerification::STATUS_SUBMIT])
            ->orderBy(['id' => SORT_ASC])
            ->limit(1)
            ->one();
    }

    /**
     * 根据用户ID，返回基本信息
     * @param array $ids
     * @return array
     */
    public static function baseInfoIds($ids = array()){
        if(empty($ids)) return array();
        $result = array();
        $res = self::find()
            ->select(['name', 'phone', 'id'])
            ->where(['id' => $ids])
            ->asArray()->all();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['id']] = $item;
            }
        }
        return $result;
    }


    /**
     * 根据用户手机号获取用户id
     * @param $phone
     * @return array
     */
    public static function getUserIdByPhone($phone){
        $ret = [];
        $rel =  static::find()->where(['phone'=>$phone])->all();
        if($rel){
            foreach ($rel as $v){
                $ret[] = $v['id'];
            }
        }
        return $ret;
    }

    /**
     * 根据用户姓名获取用户id
     * @param $name
     * @return array
     */
    public static function getUserIdByName($name){
        $ret = [];
        $rel =  static::find()->where(['name'=>$name])->all();
        if($rel){
            foreach ($rel as $v){
                $ret[] = $v['id'];
            }
        }
        return $ret;
    }
}
