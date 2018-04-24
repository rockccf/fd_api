<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Kuala_Lumpur',
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'mailer' => [
            'enableSwiftMailerLogging' => true,
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            //By default, disable the send real emails function if the environment is development
            //By default, disable the send real emails function if the environment is development
            'useFileTransport' => YII_ENV_DEV ? true : false
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'maxFileSize' => 512000, //512000KB - 500MB
                    'maxLogFiles' => 10,
                    'except' => [
                        'email',
                        'emailRequest',
                        'yii\swiftmailer\*',
                        YII_DEBUG ? '' : 'yii\db\*' //Only log DB profiling if the debug is true
                    ],
                    'logFile' => '@runtime/logs/console/app.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'maxFileSize' => 512000, //512000KB - 500MB
                    'maxLogFiles' => 10,
                    'logFile' => '@runtime/logs/console/error.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['email'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/console/email.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['emailRequest'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/console/email_request.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['yii\swiftmailer\Logger::add'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/console/swiftmailer.log'
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'hostInfo' => 'https://hrcbapi.horecabid.com'
        ]
    ],
    'params' => $params,
    'aliases' => [
        '@globalTemp' => '@runtime/temp',
        '@portalUrl' => $params["GLOBAL"]["PORTAL_URL"],
    ],
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
