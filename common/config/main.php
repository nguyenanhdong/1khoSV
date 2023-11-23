<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'timeZone' => 'Asia/Ho_Chi_Minh',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=fastjob',//edhcklfhosting_fastjob
            'username' => 'root',//edhcklfhosting_fastjob
            'password' => '', //3wHL4CWV4C_-uI4fmV9e0x
            'charset' => 'utf8',
            'emulatePrepare' => true
        ],
        'formatter' => [
            'dateFormat' => 'YYYY-MM-DD HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
       ]
    ],
];
