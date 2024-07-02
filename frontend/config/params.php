<?php
return [
    'adminEmail' => 'admin@example.com',
    'openssl_encrypt_method' => 'aes-256-ecb',
    'hotline' => '0999999999',
    'email'   => 'favico@gmail.com',
    'address' => ' Hà Nội',
    'domain' =>'https://phavico.com/',
    'og_title' => ['property' => 'og:title', 'content' => 'phaavico.com'],
    'og_description' => ['property' => 'og:description', 'content' => 'phaavico.com/'],
    'og_image' => ['property' => 'og:image', 'content' => '/images/icon/logo-fa.svg'],
    'og_fb' => ['property' => 'article:publisher', 'content' => 'https://www.facebook.com'],

    'adminEmail' => 'admin@example.com',
    'urlDomain' => 'https://api.1kho.com.vn',
    'secretKeyJWT' => 'Qd7F9!qIY2@#k441JFN4^&svxHGEYBLP@_f5yUbl',
    // 'fireBase'   => [
    //     'customer' => [
    //         'projectId' => 'khocustomer-ac2b2',
    //         'webAPIKey' => 'AIzaSyCJvghh5Shh3x-xXToyRla6-xC1MxshtV0'
    //     ]
    // ],
    'socialKey'  => [
        'facebook' => [
            'clientId' => '337902925800694',
            'clientSecret' => 'ea9d2533cc1321dff69ad23e0653959a'
        ]
    ],
    'fireBase' => [//Cấu hình firebase đăng nhập -> Dùng product cần đổi value các key
        'login' => [
            "apiKey" => "AIzaSyC7QCc1QHH6giib7hM3NK-LOzMpYsvCfrU",
            "authDomain" => "testlogin-df35b.firebaseapp.com",
            "databaseURL" => "testlogin-df35b.firebaseio.com",
            "projectId" => "testlogin-df35b",
            "storageBucket" => "testlogin-df35b.appspot.com",
            "messagingSenderId" => "856383394380"
        ]
    ]
];
