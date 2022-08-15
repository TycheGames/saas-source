<?php

$_db_1 = 'rm-uf677fth6or7v6i85.mysql.rds.aliyuncs.com';
$_db_2 = 'rm-uf677fth6or7v6i85.mysql.rds.aliyuncs.com';
$_db_user = 'risk_center';
$_db_pwd = 'Wzd@2019';

$_redis_host = 'r-uf6bzooc92u1a4cpet.redis.rds.aliyuncs.com';
$_redis_pwd = 'Wzd123456';

$_mongo_1 = 'dds-uf6741c0530e23241.mongodb.rds.aliyuncs.com:3717';
$_mongo_2 = 'dds-uf6741c0530e23241.mongodb.rds.aliyuncs.com:3717';
$_mongo_user = 'root';
$_mongo_pwd = 'Wzd123456';

$_es_host = 'es-cn-0pp1gfyua0005q9sg.elasticsearch.aliyuncs.com:9200';
$_es_user = 'elastic';
$_es_pwd = 'wFEXmkuWJHOb7cQ4';

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
            'schemaCacheDuration' => YII_ENV_PROD ? 86400 : 1800, // Duration of schema cache.
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
            'schemaCacheDuration' => YII_ENV_PROD ? 86400 : 1800, // Duration of schema cache.
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
            'database' => 14,
            'password' => $_redis_pwd,
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
            'class' => 'yii\mongodb\Connection',
            'dsn' => "mongodb://{$_mongo_user}:{$_mongo_pwd}@{$_mongo_1}/risk_user_content?authSource=admin",
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
