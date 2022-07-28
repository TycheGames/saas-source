<?php
$params['adminEmail'] = 'admin@example.com';
$params['smsService_JinCheng_HY'] = [
    'url' => 'http://13.233.230.135:8016/sms/send',
    'balance_url' => 'http://13.233.230.135:8016/sms/balance',
    'account' => '0710001', //accessId 用户编号
    'password' => 'sL6383',  //secret 用户校验码
    'from' => 'SRupee',  //手机显示发送者，六位英文字符，需报备
    'type' => 'OTP',  //OTP:行业验证码，MKT:营销短信，发送者显示为6位数字
    'sign_name' => '',
    'aisle' => 'JinCheng',
    'aisle_title' => '今骋 - 【SashaktPaisa】行业通知'
];

return $params;