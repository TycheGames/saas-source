<?php

namespace common\models\user;

use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserLoanOrderRepaymentExternal;
use common\models\question\UserQuestionVerification;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
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
class LoanPersonExternal extends ActiveRecord
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
        return Yii::$app->get('db_loan');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function isAllPlatformNewCustomer(string $panCode): bool
    {
        $userInside = self::find()
            ->where(['pan_code' => $panCode])
            ->andWhere(['customer_type' => LoanPerson::CUSTOMER_TYPE_OLD])
            ->exists();

        if ($userInside) {
            return false;
        }

        //查询Loan数据库
        $userOutside = LoanPerson::find()
            ->where(['pan_code' => $panCode])
            ->andWhere(['customer_type' => LoanPerson::CUSTOMER_TYPE_OLD])
            ->exists();

        if ($userOutside) {
            return false;
        }

        $tag = UserOldCustomerTag::find()->where(['pan_code' => $panCode])->exists();
        if($tag)
        {
            return false;
        }

        return true;
    }

    public static function isAllPlatformNewCustomerByTime(string $panCode, int $checkTime): bool
    {
        $userInside = UserLoanOrderRepayment::find()
            ->alias('r')
            ->select('r.*')
            ->leftJoin(LoanPerson::tableName() . ' as p', 'r.user_id = p.id')
            ->where(['p.pan_code' => $panCode])
            ->andWhere(['between', 'r.closing_time', 1, $checkTime])
            ->exists();

        if ($userInside) {
            return false;
        }

        //查询Loan数据库
        $userOutside = UserLoanOrderRepaymentExternal::find()
            ->alias('r')
            ->select('r.*')
            ->leftJoin(LoanPersonExternal::tableName() . ' as p', 'r.user_id = p.id')
            ->where(['p.pan_code' => $panCode])
            ->andWhere(['between', 'r.closing_time', 1, $checkTime])
            ->exists();

        if ($userOutside) {
            return false;
        }

        return true;
    }
}
