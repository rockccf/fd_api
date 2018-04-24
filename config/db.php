<?php

$host = 'localhost';
$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.$host.';dbname=fourd_db',
    'charset' => 'utf8',
];

if (YII_ENV_DEV) {
    $db['username'] = 'fourd_user';
    $db['password'] = 'fourd_user123';
} else if (YII_ENV_TEST) {
    $db['username'] = 'fourd_user';
    $db['password'] = 'fourd_user123';
} else if (YII_ENV_PROD) {
    $db['username'] = 'fourd_user';
    $db['password'] = 'fourdPa$$123!';
}

return $db;
