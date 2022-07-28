<?php
namespace common\models\risk;
use common\models\order\UserLoanOrder;
use common\models\question\UserQuestionVerification;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserBasicInfo;
use common\models\user\UserContact;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserCreditReportOcrPan;
use common\models\user\UserPanCheckLog;
use common\models\user\UserWorkInfo;
use yii\base\Model;


/**
 * 风控数据容器
 * Class RiskDataContainer
 * @package common\models\risk
 * @property UserLoanOrder $order 订单类
 * @property LoanPerson $loanPerson 用户类
 * @property UserWorkInfo $userWorkInfo 用户工作信息类
 * @property UserBasicInfo $userBasicInfo 用户基本信息类
 * @property UserBankAccount $userBankAccount 用户绑卡信息类
 * @property UserContact $userContact 用户紧急联系人类
 * @property UserCreditReportOcrAad $userAadhaarReport Aadhaar报告
 * @property UserCreditReportOcrPan $userPanReport  Pan报告
 * @property UserPanCheckLog $userPanVerifyReport  Pan验真报告
 * @property UserCreditReportFrLiveness $userFrReport  Fr报告
 * @property UserCreditReportFrVerify $userFrFrReport 人脸对比报告
 * @property UserCreditReportFrVerify $userFrPanReport 人脸与Pan对比报告
 * @property UserCreditReportFrVerify $userFrCompareReport 人脸对比报告
 * @property UserQuestionVerification $userQuestionReport 语言认证报告
 */
class RiskDataContainer extends Model {

    public $loanPerson,$order,$userWorkInfo,$userBasicInfo,$userBankAccount,$userContact;
    public $userAadhaarReport,$userPanReport,$userPanVerifyReport,
        $userFrReport,$userFrFrReport,$userFrPanReport,$userQuestionReport,$userFrCompareReport;

    /**
     * 字段验证规则
     * @return array
     */
    public function rules(){
        return [
            ['loanPerson','required', 'when' => function($model){
                if(!$model->loanPerson instanceof LoanPerson){
                    $this->addError('loanPerson', '非LoanPerson实例');
                }
            }],
            ['userWorkInfo','required', 'when' => function($model){
                if(!$model->userWorkInfo instanceof UserWorkInfo){
                    $this->addError('userWorkInfo', '非UserWorkInfo实例');
                }
            }],
            ['userBasicInfo','required', 'when' => function($model){
                if(!$model->userBasicInfo instanceof UserBasicInfo){
                    $this->addError('userBasicInfo', '非userBasicInfo实例');
                }
            }],
            [
                [
                    'userFrReport', 'userPanVerifyReport', 'userPanReport', 'userAadhaarReport',
                    'order', 'userBankAccount', 'userContact', 'userFrFrReport',
                    'userFrPanReport','userQuestionReport','userFrCompareReport'
                ],
                'safe'
            ]
        ];
    }

}