<?php

$_db_1 = '103.174.50.24';
$_db_2 = '103.174.50.24';
$_db_user = 'root';
$_db_pwd = 'smfin!@2022';

$_redis_host = '103.174.50.22';
$_redis_pwd = 'smfin_redis!@2022';

$_mongo_1 = '103.174.50.22:27017';
$_mongo_2 = 'xxxx:3717';
$_mongo_user = 'root';
$_mongo_pwd = 'smfin_mongodb_2022';



####################### loan项目 #############################################
$_db_loan = '103.174.50.24';
$_db_loan_user = 'root';
$_db_loan_pwd = 'smfin!@2022';

$_mongo_loan = '103.174.50.22:27017';
$_mongo_loan_user = 'root';
$_mongo_loan_pwd = 'smfin_mongodb_2022';

###################### assist_center项目 #####################################
$_redis_host_assist_center = '103.174.50.22';

$_redis_alert_host = '103.174.50.24';
$_redis_alert_pwd = 'smfin_redis!@2022';

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_1};dbname=saas",
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
            'dsn' => "mysql:host={$_db_2};dbname=saas",
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
        'db_stats' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_1};dbname=saas_stats",
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

        'db_stats_read' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_2};dbname=saas_stats",
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
        'db_assist' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_1};dbname=saas_assist",
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
        'db_assist_read' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_2};dbname=saas_assist",
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

####################### loan项目 #############################################
        'db_loan' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_loan};dbname=loan",
            'username' => $_db_loan_user,
            'password' => $_db_loan_pwd,
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
        'db_assist_loan' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$_db_loan};dbname=loan_assist",
            'username' => $_db_loan_user,
            'password' => $_db_loan_pwd,
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
###################################################################################

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_redis_host,
            'password' => $_redis_pwd,
            'port' => 6379,
            'database' => 1,
        ],
        'redis_assist_center' => [
            'class'    => 'yii\redis\Connection',
            'hostname' => $_redis_host_assist_center,
            'password' => $_redis_pwd,
            'port'     => 6379,
            'database' => 2,
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
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',
                'username' => 'wangpeng@vedatlas.com',//发送者邮箱地址
                'password' => 'DMNKmEcBy6g66jUv', //SMTP密码
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['wangpeng@vedatlas.com'=>'system']
            ],
        ],
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => "mongodb://{$_mongo_user}:{$_mongo_pwd}@{$_mongo_1}/saas_user_content?authSource=admin&replicaSet=rs0&readPreference=secondaryPreferred&retryWrites=false",
        ],


####################### loan项目 #############################################
        'mongodb_loan' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => "mongodb://{$_mongo_loan_user}:{$_mongo_loan_pwd}@{$_mongo_loan}/loan_user_content?authSource=admin&replicaSet=rs0&readPreference=secondaryPreferred&retryWrites=false",
        ],
################################################################################
        's3' => [
            'class'         => 'frostealth\yii2\aws\s3\Service',
            'credentials'   => [
                'key'    => 'xxxx',
                'secret' => 'xxxx',
            ],
            'region'        => 'ap-south-1',
            'defaultBucket' => 'xxxx',
            'defaultAcl'    => 'private',
        ],
        'loan_s3' => [
            'class'         => 'frostealth\yii2\aws\s3\Service',
            'credentials'   => [
                'key'    => 'xxxx',
                'secret' => 'xxxx',
            ],
            'region'        => 'ap-south-1',
            'defaultBucket' => 'xxxx',
            'defaultAcl'    => 'private',
        ],
        'dingtalk' => [
            'class' => '\lspbupt\dingtalk\Dingtalk',
            'agentid' => '11111', //您的应用的agentid
            'corpid' => 'xxxxx',  //您的企业corpid
            'corpsecret' => 'xxxxx', //您的企业的corpsecret
        ],
    ],
];
