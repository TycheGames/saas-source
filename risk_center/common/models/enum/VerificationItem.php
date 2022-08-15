<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class VerificationItem
 * @package common\models\enum
 *
 * @method static VerificationItem START_ITEM()
 * @method static VerificationItem END_ITEM()
 * @method static VerificationItem IDENTITY()
 * @method static VerificationItem BASIC()
 * @method static VerificationItem WORK()
 * @method static VerificationItem EKYC()
 * @method static VerificationItem ADDRESS()
 * @method static VerificationItem CONTACT()
 * @method static VerificationItem TAX_BILL()
 * @method static VerificationItem CREDIT_REPORT()
 * @method static VerificationItem FACE_COMPARE()
 * @method static VerificationItem LANGUAGE()
 */
class VerificationItem extends Enum
{
    private const START_ITEM = 0; //开始
    private const IDENTITY = 1;  //身份认证
    private const BASIC = 2; //基本信息
    private const WORK = 3; //工作认证
    private const EKYC = 4; //EKYC认证
    private const CONTACT = 5; //联系人数据
    private const TAX_BILL = 6; //税单
    private const CREDIT_REPORT = 7; //征信报告
    private const FACE_COMPARE = 8;//人脸对比，复借时
    private const ADDRESS = 10; //地址证明
    private const LANGUAGE = 11; //语言问题
    private const END_ITEM = 9; //结束


}