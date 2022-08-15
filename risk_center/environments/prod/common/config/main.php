<?php

$_db_1 = 'riskcenter.cmsej672thg7.ap-south-1.rds.amazonaws.com';
$_db_2 = 'riskcenter.cmsej672thg7.ap-south-1.rds.amazonaws.com';
$_db_user = 'admin';
$_db_pwd = '1htyuqtp7e92BN9k';

$_redis_host = 'riskcenter.42uj5s.ng.0001.aps1.cache.amazonaws.com';

$_mongo_1 = 'risk-center.cluster-cmsej672thg7.ap-south-1.docdb.amazonaws.com:27017';
$_mongo_read = 'docdb-2020-08-06-11-57-20.cmsej672thg7.ap-south-1.docdb.amazonaws.com:27017';
$_mongo_user = 'root';
$_mongo_pwd = '2RLDx1Bdy6uk1QoO';

$_es_host = 'vpc-riskcenter-yghd2y4isdpyakqzp6no7c6474.ap-south-1.es.amazonaws.com';

$_redis_alert_host = 'r-a2dmw1z7n1qz5f335bpd.redis.ap-south-1.rds.aliyuncs.com';
$_redis_alert_pwd = 'cZKBWs227uVznQKy';

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
            'port' => 6379,
            'database' => 2,
//            'password' => $_redis_pwd,
        ],

        'redis_alert' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_redis_alert_host,
            'password' => $_redis_alert_pwd,
            'port' => 6379,
            'database' => 1,
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
