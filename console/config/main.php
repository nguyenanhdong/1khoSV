<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    // require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php'
    // require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1;dbname=fastjob',//edhcklfhosting_fastjob
            'username' => 'root',//edhcklfhosting_fastjob
            'password' => '', //3wHL4CWV4C_-uI4fmV9e0x
            'charset' => 'utf8',
            'emulatePrepare' => true
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.gmail.com',
                'username' => 'natcompayment@gmail.com',
                'password' => 'npoxhgpfpltiboay',
                'port' => '465',
                'encryption' => 'ssl',
            ],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
