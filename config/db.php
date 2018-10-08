<?php

if (YII_ENV_TEST) {
    $dbName = 'fourd_db_test';
} else {
    $dbName = 'fourd_db';
}

$host = 'localhost';
$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.$host.';dbname='.$dbName,
    'charset' => 'utf8',
];

if (YII_ENV_DEV) {
    $db['username'] = 'fourd_user';
    $db['password'] = 'fourd_user123';
} else if (YII_ENV_TEST) {
    $db['username'] = 'fourd_user_test';
    $db['password'] = 'fourd_user123';
} else if (YII_ENV_PROD) {
    $db['username'] = 'fourd_user';
    $db['password'] = 'fourdPa$$123!';
}

return $db;
