<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'Asia/Kuala_Lumpur',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Uti-2r55NR00uvGaGPVlZxhv31hwWV0A',
            'enableCookieValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                ],

            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'numberFormatterOptions' => [
                \NumberFormatter::MIN_FRACTION_DIGITS => 3,
                \NumberFormatter::MAX_FRACTION_DIGITS => 3,
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'localhost',
                'port' => '25',
                //'encryption' => 'tls',
            ],
            /*'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'user@gmail.com',
                'password' => '123456',
                'port' => '587',
                'encryption' => 'tls',
                'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],*/
            'enableSwiftMailerLogging' => true,
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            //By default, disable the send real emails function if the environment is development
            'useFileTransport' => YII_ENV_DEV ? true : false
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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
                    ]
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'maxFileSize' => 512000, //512000KB - 500MB
                    'maxLogFiles' => 10,
                    'logFile' => '@runtime/logs/error.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['email'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/email.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['emailRequest'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/email_request.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['yii\swiftmailer\Logger::add'],
                    'logVars' => [],
                    'maxFileSize' => 102400, //102400KB - 100MB
                    'logFile' => '@runtime/logs/swiftmailer.log'
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'mutex' => [
            'class' => 'yii\mutex\MysqlMutex',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'pluralize' => URL_PLURALIZE,
                    'extraPatterns' => [
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'PUT change-password' => 'change-password',
                        'OPTIONS change-password' => 'options',
						'PUT toggle-active' => 'toggle-active',
                        'OPTIONS toggle-active' => 'options',
						'GET is-username-exists-again' => 'is-username-exists-again',
                        'OPTIONS is-username-exists-again' => 'options',
                        'GET is-email-exists-again' => 'is-email-exists-again',
                        'OPTIONS is-email-exists-again' => 'options',
                        'GET is-user-email-exists' => 'is-user-email-exists',
                        'OPTIONS is-user-email-exists' => 'options',
						'GET identity-refresh' => 'identity-refresh',
                        'OPTIONS identity-refresh' => 'options',
                        'POST request-reset-password' => 'request-reset-password',
                        'OPTIONS request-reset-password' => 'options',                        
                        'GET verify-reset-password-token' => 'verify-reset-password-token',
                        'OPTIONS reset-password-token' => 'options',
                        'POST reset-password' => 'reset-password',
                        'OPTIONS reset-password' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'auth-item',
                    'pluralize' => URL_PLURALIZE,
                    'extraPatterns' => [
                        'OPTIONS update' => 'options'
                    ]
                ],
            ],
        ],
    ],
    'params' => $params,
    'aliases' => [
        '@globalTemp' => '@runtime/temp',
        '@portalUrl' => $params["GLOBAL"]["PORTAL_URL"]
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
