<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,

    'guestPayment' => [
        'salt' => 'gt',
        'minHashLength' => 5
    ],


    'bankMaintenanceList' => [
//        'sbi'
    ],

    'link' => [
        'i' => 'icredit',
        'r' => 'rupeeplus',
        'n' => 'needrupee',
        'c' => 'cashcow',
        't' => 'topcash',
        'a' => 'cashadda',
        'b' => 'cashbowl',
        'd' => 'ikarza',

    ],

    'smsService_Nxtele_GigShark' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'bigshark',
        'aisle_title' => '牛信 - 【bigshark】- 营销',
    ],

    'smsService_Nxtele_GigShark_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'bigshark',
        'aisle_title' => '牛信 - 【bigshark】- OTP',
    ],

    'smsService_Nxtele_GigShark_NOTICE' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'bigshark',
        'aisle_title' => '牛信 - 【bigshark】- 通知',
    ],

    'smsService_Nxtelevoice_GigShark' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'bigshark',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【bigshark】- 语音',
    ],

    'smsService_Nxtele_MoneyClick' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'mclick',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'moneyclick',
        'aisle_title' => '牛信 - 【moneyclick】- 营销',
    ],

    'smsService_Nxtele_MoneyClick_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'mclick',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'moneyclick',
        'aisle_title' => '牛信 - 【moneyclick】- OTP',
    ],


    'smsService_Nxtele_MoneyClick_NOTICE' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'mclick',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'moneyclick',
        'aisle_title' => '牛信 - 【moneyclick】- 通知',
    ],

    'smsService_Nxtelevoice_MoneyClick' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'mclick',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'moneyclick',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【moneyclick】- 语音',
    ],

    //START--牛信语音群呼（需要语音文件url）
    'smsService_NxVoiceGroup_MoneyClick' => [
        'url'         => 'http://api.nxcloud.com/api/voiceSms/gpsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'aisle'       => 'moneyclick',
        'show_phone'   => '01204037520', //默认显示号码
        'country_code' => '91',
        'aisle_title' => '牛信 - 【MoneyClick】- 语音群呼(需要语音文件,定时任务不要勾选)',
    ],
    //END--牛信语音群呼（需要语音文件url）

    'smsService_TianChang_OTP' => [
        'url' => 'http://101.227.68.68:7891/mt',
        'collurl' => 'http://101.227.68.68:7891/mo',
        'balance_url' => 'http://101.227.68.68:7891/bi',
        'account' => 'xxxx',
        'password' => 'xxxx',
        'wed_password' => 'xxxx',//管理后台登录密码 admin
        'expid' => 'xxxx',
        'sign_name' => '【SashaktRupee】',
        'aisle_type' => 1, // 1:通知类，2:营销类，3:还款类，4:催收类，5:确认停用
        'aisle' => 'TianChang',
        'aisle_title' => '天畅 - 811934 -通知',
    ],

    'smsService_LianDong_MoneyClick_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxxx',  //secret 用户校验码
        'from' => 'VgaMax',  //手机显示发送者，六位英文字符，需报备
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'moneyclick',
        'aisle_title' => '联动 - 【moneyclick】通知'
    ],

    'smsService_LianDong_MoneyClick_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'VgaMax',  //手机显示发送者，六位英文字符，需报备
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'moneyclick',
        'aisle_title' => '联动 - 【moneyclick】营销'
    ],

    'smsService_LianDong_MoneyClick_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'VgaMax',  //手机显示发送者，六位英文字符，需报备
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'moneyclick',
        'aisle_title' => '联动 - 【moneyclick】OTP'
    ],

    'smsService_LianDong_MoneyClick' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'VgaMax',  //手机显示发送者，六位英文字符，需报备
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'moneyclick',
        'aisle_title' => '联动 - 【moneyclick】营销'
    ],

    'smsService_LianDong_GigShark_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'bigshark',
        'aisle_title' => '联动 - 【bigshark】通知'
    ],

    'smsService_LianDong_GigShark_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'bigshark',
        'aisle_title' => '联动 - 【bigshark】营销'
    ],

    'smsService_LianDong_GigShark_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'bigshark',
        'aisle_title' => '联动 - 【bigshark】OTP'
    ],

    'smsService_LianDong_OTP_SASHAKTRUPEE' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'XPROSP',  //手机显示发送者，六位英文字符，需报备
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'SashaktRupee',
        'aisle_title' => '联动 - 【SashaktRupee】OTP'
    ],

    'smsService_LianDong_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'XPROSP',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'iCredit',
        'aisle_title' => '联动 - 【iCredit】OTP'
    ],

    //创蓝
    'smsService_ChuangLan_LoveCash' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '',
        'aisle'       => 'LoveCash',
        'aisle_title' => '创蓝 - 【LoveCash】- 营销',
    ],

    'smsService_ChuangLan_LoveCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '',
        'aisle'       => 'LoveCash',
        'aisle_title' => '创蓝 - 【LoveCash】- 通知&OTP',
    ],

    //创蓝国内
    'smsService_ZhChuangLan_backend_OTP' => [
        'url'         => 'http://smssh1.253.com/msg/send/json', //基类中定义
        'balance_url' => 'http://smssh1.253.com/msg/send/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '【后台管理系统】',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'SashaktRupee',
        'aisle_title' => '创蓝国内 - 【后台管理系统】- OTP',
    ],
    'smsService_India_iCredit_OTP' => [
        'url'         => 'http://cloud.smsindiahub.in/vendorsms/pushsms.aspx', //基类中定义
        'balance_url' => 'http://cloud.smsindiahub.in/vendorsms/CheckBalance.aspx', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'ICREDT',  //手机显示发送者，六位英文字符，需报备
        'type'        => 2, //通知类短信
        'aisle'       => 'iCredit',
        'aisle_title' => '印度 - 【iCredit】- 通知',
    ],

    'smsService_Karix_iCredit_OTP' => [
        'url'         => 'https://japi.instaalerts.zone/httpapi/QueryStringReceiver', //基类中定义
        'balance_url' => '',
        'account'     => '',
        'password'    => 'xxxx',
        'from'        => 'ICREDT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'iCredit',
        'aisle_title' => 'Karix - 【iCredit】- 通知',
    ],


    'smsService_Nxtele_RupeeLaxmi' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeelaxmi',
        'aisle_title' => '牛信 - 【rupeelaxmi】- 营销',
    ],

    'smsService_Nxtele_RupeeLaxmi_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeelaxmi',
        'aisle_title' => '牛信 - 【rupeelaxmi】- OTP',
    ],

    'smsService_Nxtele_RupeeLaxmi_NOTICE' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeelaxmi',
        'aisle_title' => '牛信 - 【rupeelaxmi】- 通知',
    ],

    'smsService_Nxtelevoice_RupeeLaxmi' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeelaxmi',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【rupeelaxmi】- 语音',
    ],

    'smsService_LianDong_RupeeLaxmi_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeelaxmi',
        'aisle_title' => '联动 - 【rupeelaxmi】通知'
    ],

    'smsService_LianDong_RupeeLaxmi_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeelaxmi',
        'aisle_title' => '联动 - 【rupeelaxmi】营销'
    ],

    'smsService_LianDong_RupeeLaxmi_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeelaxmi',
        'aisle_title' => '联动 - 【rupeelaxmi】OTP'
    ],


    'smsService_Nxtele_DhanCash' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'dhancash',
        'aisle_title' => '牛信 - 【dhancash】- 营销',
    ],

    'smsService_Nxtele_DhanCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'dhancash',
        'aisle_title' => '牛信 - 【dhancash】- OTP',
    ],

    'smsService_Nxtele_DhanCash_NOTICE' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'dhancash',
        'aisle_title' => '牛信 - 【dhancash】- 通知',
    ],

    'smsService_Nxtelevoice_DhanCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'bShark',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'dhancash',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【dhancash】- 语音',
    ],

    'smsService_LianDong_DhanCash_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'dhancash',
        'aisle_title' => '联动 - 【dhancash】通知'
    ],

    'smsService_LianDong_DhanCash_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'dhancash',
        'aisle_title' => '联动 - 【dhancash】营销'
    ],

    'smsService_LianDong_DhanCash_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMS',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'dhancash',
        'aisle_title' => '联动 - 【dhancash】OTP'
    ],

    'smsService_LianDong_RupeeCash_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeecash',
        'aisle_title' => '联动 - 【rupeecash】通知'
    ],

    'smsService_LianDong_RupeeCash_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeecash',
        'aisle_title' => '联动 - 【rupeecash】营销'
    ],

    'smsService_LianDong_RupeeCash_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'rupeecash',
        'aisle_title' => '联动 - 【rupeecash】OTP'
    ],


    'smsService_Nxtele_RupeeCash' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeecash',
        'aisle_title' => '牛信 - 【rupeecash】- 营销',
    ],

    'smsService_Nxtele_RupeeCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeecash',
        'aisle_title' => '牛信 - 【rupeecash】- OTP',
    ],

    'smsService_Nxtelevoice_RupeeCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'rupeecash',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【rupeecash】- 语音',
    ],

    'smsService_Nxtele_CashCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'ICREDT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'iCredit',
        'aisle_title' => '牛信 - 【CashCash】- OTP',
    ],

    'smsService_Nxtele_CashCash_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'ICREDT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'iCredit',
        'aisle_title' => '牛信 - 【CashCash】- 通知',
    ],

    'smsService_Nxtele_CashCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'ICREDT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'iCredit',
        'aisle_title' => '牛信 - 【CashCash】- 营销',
    ],

    'smsService_LianDong_CashCash_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPPL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'iCredit',
        'aisle_title' => '联动 - 【CashCash】OTP'
    ],

    'smsService_LianDong_CashCash_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPPL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'iCredit',
        'aisle_title' => '联动 - 【CashCash】通知'
    ],

    'smsService_LianDong_CashCash_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPPL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'iCredit',
        'aisle_title' => '联动 - 【CashCash】- 营销'
    ],

    'smsService_Nxtele_ExcellentCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【excellentcash】- OTP',
    ],

    'smsService_Nxtele_EasyCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【easycash】- OTP',
    ],

    'smsService_Nxtele_ExcellentCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【excellentcash】- 营销',
    ],

    'smsService_Nxtele_EasyCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【easycash】- 营销',
    ],

    'smsService_Nxtelevoice_ExcellentCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'excellentcash',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【excellentcash】- 语音',
    ],

    'smsService_Nxtelevoice_EasyCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'easycash',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【easycash】- 语音',
    ],

    'smsService_ChuangLan_ExcellentCash' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'ExcellentCash',
        'aisle_title' => '创蓝 - 【ExcellentCash】- 营销',
    ],

    'smsService_ChuangLan_EasyCash' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'EasyCash',
        'aisle_title' => '创蓝 - 【EasyCash】- 营销',
    ],

    'smsService_ChuangLan_ExcellentCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'ExcellentCash',
        'aisle_title' => '创蓝 - 【ExcellentCash】- 通知&OTP',
    ],

    'smsService_ChuangLan_ExcellentCash_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'ExcellentCash',
        'aisle_title' => '创蓝 - 【ExcellentCash】- 营销',
    ],

    'smsService_ChuangLan_EasyCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'EasyCash',
        'aisle_title' => '创蓝 - 【EasyCash】- 通知&OTP',
    ],

    'smsService_ChuangLan_EasyCash_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'EasyCash',
        'aisle_title' => '创蓝 - 【EasyCash】- 营销',
    ],

    'smsService_Scoreone_Urupee_OTP' => [
        'url'         => 'http://149.129.129.116:8090/ap-web/sendSms.do', //基类中定义
        'balance_url' => 'http://149.129.129.116:8090/ap-web/sendSms.do', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 1, //基类中定义
        'from'        => '',
        'aisle'       => 'Urupee',
        'aisle_title' => '创蓝 - 【Urupee】- 通知',
    ],

    'smsService_Nxtele_Luckywallet_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【Luckywallet】- OTP',
    ],

    'smsService_Nxtele_Luckywallet_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【Luckywallet】- 营销',
    ],

    'smsService_Nxtelevoice_Luckywallet' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'nxtele',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'Luckywallet',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【Luckywallet】- 语音',
    ],

    'smsService_LianDong_Luckywallet_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Luckywallet',
        'aisle_title' => '联动 - 【Luckywallet】OTP'
    ],

    'smsService_LianDong_Luckywallet_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Luckywallet',
        'aisle_title' => '联动 - 【Luckywallet】通知'
    ],

    'smsService_LianDong_Luckywallet_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Luckywallet',
        'aisle_title' => '联动 - 【Luckywallet】- 营销'
    ],

    'smsService_Nxtele_NewCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【NewCash】- OTP',
    ],

    'smsService_Nxtele_NewCash_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【NewCash】- 通知',
    ],

    'smsService_Nxtele_NewCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【NewCash】- 营销',
    ],

    'smsService_Nxtelevoice_NewCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'Luckywallet',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【NewCash】- 语音',
    ],


    'smsService_ChuangLan_NewCash' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'NewCash',
        'aisle_title' => '创蓝 - 【NewCash】- 营销',
    ],

    'smsService_ChuangLan_NewCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'NewCash',
        'aisle_title' => '创蓝 - 【NewCash】- 通知&OTP',
    ],

    'smsService_ChuangLan_NewCash_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'NewCash',
        'aisle_title' => '创蓝 - 【NewCash】- 营销',
    ],


    'smsService_LianDong_MamaLoan_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMP',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'MamaLoan',
        'aisle_title' => '联动 - 【MamaLoan】OTP'
    ],

    'smsService_LianDong_MamaLoan_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMP',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'MamaLoan',
        'aisle_title' => '联动 - 【MamaLoan】通知'
    ],

    'smsService_LianDong_MamaLoan_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NITPMP',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'MamaLoan',
        'aisle_title' => '联动 - 【MamaLoan】- 营销'
    ],

    'smsService_Nxtele_WealthSteward_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【WealthSteward】- OTP',
    ],

    'smsService_Nxtele_WealthSteward_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【WealthSteward】- 营销',
    ],

    'smsService_Nxtelevoice_WealthSteward' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【WealthSteward】- 语音',
    ],

    'smsService_Nxtele_BlueCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【BlueCash】- OTP',
    ],

    'smsService_Nxtele_BlueCash_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【BlueCash】- 通知',
    ],

    'smsService_Nxtele_BlueCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【BlueCash】- 营销',
    ],

    'smsService_Nxtelevoice_BlueCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【BlueCash】- 语音',
    ],

    'smsService_ChuangLan_BlueCash' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'BlueCash',
        'aisle_title' => '创蓝 - 【BlueCash】- 营销',
    ],

    'smsService_ChuangLan_BlueCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'BlueCash',
        'aisle_title' => '创蓝 - 【BlueCash】- 通知&OTP',
    ],

    'smsService_ChuangLan_BlueCash_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'BlueCash',
        'aisle_title' => '创蓝 - 【BlueCash】- 营销',
    ],

    'smsService_Nxtele_HappyWallet_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'SMSALT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'HappyWallet',
        'aisle_title' => '牛信 - 【HappyWallet】- OTP',
    ],

    'smsService_Nxtele_HappyWallet_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'SMSALT',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'HappyWallet',
        'aisle_title' => '牛信 - 【HappyWallet】- 通知',
    ],

    'smsService_Nxtele_HappyWallet_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '863767',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'HappyWallet',
        'aisle_title' => '牛信 - 【HappyWallet】- 营销',
    ],

    'smsService_Nxtelevoice_HappyWallet' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'HappyWallet',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【HappyWallet】- 语音',
    ],


    //Cashkash && LianDong
    'smsService_LianDong_Cashkash' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Cashkash',
        'aisle_title' => '联动 - 【Cashkash】营销'
    ],

    'smsService_LianDong_Cashkash_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Cashkash',
        'aisle_title' => '联动 - 【Cashkash】OTP'
    ],

    'smsService_LianDong_Cashkash_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'Cashkash',
        'aisle_title' => '联动 - 【Cashkash】通知'
    ],


    //Cashkash && 牛信
    'smsService_Nxtele_Cashkash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '',
        'aisle'       => 'Cashka',
        'aisle_title' => '牛信 - 【Cashkash】- 营销',
    ],

    'smsService_Nxtele_Cashkash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '',
        'aisle'       => 'Cashkash',
        'aisle_title' => '牛信 - 【Cashkash】- OTP',
    ],

    'smsService_LianDong_HappyWallet_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'HappyWallet',
        'aisle_title' => '联动 - 【HappyWallet】营销'
    ],

    'smsService_LianDong_HappyWallet_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'HappyWallet',
        'aisle_title' => '联动 - 【HappyWallet】OTP'
    ],

    'smsService_LianDong_HappyWallet_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'HappyWallet',
        'aisle_title' => '联动 - 【HappyWallet】通知'
    ],

    'smsService_ChuangLan_FirstCash_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'FirstCash',
        'aisle_title' => '创蓝 - 【FirstCash】- 通知&OTP',
    ],

    'smsService_ChuangLan_FirstCash_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'FirstCash',
        'aisle_title' => '创蓝 - 【FirstCash】- 营销',
    ],

    'smsService_Nxtele_FirstCash_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【FirstCash】- OTP',
    ],

    'smsService_Nxtele_FirstCash_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【FirstCash】- 通知',
    ],

    'smsService_Nxtele_FirstCash_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'ZHL9Ixq7', //基类中定义
        'password'    => 'RyELFFfU', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'BSPRIN',
        'aisle_title' => '牛信 - 【FirstCash】- 营销',
    ],

    'smsService_Nxtelevoice_FirstCash' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'FirstCash',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【FirstCash】- 语音',
    ],

    'smsService_Nxtele_DailyRupee_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'DYRUPE',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'DailyRupee',
        'aisle_title' => '牛信 - 【DailyRupee】- OTP',
    ],

    'smsService_Nxtele_DailyRupee_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'DYRUPE',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'DailyRupee',
        'aisle_title' => '牛信 - 【DailyRupee】- 通知',
    ],

    'smsService_Nxtele_DailyRupee_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'DYRUPE',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'DailyRupee',
        'aisle_title' => '牛信 - 【DailyRupee】- 营销',
    ],

    'smsService_Nxtelevoice_DailyRupee' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'DYRUPE',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'DailyRupee',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【DailyRupee】- 语音',
    ],

    'smsService_LianDong_DailyRupee_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'DailyRupee',
        'aisle_title' => '联动 - 【DailyRupee】营销'
    ],

    'smsService_LianDong_DailyRupee_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'DailyRupee',
        'aisle_title' => '联动 - 【DailyRupee】OTP'
    ],

    'smsService_LianDong_DailyRupee_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'DailyRupee',
        'aisle_title' => '联动 - 【DailyRupee】通知'
    ],

    'smsService_ChuangLan_RupeeFirst_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'RupeeFirst',
        'aisle_title' => '创蓝 - 【RupeeFirst】- 通知&OTP',
    ],

    'smsService_ChuangLan_RupeeFirst_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'RupeeFirst',
        'aisle_title' => '创蓝 - 【RupeeFirst】- 营销',
    ],

    'smsService_Nxtele_RupeeFirst_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'RupeeFirst',
        'aisle_title' => '牛信 - 【RupeeFirst】- OTP',
    ],

    'smsService_Nxtele_RupeeFirst_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'RupeeFirst',
        'aisle_title' => '牛信 - 【RupeeFirst】- 通知',
    ],

    'smsService_Nxtele_RupeeFirst_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'RupeeFirst',
        'aisle_title' => '牛信 - 【RupeeFirst】- 营销',
    ],

    'smsService_Nxtelevoice_RupeeFirst' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'RupeeFirst',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【RupeeFirst】- 语音',
    ],

    'smsService_ChuangLan_GetRupee_OTP' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'GetRupee',
        'aisle_title' => '创蓝 - 【GetRupee】- 通知&OTP',
    ],

    'smsService_ChuangLan_GetRupee_MKT' => [
        'url'         => 'http://intapi.253.com/send', //基类中定义
        'balance_url' => 'http://intapi.253.com/balance/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',
        'aisle'       => 'GetRupee',
        'aisle_title' => '创蓝 - 【GetRupee】- 营销',
    ],

    'smsService_Nxtele_GetRupee_OTP' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'GetRupee',
        'aisle_title' => '牛信 - 【GetRupee】- OTP',
    ],

    'smsService_Nxtele_GetRupee_NOTIFY' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'GetRupee',
        'aisle_title' => '牛信 - 【GetRupee】- 通知',
    ],

    'smsService_Nxtele_GetRupee_MKT' => [
        'url'         => 'https://api.nxcloud.com/api/sms/mtsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'GetRupee',
        'aisle_title' => '牛信 - 【GetRupee】- 营销',
    ],

    'smsService_Nxtelevoice_GetRupee' => [
        'url'         => 'https://api.nxcloud.com/api/voiceSms/notsend', //基类中定义
        'balance_url' => 'https://api.nxcloud.com/api/common/getBalance', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => 'BSPRIN',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'GetRupee',
        'lang'         => 'en',
        'show_phone'   => '919953020821',
        'country_code' => '91',
        'aisle_title' => '牛信 - 【GetRupee】- 语音',
    ],

    'smsService_LianDong_WhaleLoan_NOTIFY' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备
        'type' => 'NOTIFY',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'WhaleLoan',
        'aisle_title' => '联动 - 【WhaleLoan】通知'
    ],

    'smsService_LianDong_WhaleLoan_MKT' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => 'xxxx', //accessId 用户编号
        'password' => 'xxxx',  //secret 用户校验码
        'from' => 'NMSPRL',  //手机显示发送者，六位英文字符，需报备
        'type' => 'MKT',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'WhaleLoan',
        'aisle_title' => '联动 - 【WhaleLoan】营销'
    ],

];

