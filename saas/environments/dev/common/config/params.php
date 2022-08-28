<?php
$params['OSS'] = [
    'OSS_ACCESS_ID'    => 'xxxx',
    'OSS_ACCESS_KEY'   => 'xxxx',
    'OSS_BUCKET'       => 'xxxx',
    'OSS_ENDPOINT_WAN' => 'oss-cn-shanghai.aliyuncs.com',
    'OSS_ENDPOINT_LAN' => 'oss-cn-shanghai.aliyuncs.com',
];

$params['ali_log'] = [
    'accessKeyId'  => 'xxxx',
    'accessKey'    => 'xxxx',
    'project'      => 'yuntu-saas',
    'logstore'     => 'auth_log',
    'endpoint_lan' => 'ap-south-1-intranet.log.aliyuncs.com',
    'endpoint_wan' => 'ap-south-1.log.aliyuncs.com',
];

$params['ali_slide'] = [
    'access_key'   => 'xxxx',
    'secret_key'    => 'xxxx',
];

$params['YuanDing'] = [
    'common' => [
        'url'         => 'https://in.creditech.biz/rt/verify_bank/',
        'user'        => 'xxxx',
        'password'    => 'xxxx',
    ],
    //NewCash
    '13'     => [
        'url'      => 'https://in.creditech.biz/rt/verify_bank/',
        'user'     => 'xxxx',
        'password' => 'xxxx',
    ],
    //EasyCash
    '18'     => [
        'url'      => 'https://in.creditech.biz/rt/verify_bank/',
        'user'     => 'xxxx',
        'password' => 'xxxx',
    ],
];

$params['AadhaarApi'] = [
    'common' => [
        'base_url' => 'https://kyc-api.aadhaarapi.io',
        'token'    => 'xxxx',
        'account'  => 'xxxx',
    ],
    //rupee cash
    '8'      => [
        'base_url' => 'https://kyc-api.aadhaarapi.io',
        'token'    => 'xxxx',
        'account'  => 'xxxx',
    ],
    //NewCash
    '13'      => [
        'base_url' => 'https://kyc-api.aadhaarapi.io',
        'token'    => 'xxxx',
        'account'  => 'xxxx',
    ],
    //EasyCash
    '18'      => [
        'base_url' => 'https://kyc-api.aadhaarapi.io',
        'token'    => 'xxxx',
        'account'  => 'xxxx',
    ],
];

$params['Accuauth'] = [
    'common' => [
        'base_url'   => 'https://cloudapi.accuauth.com/',
        'api_id'     => 'xxxx',
        'api_secret' => 'xxxx',
    ],
    //rupee cash
    '8'      => [
        'base_url'   => 'https://cloudapi.accuauth.com/',
        'api_id'     => 'xxxx',
        'api_secret' => 'xxxx',
    ],
    //NewCash
    '13'      => [
        'base_url'   => 'https://cloudapi.accuauth.com/',
        'api_id'     => 'xxxx',
        'api_secret' => 'xxxx',
    ],
    //EasyCash
    '18'      => [
        'base_url'   => 'https://cloudapi.accuauth.com/',
        'api_id'     => 'xxxx',
        'api_secret' => 'xxxx',
    ],
];

$params['WeWork'] = [
    'agent_id'   =>  'xxxx',
    'corp_id'   =>  'xxxx',
    'secret'   =>  'xxxx',
];

$params['loan'] = [
    'base_url' => 'http://test-api.i-credit.in:8081/',
    'token'    => 'xxxx',
];

$params['RiskCenter'] = [
    'base_url' => 'http://103.174.50.22:8092/',
];

$params['reCaptcha'] = [
    'secret' => '6LeW6lghAAAAAAztmS4wC4d_T2IpJPNdwD3mSVT7',
    'webSecret' => '6LeW6lghAAAAAJv9LKrsQmnn3I9H_VJM4MR6IPpG',
    'uri' => 'https://www.recaptcha.net',
    'uriCn' => 'https://www.recaptcha.net'
];

$params['AssistCenter'] = [
    'moneyclick' => [
        'base_url' => '',
        'token'    => ''
    ],
];

return $params;