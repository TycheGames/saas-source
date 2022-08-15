<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,

    //创蓝国内
    'smsService_ZhChuangLan_backend_OTP' => [
        'url'         => 'http://smssh1.253.com/msg/send/json', //基类中定义
        'balance_url' => 'http://smssh1.253.com/msg/send/json', //基类中定义
        'account'     => 'xxxx', //基类中定义
        'password'    => 'xxxx', //基类中定义
        'from'        => '【后台管理系统】',  //手机显示发送者，六位英文字符，需报备
        'aisle'       => 'xxxx',
        'aisle_title' => '创蓝国内 - 【后台管理系统】- OTP',
    ],

    'smsService_LianDong_OTP' => [
        'url' => 'https://www.indiahm.com/sms/send',
        'balance_url' => 'http://13.233.230.135:8016/sms/balance',
        'account' => '000001', //accessId 用户编号
        'password' => 'xxxxx',  //secret 用户校验码
        'from' => 'NITPMK',  //手机显示发送者，六位英文字符，需报备 SRupee
        'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
        'sign_name' => '',
        'aisle' => 'iCredit',
        'aisle_title' => '联动 - 【iCredit】OTP'
    ],
];

