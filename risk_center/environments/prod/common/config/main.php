<?php

$_db_1 = '103.174.50.24';
$_db_2 = '103.174.50.24';
$_db_user = 'root';
$_db_pwd = 'smfin!@2022';

$_redis_host = '103.174.50.22';
$_redis_pwd = 'smfin_redis!@2022';

$_mongo_1 = '103.174.50.24:27017';
$_mongo_read = 'docdb-2020-08-06-11-57-20.cmsej672thg7.ap-south-1.docdb.amazonaws.com:27017';
$_mongo_user = 'root';
$_mongo_pwd = 'smfin_mongodb_2022';

$_es_host = '103.174.50.22:9200';
$_es_user = 'elastic';
$_es_pwd = 'smfin_elastic!@2022';

$_redis_alert_host = '103.174.50.22';
$_redis_alert_pwd = 'smfin_redis!@2022';

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_1};dbname=risk_center",
            'username' => $_db_user,
            'password' => $_db_pwd,
            'charset' => 'utf8',
            'tablePrefix' => 'tb_',
            'enableSchemaCache' => !YII_DEBUG,
            'schemaCacheDuration' => 10800, // Duration of schema cache.
            'schemaCache' => 'cache', // Name of the cache component used to store schema information
            'attributes' => [
                PDO::ATTR_TIMEOUT => 10, // rds 的 connection_timeout 设置为10
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
        'db_read_1' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_2};dbname=risk_center",
            'username' => $_db_user,
            'password' => $_db_pwd,
            'charset' => 'utf8',
            'tablePrefix' => 'tb_',
            'enableSchemaCache' => !YII_DEBUG,
            'schemaCacheDuration' => 10800, // Duration of schema cache.
            'schemaCache' => 'cache', // Name of the cache component used to store schema information
            'attributes' => [
                PDO::ATTR_TIMEOUT => 10, // rds 的 connection_timeout 设置为10
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_redis_host,
            'password' => $_redis_pwd,
            'port' => 6379,
            'database' => 14,
        ],

        'redis_alert' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_redis_alert_host,
            'password' => $_redis_alert_pwd,
            'port' => 6379,
            'database' => 14,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => "mongodb://{$_mongo_user}:{$_mongo_pwd}@{$_mongo_1}/risk_user_content?authSource=admin&replicaSet=rs0&readPreference=secondaryPreferred&retryWrites=false",
        ],
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'autodetectCluster' => false,
            'nodes' => [
                [
                    'http_address' => $_es_host,
                    'auth'         => ['username' => $_es_user, 'password' => $_es_pwd],
                ],
            ],
        ],
        'dingtalk' => [
            'class' => '\lspbupt\dingtalk\Dingtalk',
            'agentid' => '858146714', //您的应用的agentid
            'corpid' => 'dingojsy6ddwpbdk59gl',  //您的企业corpid
            'corpsecret' => 'piL5AX7LYtJutsFRjPEEse6QwbHwVndl8wTWe3EbZ4H8WHITL_qB_YYAA2zqSUje', //您的企业的corpsecret
        ],
    ],
];
