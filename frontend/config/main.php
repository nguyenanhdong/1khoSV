<?php
$db     = require __DIR__ . '/../../common/config/db.php';
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
    require(__DIR__ . '/menu.php')
);

return [
    'id' => 'app-frontend',
    'name'=>'Frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
       
    ],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'site',
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'js'=>[]
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'cookieValidationKey' => 'xcALdDW8XEqJYHQVw5fR4cXTN4QEJV',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'db' => $db,
        'user' => [
            'identityClass' => 'common\models\Users',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,//set this property to false to send mails to real email addresses
            //comment the following array to send mail using php's mail function
            'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => 'mail.abe.edu.vn',
            'username' => 'support@abe.edu.vn',
            'password' => '3M@ail63cf647caa',
            'port' => '465',
            'encryption' => 'ssl',
            'streamOptions' => [
            'ssl' => [
            'allow_self_signed' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
            ],
            ]
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/'                => '/site/index',
                'dang-nhap' =>'/site/login',
                'dang-ky' =>'/site/login',
                'dang-xuat' => '/site/logout',
                // 'danh-muc-khoa-hoc/<slug>' => '/category/index',
                // 'danh-muc-khoa-hoc' => '/category/index',   
                'lien-he' => '/site/contact',
                'san-pham' => '/product/search',
                'tim-kiem' => '/product/search',
                '<slug:(gioi-thieu|huong-dan-mua-hang|thanh-toan|van-chuyen|chinh-sach-doi-tra|chinh-sach-bao-hanh|chinh-sach-bao-mat|dieu-khoan|tuyen-dung)>' => '/site/page',
                // 'chi-tiet-san-pham-<id>' => '/product/detail',




                // 'tin-tuc/<slug>/<id>' => '/news/index',
                // 'tin-tuc/<slug>' => '/news/detail',
                // 'tin-tuc' => '/news/index',
                // 'video/view/<vid>' => '/video/view',

                // 'quen-mat-khau' =>'/site/forgot-password',
                // 'auth' =>'/site/auth',
                // // 'fanpage/<type>' => '/support/perlink',
                // 'fanpage' => '/support/fb',
                // 'instagram' => '/support/insta',
                // 'youtube' => '/support/youtube',
                // 'group' => '/support/group',
                // 'verify-email/<token>'  => '/site/verify-email',
                // 'khoa-hoc-cua-toi' => '/user/mycourse',
                // 'doi-mat-khau' => '/user/change-password',
                // 'khoa-hoc-offline' => '/courseoffline/index',
                // 'khoa-hoc/video-<id>' => '/product/video',
                // 'doi-ngu-giao-vien/'=>'/site/teachers',
                // 'hinh-anh'=>'/site/images',
                // 'lien-he'=>'/support/lienhe',
                // 'he-thong-co-so'=>'/support/listclass',
                // 'chinh-sach-bao-mat'=>'/support/chinhsach',
                // 'qua-tang'=>'/site/gift',
                // 'khoa-hoc/chu-de/<slug>' => '/product/index',
                // 'khoa-hoc' => '/product/index',
                // 'chi-tiet-khoa-hoc/<slug>/<slug_lesson>' => '/product/detail',
                // 'chi-tiet-khoa-hoc/<slug>' => '/product/detail',
                // 'noi-dung-khoa' => '/product/block-content',
                // 'lien-he' => 'site/contact',
                // 'dieu-khoan-su-dung-dich-vu' => 'site/terms',
                // 'chinh-sach-bao-mat' => 'site/privacy-policy',
                // 'chinh-sach-doi-tra-va-hoan-lai-tien' => 'site/refund',
                // 'profile' => 'user/index',
                // 'danh-gia' => 'product/rating',


                // '<alias>' => '/courseoffline/detail',

                
                
                
            ],
        ],
        
    ],
    'as beforeAction' => [
        'class' => 'frontend\components\UAccess'
    ],
    'params' => $params,
];
