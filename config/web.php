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
                        'verify_per' => false,
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
						'GET is-username-exists-again' => 'is-username-exists-again',
                        'OPTIONS is-username-exists-again' => 'options',
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
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'master',
                    'pluralize' => URL_PLURALIZE,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'company',
                    'pluralize' => URL_PLURALIZE,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'company-draw',
                    'pluralize' => URL_PLURALIZE,
                    'extraPatterns' => [
                        'DELETE bulk-delete' => 'bulk-delete',
                        'OPTIONS bulk-delete' => 'options'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'package',
                    'pluralize' => URL_PLURALIZE,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'bet',
                    'pluralize' => URL_PLURALIZE,
                    'extraPatterns' => [
                        'GET get-bet-slip-history' => 'get-bet-slip-history',
                        'OPTIONS get-bet-slip-history' => 'options',
                        'GET get-bet-number-history' => 'get-bet-number-history',
                        'OPTIONS get-bet-number-history' => 'options',
                        'GET get-void-bet-history' => 'get-void-bet-history',
                        'OPTIONS get-void-bet-history' => 'options',
                        'GET get-voidable-bets' => 'get-voidable-bets',
                        'OPTIONS get-voidable-bets' => 'options',
                        'GET get-voided-bets' => 'get-voided-bets',
                        'OPTIONS get-voided-bets' => 'options'
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'report',
                    'pluralize' => URL_PLURALIZE,
                    'extraPatterns' => [
                        'GET get-report' => 'get-report',
                        'OPTIONS get-report' => 'options'
                    ]
                ]
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
