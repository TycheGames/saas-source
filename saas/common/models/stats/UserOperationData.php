<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * Class UserOperationData
 * @package common\models\stats
 * @property string date 日期
 * @property int type 用户操作汇总类型
 * @property int app_market 渠道
 * @property int num
 * @property int created_at
 * @property int updated_at
 */
class UserOperationData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%user_operation_data}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    const TYPE_USER_REGISTER = 1;   //注册数


    /////////////////阿里数据type///////////////////
    //获取基本信息
    const TYPE_GET_BASIC_INFO_UV = 101;  //basic进入uv
    const TYPE_GET_BASIC_INFO_PV = 102;  //basic进入pv

    const TYPE_SUBMIT_BASIC_UV = 103;  //basic提交uv
    const TYPE_SUBMIT_BASIC_PV = 104;  //basic提交pv
    //保存基本信息
    const TYPE_SUBMIT_BASIC_SUCCESS_UV = 105;  //basic提交成功uv
    const TYPE_SUBMIT_BASIC_SUCCESS_PV = 106;  //basic提交成功pv
    const TYPE_SUBMIT_BASIC_FAIL_UV = 107;  //basic提交失败uv
    const TYPE_SUBMIT_BASIC_FAIL_PV = 108;  //basic提交失败pv

    //获取用户KYC
    const TYPE_GET_KYC_INFO_UV = 201;  //KYC进入uv   get_kyc_info
    const TYPE_GET_KYC_INFO_PV = 202;  //KYC进入Pv

    const TYPE_SUBMIT_KYC_UV = 203;  //KYC提交uv
    const TYPE_SUBMIT_KYC_PV = 204;  //KYC提交pv
    //保存用户KYC
    const TYPE_SUBMIT_KYC_SUCCESS_UV = 205;  //KYC提交成功uv
    const TYPE_SUBMIT_KYC_SUCCESS_PV = 206;  //KYC提交成功pv
    const TYPE_SUBMIT_KYC_FAIL_UV = 207;  //KYC提交失败uv
    const TYPE_SUBMIT_KYC_FAIL_PV = 208;  //KYC提交失败pv

    //获取联系人信息
    const TYPE_GET_CONTACT_INFO_UV = 301;  //CONTACT进入uv  //get_contact_info
    const TYPE_GET_CONTACT_INFO_PV = 302;  //CONTACT进入pv
    const TYPE_SUBMIT_CONTACT_UV = 303;  //CONTACT提交uv //contact_info
    const TYPE_SUBMIT_CONTACT_PV = 304;  //CONTACT提交pv
    //保存联系人信息
    const TYPE_SUBMIT_CONTACT_SUCCESS_UV = 305;  //CONTACT提交成功uv //contact_info
    const TYPE_SUBMIT_CONTACT_SUCCESS_PV = 306;  //CONTACT提交成功pv
    const TYPE_SUBMIT_CONTACT_FAIL_UV = 307;  //CONTACT提交失败uv
    const TYPE_SUBMIT_CONTACT_FAIL_PV = 308;  //CONTACT提交失败pv

    //获取用户银行卡列表
    const TYPE_GET_BANK_INFO_UV = 401;  //bindcard进入uv  //get_bank_info
    const TYPE_GET_BANK_INFO_PV = 402;  //bindcard进入pv
    const TYPE_SUBMIT_BANK_UV = 403;  //bindcard提交uv  //bank_info 绑卡uv
    const TYPE_SUBMIT_BANK_PV = 404;  //bindcard提交pv
    //绑卡
    const TYPE_SUBMIT_BANK_SUCCESS_UV = 405;  //bindcard提交成功uv  //bank_info 绑卡uv
    const TYPE_SUBMIT_BANK_SUCCESS_PV = 406;  //bindcard提交成功pv
    const TYPE_SUBMIT_BANK_FAIL_UV = 407;  //bindcard提交失败uv
    const TYPE_SUBMIT_BANK_FAIL_PV = 408;  //bindcard提交失败pv

    //绑卡校验
    const TYPE_BANK_VERIFY_UV = 409;  //bindcard校验uv  //bank_verify 绑卡uv
    const TYPE_BANK_VERIFY_PV = 410;  //bindcard校验pv
    const TYPE_BANK_VERIFY_SUCCESS_UV = 411;  //bindcard校验成功uv
    const TYPE_BANK_VERIFY_SUCCESS_PV = 412;  //bindcard校验成功pv
    const TYPE_BANK_VERIFY_FAIL_UV = 413;  //bindcard校验失败uv
    const TYPE_BANK_VERIFY_FAIL_PV = 414;  //bindcard校验失败pv

    //活体检查
    const TYPE_SUBMIT_FR_INFO_UV = 501;  //fr_info提交uv  //fr_info
    const TYPE_SUBMIT_FR_INFO_PV = 502;  //fr_info提交pv
    const TYPE_SUBMIT_FR_INFO_SUCCESS_UV = 503;  //fr_info提交成功uv
    const TYPE_SUBMIT_FR_INFO_SUCCESS_PV = 504;  //fr_info提交成功pv
    const TYPE_SUBMIT_FR_INFO_FAIL_UV = 505;  //fr_info提交失败uv
    const TYPE_SUBMIT_FR_INFO_FAIL_PV = 506;  //fr_info提交失败pv

    //人脸对比
    const TYPE_SUBMIT_FR_VER_UV = 507;  //fr_ver_info提交uv  //fr_ver_info
    const TYPE_SUBMIT_FR_VER_PV = 508;  //fr_ver_info提交pv
    const TYPE_SUBMIT_FR_VER_SUCCESS_UV = 509;  //fr_ver_info提交成功uv
    const TYPE_SUBMIT_FR_VER_SUCCESS_PV = 510;  //fr_ver_info提交成功pv
    const TYPE_SUBMIT_FR_VER_FAIL_UV = 511;  //fr_ver_info提交失败uv
    const TYPE_SUBMIT_FR_VER_FAIL_PV = 512;  //fr_ver_info提交失败pv

    const TYPE_SUBMIT_OLD_FR_VER_UV = 513;  //old_fr_ver_info提交uv  //old_fr_ver_info
    const TYPE_SUBMIT_OLD_FR_VER_PV = 514;  //old_fr_ver_info提交pv
    const TYPE_SUBMIT_OLD_FR_VER_SUCCESS_UV = 515;  //old_fr_ver_info提交成功uv
    const TYPE_SUBMIT_OLD_FR_VER_SUCCESS_PV = 516;  //old_fr_ver_info提交成功pv
    const TYPE_SUBMIT_OLD_FR_VER_FAIL_UV = 517;  //old_fr_ver_info提交失败uv
    const TYPE_SUBMIT_OLD_FR_VER_FAIL_PV = 518;  //old_fr_ver_info提交失败pv

    //pan ocr
    const TYPE_SUBMIT_PAN_INFO_UV = 601;  //pan_info提交uv  //pan_info
    const TYPE_SUBMIT_PAN_INFO_PV = 602;  //pan_info提交pv
    const TYPE_SUBMIT_PAN_INFO_SUCCESS_UV = 603;  //pan_info提交成功uv
    const TYPE_SUBMIT_PAN_INFO_SUCCESS_PV = 604;  //pan_info提交成功pv
    const TYPE_SUBMIT_PAN_INFO_FAIL_UV = 605;  //pan_info提交失败uv
    const TYPE_SUBMIT_PAN_INFO_FAIL_PV = 606;  //pan_info提交失败pv

    //pan 验真
    const TYPE_SUBMIT_PAN_VER_UV = 607;  //pan_ver_info提交uv  //pan_ver_info
    const TYPE_SUBMIT_PAN_VER_PV = 608;  //pan_ver_info提交pv
    const TYPE_SUBMIT_PAN_VER_SUCCESS_UV = 609;  //pan_ver_info提交成功uv
    const TYPE_SUBMIT_PAN_VER_SUCCESS_PV = 610;  //pan_ver_info提交成功pv
    const TYPE_SUBMIT_PAN_VER_FAIL_UV = 611;  //pan_ver_info提交失败uv
    const TYPE_SUBMIT_PAN_VER_FAIL_PV = 612;  //pan_ver_info提交失败pv

    //aadhaar ocr
    const TYPE_SUBMIT_AAD_INFO_UV = 701;  //aad_info提交uv  //add_info
    const TYPE_SUBMIT_AAD_INFO_PV = 702;  //aad_info提交pv
    const TYPE_SUBMIT_AAD_INFO_SUCCESS_UV = 703;  //aad_info提交成功uv
    const TYPE_SUBMIT_AAD_INFO_SUCCESS_PV = 704;  //aad_info提交成功pv
    const TYPE_SUBMIT_AAD_INFO_FAIL_UV = 705;  //aad_info提交失败uv
    const TYPE_SUBMIT_AAD_INFO_FAIL_PV = 706;  //aad_info提交失败pv

    //aadhaar 验真
    const TYPE_SUBMIT_AAD_VER_UV = 707;  //aad_ver_info提交uv  //AAD_ver_info
    const TYPE_SUBMIT_AAD_VER_PV = 708;  //aad_ver_info提交pv
    const TYPE_SUBMIT_AAD_VER_SUCCESS_UV = 709;  //aad_ver_info提交成功uv
    const TYPE_SUBMIT_AAD_VER_SUCCESS_PV = 710;  //aad_ver_info提交成功pv
    const TYPE_SUBMIT_AAD_VER_FAIL_UV = 711;  //aad_ver_info提交失败uv
    const TYPE_SUBMIT_AAD_VER_FAIL_PV = 712;  //aad_ver_info提交失败pv

    const TYPE_THREE_ELEMENT_VERIFY = 890;  //三要素认证成功数
    const TYPE_NEW_USER_APPLY_ORDER = 901;   //新客申请订单数
    const TYPE_NEW_USER_APPLY_BY_PERSON = 911;  //新客申请订人数
    const TYPE_NEW_USER_RISK_PASS = 908;  //新客风控通过的的订单数

    const TYPE_USER_RISK_CREDIT_PASS = 902;  //风控通过（机审通过）的订单数
    const TYPE_USER_MANUAL_CREDIT_PASS = 909;  //风控通过（信审通过）的订单数

    ///风控通过
    ///     ->风控通过（未进人工信审，机审通过）
    ///         ->(卡未认证)人工卡认证审核
    ///             ->（审核通过）放款中
    ///         ->(已认证)放款中
    ///
    ///     ->风控通过（未进人工信审，机审通过）
    ///         ->(卡未认证)人工卡认证审核
    ///             ->（审核通过）放款中
    ///         ->(已认证)放款中
    const TYPE_USER_RISK_PASS_TO_BANK_AUDIT = 903;  //进人工卡认证审核的订单数
    const TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS = 904;  //卡认证审核通过的订单数
    const TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS = 905; //卡认证审核被拒的订单数
    const TYPE_LOAN_ORDER = 906;            //放款人数
    const TYPE_LOAN_ORDER_SUCCESS = 907;  //放款成功人数
    const TYPE_NEW_LOAN_ORDER_SUCCESS = 910;  //放款成功人数

    public static $ali_log_type_map = [
        self::TYPE_GET_BASIC_INFO_UV => 'get_basic_info_uv',
        self::TYPE_GET_BASIC_INFO_PV => 'get_basic_info_pv',
        self::TYPE_SUBMIT_BASIC_UV => 'basic_info_uv',
        self::TYPE_SUBMIT_BASIC_PV => 'basic_info_pv',
        self::TYPE_SUBMIT_BASIC_SUCCESS_UV => 'basic_info_success_uv',
        self::TYPE_SUBMIT_BASIC_SUCCESS_PV => 'basic_info_success_pv',
        self::TYPE_SUBMIT_BASIC_FAIL_UV => 'basic_info_fail_uv',
        self::TYPE_SUBMIT_BASIC_FAIL_PV => 'basic_info_fail_pv',

        self::TYPE_GET_KYC_INFO_UV => 'get_kyc_info_uv',
        self::TYPE_GET_KYC_INFO_PV => 'get_kyc_info_pv',
        self::TYPE_SUBMIT_KYC_UV => 'kyc_info_uv',
        self::TYPE_SUBMIT_KYC_PV => 'kyc_info_pv',
        self::TYPE_SUBMIT_KYC_SUCCESS_UV => 'kyc_info_success_uv',
        self::TYPE_SUBMIT_KYC_SUCCESS_PV => 'kyc_info_success_pv',
        self::TYPE_SUBMIT_KYC_FAIL_UV => 'kyc_info_fail_uv',
        self::TYPE_SUBMIT_KYC_FAIL_PV => 'kyc_info_fail_pv',

        self::TYPE_GET_CONTACT_INFO_UV => 'get_contact_info_uv',
        self::TYPE_GET_CONTACT_INFO_PV => 'get_contact_info_pv',
        self::TYPE_SUBMIT_CONTACT_UV => 'contact_info_uv',
        self::TYPE_SUBMIT_CONTACT_PV => 'contact_info_pv',
        self::TYPE_SUBMIT_CONTACT_SUCCESS_UV => 'contact_info_success_uv',
        self::TYPE_SUBMIT_CONTACT_SUCCESS_PV => 'contact_info_success_pv',
        self::TYPE_SUBMIT_CONTACT_FAIL_UV => 'contact_info_fail_uv',
        self::TYPE_SUBMIT_CONTACT_FAIL_PV => 'contact_info_fail_pv',

        self::TYPE_GET_BANK_INFO_UV => 'get_bank_info_uv',
        self::TYPE_GET_BANK_INFO_PV => 'get_bank_info_pv',
        self::TYPE_SUBMIT_BANK_UV => 'bank_info_uv',
        self::TYPE_SUBMIT_BANK_PV => 'bank_info_pv',
        self::TYPE_SUBMIT_BANK_SUCCESS_UV => 'bank_info_success_uv',
        self::TYPE_SUBMIT_BANK_SUCCESS_PV => 'bank_info_success_pv',
        self::TYPE_SUBMIT_BANK_FAIL_UV => 'bank_info_fail_uv',
        self::TYPE_SUBMIT_BANK_FAIL_PV => 'bank_info_fail_pv',

        self::TYPE_BANK_VERIFY_UV => 'bank_verify_uv',
        self::TYPE_BANK_VERIFY_PV => 'bank_verify_pv',
        self::TYPE_BANK_VERIFY_SUCCESS_UV => 'bank_verify_success_uv',
        self::TYPE_BANK_VERIFY_SUCCESS_PV => 'bank_verify_success_pv',
        self::TYPE_BANK_VERIFY_FAIL_UV => 'bank_verify_fail_uv',
        self::TYPE_BANK_VERIFY_FAIL_PV => 'bank_verify_fail_pv',

        self::TYPE_SUBMIT_FR_INFO_UV => 'fr_info_uv',
        self::TYPE_SUBMIT_FR_INFO_PV => 'fr_info_pv',
        self::TYPE_SUBMIT_FR_INFO_SUCCESS_UV => 'fr_info_success_uv',
        self::TYPE_SUBMIT_FR_INFO_SUCCESS_PV => 'fr_info_success_pv',
        self::TYPE_SUBMIT_FR_INFO_FAIL_UV => 'fr_info_fail_uv',
        self::TYPE_SUBMIT_FR_INFO_FAIL_PV => 'fr_info_fail_pv',

        self::TYPE_SUBMIT_FR_VER_UV => 'fr_ver_info_uv',
        self::TYPE_SUBMIT_FR_VER_PV => 'fr_ver_info_pv',
        self::TYPE_SUBMIT_FR_VER_SUCCESS_UV => 'fr_ver_info_success_uv',
        self::TYPE_SUBMIT_FR_VER_SUCCESS_PV => 'fr_ver_info_success_pv',
        self::TYPE_SUBMIT_FR_VER_FAIL_UV => 'fr_ver_info_fail_uv',
        self::TYPE_SUBMIT_FR_VER_FAIL_PV => 'fr_ver_info_fail_pv',

        self::TYPE_SUBMIT_OLD_FR_VER_UV => 'old_fr_ver_info_uv',
        self::TYPE_SUBMIT_OLD_FR_VER_PV => 'old_fr_ver_info_pv',
        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_UV => 'old_fr_ver_info_success_uv',
        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_PV => 'old_fr_ver_info_success_pv',
        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_UV => 'old_fr_ver_info_fail_uv',
        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_PV => 'old_fr_ver_info_fail_pv',

        self::TYPE_SUBMIT_PAN_INFO_UV => 'pan_info_uv',
        self::TYPE_SUBMIT_PAN_INFO_PV => 'pan_info_pv',
        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV => 'pan_info_success_uv',
        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_PV => 'pan_info_success_pv',
        self::TYPE_SUBMIT_PAN_INFO_FAIL_UV => 'pan_info_fail_uv',
        self::TYPE_SUBMIT_PAN_INFO_FAIL_PV => 'pan_info_fail_pv',

        self::TYPE_SUBMIT_PAN_VER_UV => 'pan_ver_info_uv',
        self::TYPE_SUBMIT_PAN_VER_PV => 'pan_ver_info_pv',
        self::TYPE_SUBMIT_PAN_VER_SUCCESS_UV => 'pan_ver_info_success_uv',
        self::TYPE_SUBMIT_PAN_VER_SUCCESS_PV => 'pan_ver_info_success_pv',
        self::TYPE_SUBMIT_PAN_VER_FAIL_UV => 'pan_ver_info_fail_uv',
        self::TYPE_SUBMIT_PAN_VER_FAIL_PV => 'pan_ver_info_fail_pv',

        self::TYPE_SUBMIT_AAD_INFO_UV => 'aad_info_uv',
        self::TYPE_SUBMIT_AAD_INFO_PV => 'aad_info_pv',
        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV => 'aad_info_success_uv',
        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_PV => 'aad_info_success_pv',
        self::TYPE_SUBMIT_AAD_INFO_FAIL_UV => 'aad_info_fail_uv',
        self::TYPE_SUBMIT_AAD_INFO_FAIL_PV => 'aad_info_fail_pv',

        self::TYPE_SUBMIT_AAD_VER_UV => 'aad_ver_info_uv',
        self::TYPE_SUBMIT_AAD_VER_PV => 'aad_ver_info_pv',
        self::TYPE_SUBMIT_AAD_VER_SUCCESS_UV => 'aad_ver_info_success_uv',
        self::TYPE_SUBMIT_AAD_VER_SUCCESS_PV => 'aad_ver_info_success_pv',
        self::TYPE_SUBMIT_AAD_VER_FAIL_UV => 'aad_ver_info_fail_uv',
        self::TYPE_SUBMIT_AAD_VER_FAIL_PV => 'aad_ver_info_fail_pv'
    ];

    public static $type_name_map = [
        self::TYPE_USER_REGISTER => '注册',
        self::TYPE_GET_BASIC_INFO_UV => 'basic进入uv',
        self::TYPE_GET_BASIC_INFO_PV => 'basic进入pv',

        self::TYPE_SUBMIT_BASIC_UV => 'basic提交',
        self::TYPE_SUBMIT_BASIC_PV => 'basic提交',
        self::TYPE_SUBMIT_BASIC_SUCCESS_UV => 'basic提交成功uv',
        self::TYPE_SUBMIT_BASIC_SUCCESS_PV => 'basic提交成功pv',
        self::TYPE_SUBMIT_BASIC_FAIL_UV => 'basic提交失败uv',
        self::TYPE_SUBMIT_BASIC_FAIL_PV => 'basic提交失败pv',

        self::TYPE_GET_KYC_INFO_UV => 'KYC进入uv',
        self::TYPE_GET_KYC_INFO_PV => 'KYC进入Pv',
        self::TYPE_SUBMIT_KYC_UV => 'kyc提交uv',
        self::TYPE_SUBMIT_KYC_PV => 'kyc提交pv',
        self::TYPE_SUBMIT_KYC_SUCCESS_UV => 'KYC提交成功uv',
        self::TYPE_SUBMIT_KYC_SUCCESS_PV => 'KYC提交成功pv',
        self::TYPE_SUBMIT_KYC_FAIL_UV => 'KYC提交失败uv',
        self::TYPE_SUBMIT_KYC_FAIL_PV => 'KYC提交失败pv',

        self::TYPE_GET_CONTACT_INFO_UV => '联系人进入uv',
        self::TYPE_GET_CONTACT_INFO_PV => '联系人进入pv',
        self::TYPE_SUBMIT_CONTACT_UV => '联系人提交uv',
        self::TYPE_SUBMIT_CONTACT_PV => '联系人提交pv',
        self::TYPE_SUBMIT_CONTACT_SUCCESS_UV => '联系人提交成功uv',
        self::TYPE_SUBMIT_CONTACT_SUCCESS_PV => '联系人提交成功pv',
        self::TYPE_SUBMIT_CONTACT_FAIL_UV => '联系人提交失败uv',
        self::TYPE_SUBMIT_CONTACT_FAIL_PV => '联系人提交失败pv',

        self::TYPE_GET_BANK_INFO_UV => '绑卡进入uv',
        self::TYPE_GET_BANK_INFO_PV => '绑卡进入pv',
        self::TYPE_SUBMIT_BANK_UV => '绑卡提交uv',
        self::TYPE_SUBMIT_BANK_PV => '绑卡提交pv',
        self::TYPE_SUBMIT_BANK_SUCCESS_UV => '绑卡提交成功uv',
        self::TYPE_SUBMIT_BANK_SUCCESS_PV => '绑卡提交成功pv',
        self::TYPE_SUBMIT_BANK_FAIL_UV => '绑卡提交失败uv',
        self::TYPE_SUBMIT_BANK_FAIL_PV => '绑卡提交失败pv',

        self::TYPE_BANK_VERIFY_UV => '绑卡校验uv',
        self::TYPE_BANK_VERIFY_PV => '绑卡校验pv',
        self::TYPE_BANK_VERIFY_SUCCESS_UV => '绑卡校验success_uv',
        self::TYPE_BANK_VERIFY_SUCCESS_PV => '绑卡校验success_pv',
        self::TYPE_BANK_VERIFY_FAIL_UV => '绑卡校验fail_uv',
        self::TYPE_BANK_VERIFY_FAIL_PV => '绑卡校验fail_pv',


//        self::TYPE_SUBMIT_FR_INFO_UV => 'fr_info提交uv',
//        self::TYPE_SUBMIT_FR_INFO_PV => 'fr_info提交pv',
//        self::TYPE_SUBMIT_FR_INFO_UV => 'fr_info提交uv',
//        self::TYPE_SUBMIT_FR_INFO_PV => 'fr_info提交pv',
//        self::TYPE_SUBMIT_FR_INFO_SUCCESS_UV => 'fr_info提交成功uv',
//        self::TYPE_SUBMIT_FR_INFO_SUCCESS_PV => 'fr_info提交成功pv',
//        self::TYPE_SUBMIT_FR_INFO_FAIL_UV => 'fr_info提交失败uv',
//        self::TYPE_SUBMIT_FR_INFO_FAIL_PV => 'fr_info提交失败pv',
//
//        self::TYPE_SUBMIT_FR_VER_UV => 'fr_ver_info提交uv',
//        self::TYPE_SUBMIT_FR_VER_PV => 'fr_ver_info提交pv',
//        self::TYPE_SUBMIT_FR_VER_SUCCESS_UV => 'fr_ver_info提交成功uv',
//        self::TYPE_SUBMIT_FR_VER_SUCCESS_PV => 'fr_ver_info提交成功pv',
//        self::TYPE_SUBMIT_FR_VER_FAIL_UV => 'fr_ver_info提交失败uv',
//        self::TYPE_SUBMIT_FR_VER_FAIL_PV => 'fr_ver_info提交失败pv',
//
//        self::TYPE_SUBMIT_OLD_FR_VER_UV => 'old_fr_ver_info提交uv',
//        self::TYPE_SUBMIT_OLD_FR_VER_PV => 'old_fr_ver_info提交pv',
//        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_UV => 'old_fr_ver_info提交成功uv',
//        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_PV => 'old_fr_ver_info提交成功pv',
//        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_UV => 'old_fr_ver_info提交失败uv',
//        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_PV => 'old_fr_ver_info提交失败pv',
//
//        self::TYPE_SUBMIT_PAN_INFO_UV => 'pan_info提交uv',
//        self::TYPE_SUBMIT_PAN_INFO_PV => 'pan_info提交pv',
//        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV => 'pan_info提交成功uv',
//        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_PV => 'pan_info提交成功pv',
//        self::TYPE_SUBMIT_PAN_INFO_FAIL_UV => 'pan_info提交失败uv',
//        self::TYPE_SUBMIT_PAN_INFO_FAIL_PV => 'pan_info提交失败pv',
//
//        self::TYPE_SUBMIT_PAN_VER_UV => 'pan_ver_info提交uv',
//        self::TYPE_SUBMIT_PAN_VER_PV => 'pan_ver_info提交pv',
//        self::TYPE_SUBMIT_PAN_VER_SUCCESS_UV => 'pan_ver_info提交成功uv',
//        self::TYPE_SUBMIT_PAN_VER_SUCCESS_PV => 'pan_ver_info提交成功pv',
//        self::TYPE_SUBMIT_PAN_VER_FAIL_UV => 'pan_ver_info提交失败uv',
//        self::TYPE_SUBMIT_PAN_VER_FAIL_PV => 'pan_ver_info提交失败pv',
//
//        self::TYPE_SUBMIT_AAD_INFO_UV => 'aad_info提交uv',
//        self::TYPE_SUBMIT_AAD_INFO_PV => 'aad_info提交pv',
//        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV => 'add_info提交成功uv',
//        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_PV => 'add_info提交成功pv',
//        self::TYPE_SUBMIT_AAD_INFO_FAIL_UV => 'add_info提交失败uv',
//        self::TYPE_SUBMIT_AAD_INFO_FAIL_PV => 'add_info提交失败pv',
//
//        self::TYPE_SUBMIT_AAD_VER_UV => 'aad_ver_info提交uv',
//        self::TYPE_SUBMIT_AAD_VER_PV => 'aad_ver_info提交pv',
//        self::TYPE_SUBMIT_AAD_VER_SUCCESS_UV => 'aad_ver_info提交成功uv',
//        self::TYPE_SUBMIT_AAD_VER_SUCCESS_PV => 'aad_ver_info提交成功pv',
//        self::TYPE_SUBMIT_AAD_VER_FAIL_UV => 'aad_ver_info提交失败uv',
//        self::TYPE_SUBMIT_AAD_VER_FAIL_PV => 'aad_ver_info提交失败pv',
        self::TYPE_THREE_ELEMENT_VERIFY => '三要素认证成功数',

        self::TYPE_NEW_USER_APPLY_ORDER => '新客申请订单数',   //
        self::TYPE_NEW_USER_APPLY_BY_PERSON => '新客申请人数',   //
        self::TYPE_NEW_USER_RISK_PASS => '新客风控通过的的订单数',
        self::TYPE_USER_RISK_CREDIT_PASS  => '风控通过（机审通过）的订单数',
        self::TYPE_USER_MANUAL_CREDIT_PASS => '风控通过（人工信审通过）的订单数',
        self::TYPE_USER_RISK_PASS_TO_BANK_AUDIT => '进人工卡认证审核的订单数',
        self::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS => '卡认证审核通过的订单数',  //
        self::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS => '卡认证审核被拒的订单数', //
        self::TYPE_LOAN_ORDER => '放款人数',           //
        self::TYPE_LOAN_ORDER_SUCCESS => '放款成功人数',  //
        self::TYPE_NEW_LOAN_ORDER_SUCCESS => '新客放款成功人数',  //

    ];

    public static $kyc_type_name_map = [
        self::TYPE_GET_KYC_INFO_UV => 'KYC进入uv',
        self::TYPE_GET_KYC_INFO_PV => 'KYC进入Pv',
        self::TYPE_SUBMIT_KYC_UV => 'kyc提交uv',
        self::TYPE_SUBMIT_KYC_PV => 'kyc提交pv',
        self::TYPE_SUBMIT_KYC_SUCCESS_UV => 'KYC提交成功uv',
        self::TYPE_SUBMIT_KYC_SUCCESS_PV => 'KYC提交成功pv',
        self::TYPE_SUBMIT_KYC_FAIL_UV => 'KYC提交失败uv',
        self::TYPE_SUBMIT_KYC_FAIL_PV => 'KYC提交失败pv',

        self::TYPE_SUBMIT_FR_INFO_UV => 'fr_info提交uv',
        self::TYPE_SUBMIT_FR_INFO_PV => 'fr_info提交pv',
        self::TYPE_SUBMIT_FR_INFO_UV => 'fr_info提交uv',
        self::TYPE_SUBMIT_FR_INFO_PV => 'fr_info提交pv',
        self::TYPE_SUBMIT_FR_INFO_SUCCESS_UV => 'fr_info提交成功uv',
        self::TYPE_SUBMIT_FR_INFO_SUCCESS_PV => 'fr_info提交成功pv',
        self::TYPE_SUBMIT_FR_INFO_FAIL_UV => 'fr_info提交失败uv',
        self::TYPE_SUBMIT_FR_INFO_FAIL_PV => 'fr_info提交失败pv',

        self::TYPE_SUBMIT_FR_VER_UV => 'fr_ver_info提交uv',
        self::TYPE_SUBMIT_FR_VER_PV => 'fr_ver_info提交pv',
        self::TYPE_SUBMIT_FR_VER_SUCCESS_UV => 'fr_ver_info提交成功uv',
        self::TYPE_SUBMIT_FR_VER_SUCCESS_PV => 'fr_ver_info提交成功pv',
        self::TYPE_SUBMIT_FR_VER_FAIL_UV => 'fr_ver_info提交失败uv',
        self::TYPE_SUBMIT_FR_VER_FAIL_PV => 'fr_ver_info提交失败pv',

        self::TYPE_SUBMIT_OLD_FR_VER_UV => 'old_fr_ver_info提交uv',
        self::TYPE_SUBMIT_OLD_FR_VER_PV => 'old_fr_ver_info提交pv',
        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_UV => 'old_fr_ver_info提交成功uv',
        self::TYPE_SUBMIT_OLD_FR_VER_SUCCESS_PV => 'old_fr_ver_info提交成功pv',
        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_UV => 'old_fr_ver_info提交失败uv',
        self::TYPE_SUBMIT_OLD_FR_VER_FAIL_PV => 'old_fr_ver_info提交失败pv',

        self::TYPE_SUBMIT_PAN_INFO_UV => 'pan_info提交uv',
        self::TYPE_SUBMIT_PAN_INFO_PV => 'pan_info提交pv',
        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_UV => 'pan_info提交成功uv',
        self::TYPE_SUBMIT_PAN_INFO_SUCCESS_PV => 'pan_info提交成功pv',
        self::TYPE_SUBMIT_PAN_INFO_FAIL_UV => 'pan_info提交失败uv',
        self::TYPE_SUBMIT_PAN_INFO_FAIL_PV => 'pan_info提交失败pv',

        self::TYPE_SUBMIT_PAN_VER_UV => 'pan_ver_info提交uv',
        self::TYPE_SUBMIT_PAN_VER_PV => 'pan_ver_info提交pv',
        self::TYPE_SUBMIT_PAN_VER_SUCCESS_UV => 'pan_ver_info提交成功uv',
        self::TYPE_SUBMIT_PAN_VER_SUCCESS_PV => 'pan_ver_info提交成功pv',
        self::TYPE_SUBMIT_PAN_VER_FAIL_UV => 'pan_ver_info提交失败uv',
        self::TYPE_SUBMIT_PAN_VER_FAIL_PV => 'pan_ver_info提交失败pv',

        self::TYPE_SUBMIT_AAD_INFO_UV => 'aad_info提交uv',
        self::TYPE_SUBMIT_AAD_INFO_PV => 'aad_info提交pv',
        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_UV => 'add_info提交成功uv',
        self::TYPE_SUBMIT_AAD_INFO_SUCCESS_PV => 'add_info提交成功pv',
        self::TYPE_SUBMIT_AAD_INFO_FAIL_UV => 'add_info提交失败uv',
        self::TYPE_SUBMIT_AAD_INFO_FAIL_PV => 'add_info提交失败pv',

        self::TYPE_SUBMIT_AAD_VER_UV => 'aad_ver_info提交uv',
        self::TYPE_SUBMIT_AAD_VER_PV => 'aad_ver_info提交pv',
        self::TYPE_SUBMIT_AAD_VER_SUCCESS_UV => 'aad_ver_info提交成功uv',
        self::TYPE_SUBMIT_AAD_VER_SUCCESS_PV => 'aad_ver_info提交成功pv',
        self::TYPE_SUBMIT_AAD_VER_FAIL_UV => 'aad_ver_info提交失败uv',
        self::TYPE_SUBMIT_AAD_VER_FAIL_PV => 'aad_ver_info提交失败pv',
    ];
}
