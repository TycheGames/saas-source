<?php

namespace common\services\message;

use common\services\BaseService;

class SendMessageService extends BaseService
{

    /** @var FirebasePushService $pushService */
    public $pushService;
    public $sendSmsService;
    public $message;
    public $phone;
    public $userId;
    public $packageName;
    public $productName;

    public static $msgContent = [  //0->内部订单 1->外部订单 push开头的key为push消息
        '0' =>
            [   //30绑卡被拒30分钟后再次提醒绑卡  60绑卡被拒60分钟后再次提醒绑卡
                'loanSuccess' => "[ {{packageName}} ] Congratulations, the loan that you applied has been sent to your bank account. Also, you can continue to apply for a loan after repayment.",
                'loanReject' => "[ {{packageName}} APP ] Sorry! Your loan order is timeout. Please apply again. Withdraw in 1 minute.",
                'checkReject' => "[ {{packageName}} APP ] Sorry your application hasn't been approved! We look forward to your re-application.",
                'bindCardReject' => "[ {{packageName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name!",
                'fundReject' =>  "[ {{packageName}} APP ] Sorry! Your loan order is timeout. Please apply again. Withdraw in 1 minute.",
                'withdrawalReject' =>  "[ {{packageName}} ] Your withdraw is timeout! But we will reserve your credit amount. Pls apply again. Open APP to apply and withdraw!",
                'preRiskReject' =>  "[ {{packageName}} APP ] Sorry your application hasn't been approved! We look forward to your re-application.",
                'mainRiskReject' =>  "[ {{packageName}} APP ] Sorry your application hasn't been approved! We look forward to your re-application.",
                'repayComplete' =>  "[ {{packageName}} ] Congratulations, your loan has been paid off! Welcome to apply for your next loan.",
                'withdrawal0' => "[ {{packageName}} ] Congratulations! Your application has been approved, open APP and get money. Only in 1 minute!",  //待提现
                'withdrawal20' => "[ {{packageName}} ] Dear Customer, Congratulations! Your application is ready to withdraw! Open {{packageName}} and get money. Just in 1 minute!",
                'withdrawal40' => "[ {{packageName}} ] Dear Customer, your application has been approved. Open {{packageName}} APP for withdrawal cash.",
                'withdrawal60' => "[ {{packageName}} ] Your application has been approved. Open {{packageName}} APP to Withdraw Cash, {{packageName}} will transfer the loan to your bank account",
                'withdrawal120' => "[ {{packageName}}] Congratulations! your application is successful. Open {{packageName}} APP now to withdraw cash.",
                'withdrawal240' => "[ {{packageName}} ] Dear Customer, GOOD NEWS! Your credit amount has been updated. Open {{packageName}} APP to withdraw, just in 1 minute!",
                'riskVoice' => "Dear Customer, this is {{packageName}} APP platform. {{packageName}} APP remind you, your application has been approved. Open {{packageName}} APP to withdraw cash.",
                'pushLoanSuccess' => 'Congratulations, the loan that you applied has been sent to your bank account. By the way, you can continue to apply for a loan after repayment.',
                'pushLoanReject' => "[ {{packageName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushCheckReject' => "Sorry your application hasn't been approved! We look forward to your re-application.",
                'pushBindCardReject' => "[ {{packageName}} ] Your previous bank account is failed to verify. Please submit another new bank account under your name!",
                'pushFundReject' => "[ {{packageName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushWithdrawalReject' => "[ {{packageName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushPreRiskReject' => "Sorry your application hasn't been approved! We look forward to your re-application",
                'pushMainRiskReject' => "Sorry your application hasn't been approved! We look forward to your re-application.",
                'pushRepayComplete' => "Congratulations, your loan has been paid off! Welcome to continue to apply for a loan",
                'repeatedBorrowing' => "[ {{packageName}} APP ] Dear Customer, your credit limit has been updated! Welcome to apply for your next loan. You will get the money just in 5 minutes!",
                '30' => "[ {{packageName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name! ",
                '60' => "[ {{packageName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name! ",
                'pushRepeatedBorrowing' => "[ {{packageName}} APP ] Dear Customer, your credit limit has been updated! Welcome to apply for your next loan. You will get the money just in 5 minutes! ",
                'delayRepayment' => "[ {{packageName}} ] Your apply for partial deferral has been approved! The platform will stop launch collection service to you for 7 days. Pls repay in time after 7 days!"
            ],
        '1' =>
            [
                'loanSuccess' => "[ {{productName}} ] Congratulations, the [{{packageName}}] loan product that you applied has been sent to your bank account. Also, you can continue to apply for a loan after repayment.",
                'loanReject' => "[ {{productName}} APP ] Sorry! Your loan order of [{{packageName}}] is timeout. Please apply again. Withdraw in 1 minute.",
                'checkReject' => "[ {{productName}} APP ] Sorry, your application for [{{packageName}}] loan product has not been approved. Please try other loan product.",
                'bindCardReject' => "[ {{productName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name!",
                'fundReject' =>  "[ {{productName}} APP ] Sorry! Your loan order of [{{packageName}}] is timeout. Please apply again. Withdraw in 1 minute.",
                'withdrawalReject' =>  "[ {{productName}} ] Your withdraw from [{{packageName}}] loan product is timeout! But we will reserve your credit amount. Pls apply again. *Withdraw only 1 minute*",
                'preRiskReject' =>  "[ {{productName}} APP ] Sorry, your application for [{{packageName}}] loan product has not been approved. Please try other loan product.",
                'mainRiskReject' =>  "[ {{productName}} APP ] Sorry, your application for [{{packageName}}] loan product has not been approved. Please try other loan product.",
                'repayComplete' =>  "[ {{productName}} ] Congratulations, your loan product of [{{packageName}}] has been paid off! Welcome to apply for your next loan.",
                'withdrawal0' => "[ {{productName}} ] Congratulations! Your application of [{{packageName}}] loan product has been approved, open APP and get money. Only in 1 minute!", //待提现
                'withdrawal20' => "[ {{productName}} ] Dear Customer, Congratulations! Your application of [{{packageName}}] loan product is ready to withdraw! Open {{productName}} and get money. Just in 1 minute!",
                'withdrawal40' => "[ {{productName}} ] Dear Customer, your application of [{{packageName}}] loan product has been approved. Open {{productName}} APP for withdrawal cash.",
                'withdrawal60' => "[ {{productName}} ] Your application of [{{packageName}}] loan product has been approved. Open {{productName}} APP to Withdraw Cash, {{productName}} will transfer the loan to your bank account",
                'withdrawal120' => "[ {{productName}} ] Congratulations! your application of [{{packageName}}] loan product has been approved. Open {{productName}} APP to withdraw cash.",
                'withdrawal240' => "[ {{productName}} ] Dear Customer, GOOD NEWS! Your credit amount from [{{packageName}}] loan product has been updated. Open {{productName}} APP to withdraw, just in 1 minute! ",
                'riskVoice' => "Dear Customer, this is {{productName}} APP platform. {{productName}} APP remind you, your application has been approved. Open {{productName}} APP to withdraw cash.",
                'pushLoanSuccess' => "Congratulations, the loan that you applied has been sent to your bank account. By the way, you can continue to apply for a loan after repayment",
                'pushLoanReject' => "[ {{productName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushCheckReject' => "Sorry your application hasn't been approved! We look forward to your re-application.",
                'pushBindCardReject' => "[ {{productName}} ] Your previous bank account is failed to verify. Please submit another new bank account under your name! ",
                'pushFundReject' => "[ {{productName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushWithdrawalReject' => "[ {{productName}} ] Sorry! Bank's System Payment timed out and you need to APPLY again.",
                'pushPreRiskReject' => "Sorry your application hasn't been approved! We look forward to your re-application",
                'pushMainRiskReject' => "Sorry your application hasn't been approved! We look forward to your re-application.",
                'pushRepayComplete' => "Congratulations, your loan has been paid off! Welcome to continue to apply for a loan.",
                'repeatedBorrowing' => "[ {{productName}} APP ] Dear Customer, your [{{packageName}}] credit limit has been updated! Welcome to apply for your next loan. You will get the money just in 5 minutes! ",
                '30' => "[ {{productName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name! ",
                '60' => "[ {{productName}} APP ] Your previous bank account is failed to verify. Please submit another new bank account under your name! ",
                'pushRepeatedBorrowing' => "[ {{productName}} APP ] Dear Customer, your [{{packageName}}] credit limit has been updated! Welcome to apply for your next loan. You will get the money just in 5 minutes! ",
                'delayRepayment' => "[ {{packageName}} ] Your apply for partial deferral has been approved! The platform will stop launch collection service to you for 7 days. Pls repay in time after 7 days!"
            ],
    ];


    public function getMsgContent($is_export,$key)
    {
        $map = [
            '/{{packageName}}/' => $this->packageName,
            '/{{phone}}/' => $this->phone,
            '/{{productName}}/' => $this->productName
        ];
        $content = self::$msgContent[$is_export][$key];
        $content = preg_replace(array_keys($map), array_values($map), $content);
        return $content;

    }

    public static $smsConfigList = [
        'bigshark'      => 'smsService_LianDong_GigShark_OTP',
        'moneyclick'    => 'smsService_LianDong_MoneyClick_OTP',
        'lovecash'      => 'smsService_ChuangLan_LoveCash_OTP',
        'rupeefanta'    => 'smsService_LianDong_MoneyClick_OTP',
        'rupeelaxmi'    => 'smsService_LianDong_RupeeLaxmi_OTP',
        'dhancash'      => 'smsService_LianDong_DhanCash_OTP',
        'rupeecash'     => 'smsService_LianDong_RupeeCash_OTP',
        'cashcash'      => 'smsService_LianDong_CashCash_OTP',
        'excellentcash' => 'smsService_ChuangLan_ExcellentCash_OTP',
        'luckywallet'   => 'smsService_LianDong_Luckywallet_OTP',
        'cashalo'       => 'smsService_LianDong_MoneyClick_OTP',
        'newcash'       => 'smsService_ChuangLan_NewCash_OTP',
        'hindmoney'     => 'smsService_LianDong_GigShark_OTP',
        'mamaloan'      => 'smsService_LianDong_MamaLoan_OTP',
        'wealthsteward' => 'smsService_Nxtele_WealthSteward_OTP',
        'bluecash'      => 'smsService_ChuangLan_BlueCash_OTP',
        'easycash'      => 'smsService_ChuangLan_EasyCash_OTP',
        'happywallet'   => 'smsService_LianDong_HappyWallet_OTP',
        'firstcash'     => 'smsService_ChuangLan_FirstCash_OTP',
        'dailyrupee'    => 'smsService_LianDong_DailyRupee_OTP',
        'rupeefirst'    => 'smsService_ChuangLan_RupeeFirst_OTP',
        'getrupee'      => 'smsService_ChuangLan_GetRupee_OTP',
        'whaleloan'     => 'smsService_LianDong_WhaleLoan_OTP',
        'orangkaya'     => 'smsService_ChuangLan_OrangKaya_OTP',
        'dreamloan'     => 'smsService_ChuangLan_DreamLoan_OTP',
        'chiefloan'     => 'smsService_ChuangLan_ChiefLoan_OTP',
        'pearcash'      => 'smsService_ChuangLan_PearCash_OTP',
    ];

    //OTP验证码-2
    public static $smsConfigListBak = [
        'bigshark'    => '',
        'moneyclick'  => '',
        'lovecash'    => '',
        'rupeefanta'  => '',
        'rupeelaxmi'  => '',
        'dhancash'    => '',
        'rupeecash'   => '',
        'cashcash'    => '',
        'luckywallet' => '',
        'cashalo'     => '',
        'newcash'     => '',
        'hindmoney'   => '',
        'bluecash'    => '',
        'firstcash'   => '',
        'dailyrupee'  => '',
        'rupeefirst'  => '',
        'getrupee'    => '',
        'whaleloan'   => '',
        'orangkaya'   => '',
        'dreamloan'   => '',
        'chiefloan'   => '',
        'pearcash'    => '',
    ];

    public static $smsNotifyConfigList = [
        'bigshark'      => 'smsService_LianDong_GigShark_NOTIFY',
        'moneyclick'    => 'smsService_LianDong_MoneyClick_NOTIFY',
        'lovecash'      => 'smsService_ChuangLan_LoveCash_OTP',
        'rupeefanta'    => 'smsService_LianDong_MoneyClick_NOTIFY',
        'rupeelaxmi'    => 'smsService_LianDong_RupeeLaxmi_NOTIFY',
        'dhancash'      => 'smsService_LianDong_DhanCash_NOTIFY',
        'rupeecash'     => 'smsService_LianDong_RupeeCash_NOTIFY',
        'excellentcash' => 'smsService_ChuangLan_ExcellentCash_OTP',
        'cashcash'      => 'smsService_LianDong_CashCash_NOTIFY',
        'luckywallet'   => 'smsService_LianDong_Luckywallet_NOTIFY',
        'cashalo'       => 'smsService_LianDong_MoneyClick_NOTIFY',
        'newcash'       => 'smsService_ChuangLan_NewCash_OTP',
        'hindmoney'     => 'smsService_LianDong_MoneyClick_NOTIFY',
        'mamaloan'      => 'smsService_LianDong_MamaLoan_NOTIFY',
        'wealthsteward' => 'smsService_Nxtele_WealthSteward_OTP',
        'bluecash'      => 'smsService_ChuangLan_BlueCash_OTP',
        'easycash'      => 'smsService_ChuangLan_EasyCash_OTP',
        'happywallet'   => 'smsService_LianDong_HappyWallet_NOTIFY',
        'cashkash'      => 'smsService_LianDong_Cashkash_NOTIFY',
        'firstcash'     => 'smsService_ChuangLan_FirstCash_OTP',
        'dailyrupee'    => 'smsService_LianDong_DailyRupee_NOTIFY',
        'rupeefirst'    => 'smsService_ChuangLan_RupeeFirst_OTP',
        'getrupee'      => 'smsService_ChuangLan_GetRupee_OTP',
        'whaleloan'     => 'smsService_LianDong_WhaleLoan_NOTIFY',
        'orangkaya'     => 'smsService_ChuangLan_OrangKaya_OTP',
        'dreamloan'     => 'smsService_ChuangLan_DreamLoan_OTP',
        'chiefloan'     => 'smsService_ChuangLan_ChiefLoan_OTP',
        'pearcash'      => 'smsService_ChuangLan_PearCash_OTP',
    ];

    public static $voiceConfigList = [
        'bigshark'      => 'smsService_Nxtelevoice_GigShark',
        'moneyclick'    => 'smsService_Nxtelevoice_MoneyClick',
        'rupeefanta'    => 'smsService_Nxtelevoice_MoneyClick',
        'rupeelaxmi'    => 'smsService_Nxtelevoice_RupeeLaxmi',
        'dhancash'      => 'smsService_Nxtelevoice_DhanCash',
        'rupeecash'     => 'smsService_Nxtelevoice_RupeeCash',
        'hindmoney'     => 'smsService_Nxtelevoice_GigShark',
        'bluecash'      => 'smsService_Nxtelevoice_BlueCash',
        'excellentcash' => 'smsService_Nxtelevoice_ExcellentCash',
        'easycash'      => 'smsService_Nxtelevoice_EasyCash',
        'happywallet'   => 'smsService_Nxtelevoice_HappyWallet',
        'newcash'       => 'smsService_Nxtelevoice_NewCash',
        'firstcash'     => 'smsService_Nxtelevoice_FirstCash',
        'dailyrupee'    => 'smsService_Nxtelevoice_DailyRupee',
        'rupeefirst'    => 'smsService_Nxtelevoice_RupeeFirst',
        'getrupee'      => 'smsService_Nxtelevoice_GetRupee',
        'whaleloan'     => 'smsService_Nxtelevoice_WhaleLoan',
        'orangkaya'     => 'smsService_Nxtelevoice_OrangKaya',
        'dreamloan'     => 'smsService_Nxtelevoice_DreamLoan',
        'chiefloan'     => 'smsService_Nxtelevoice_ChiefLoan',
        'pearcash'      => 'smsService_Nxtelevoice_PearCash',
    ];

    //营销类  提醒、催收
    public static $smsMKTConfigList = [
        'bigshark'      => 'smsService_LianDong_GigShark_MKT',
        'moneyclick'    => 'smsService_LianDong_MoneyClick_MKT',
        'lovecash'      => 'smsService_ChuangLan_LoveCash',
        'rupeefanta'    => 'smsService_LianDong_MoneyClick_MKT',
        'rupeelaxmi'    => 'smsService_LianDong_RupeeLaxmi_MKT',
        'dhancash'      => 'smsService_LianDong_DhanCash_MKT',
        'rupeecash'     => 'smsService_LianDong_RupeeCash_MKT',
        'excellentcash' => 'smsService_ChuangLan_ExcellentCash_MKT',
        'luckywallet'   => 'smsService_LianDong_Luckywallet_MKT',
        'newcash'       => 'smsService_ChuangLan_NewCash_MKT',
        'hindmoney'     => 'smsService_LianDong_GigShark_MKT',
        'mamaloan'      => 'smsService_LianDong_MamaLoan_MKT',
        'wealthsteward' => 'smsService_Nxtele_WealthSteward_MKT',
        'bluecash'      => 'smsService_ChuangLan_BlueCash_MKT',
        'easycash'      => 'smsService_ChuangLan_EasyCash_MKT',
        'happywallet'   => 'smsService_LianDong_HappyWallet_MKT',
        'cashkash'      => 'smsService_LianDong_Cashkash',
        'firstcash'     => 'smsService_ChuangLan_FirstCash_MKT',
        'dailyrupee'    => 'smsService_LianDong_DailyRupee_MKT',
        'rupeefirst'    => 'smsService_ChuangLan_RupeeFirst_MKT',
        'getrupee'      => 'smsService_ChuangLan_GetRupee_MKT',
        'whaleloan'     => 'smsService_LianDong_WhaleLoan_MKT',
        'orangkaya'     => 'smsService_ChuangLan_OrangKaya_MKT',
        'dreamloan'     => 'smsService_ChuangLan_DreamLoan_MKT',
        'chiefloan'     => 'smsService_ChuangLan_ChiefLoan_MKT',
        'pearcash'      => 'smsService_ChuangLan_PearCash_MKT',
    ];
}
