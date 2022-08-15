<?php
$params['OSS'] = [
    'OSS_ACCESS_ID'  => 'xxxxxxxxxxxx',
    'OSS_ACCESS_KEY' => 'xxxxxxxxxxxx',
    'OSS_BUCKET'     => 'xxxxxxxxxxxx',
    'OSS_ENDPOINT_LAN'   => 'oss-ap-south-1-internal.aliyuncs.com',
    'OSS_ENDPOINT_WAN'   => 'oss-ap-south-1.aliyuncs.com',
];



$params['WeWork'] = [
    'agent_id'   =>  '1000001',
    'corp_id'   =>  'xxxxxxxxxxxx',
    'secret'   =>  'xxxxxxxxxxxx',
];

$params['KudosCredit'] = [
    'partnerId'   => 'KUD-SHS-00001',
    'authKey'     => 'xxxxxxxxxxxx',
];

$params['KudosExperian'] = [
    'token' => 'xxxxxxxxxxxx',
    'company_code' => 'REP0001'
];

$params['BangaloreExperian'] = [
    'username' => 'xxxxxxxxxxxx',
    'password' => 'xxxxxxxxxxxx',
];

$params['ShanyunExperian'] = [
    'appId' => 'xxxxxxxxxxxx',
    'appSecretKey' => 'xxxxxxxxxxxx',
];

$params['MobiExperian'] = [
    'appId' => 'xxxxxxxxxxxx@repegon.onaliyun.com',
    'appSecretKey' => 'xxxxxxxxxxxx',
];

$params['ExportRisk'] = [
    'loan' => [
        'base_url' => 'http://internal-notify.i-credit.in/',
        'token'    => 'xxxxxxxxxxxx'
    ],
    'saas' => [
        'base_url' => 'http://internal-notify.smallflyelephantsaas.com/',
        'token'    => 'xxxxxxxxxxxx'
    ],
];

$params['googleMap'] = [
    'base_url' => 'https://maps.googleapis.com',
    'geo_url'  => 'maps/api/geocode/json',
    'api_key'  => 'xxxxxxxxxxxx',
];

$params['reCaptcha'] = [
    'secret' => 'xxxxxxxxxxxx1',
    'webSecret' => 'xxxxxxxxxxxx2',
    'uri' => 'https://www.google.com',
    'uriCn' => 'https://www.recaptcha.net'
];

return $params;
