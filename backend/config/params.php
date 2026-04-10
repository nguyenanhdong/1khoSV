<?php
return [
    'adminEmail' => 'admin@example.com',
    'urlDomain' => 'https://api.1kho.com.vn',
    'secretKeyJWT' => 'Qd7F9!qIY2@#k441JFN4^&svxHGEYBLP@_f5yUbl',
    'fireBase'   => [
        'customer' => [
            'projectId' => 'khocustomer-ac2b2',
            'webAPIKey' => 'AIzaSyCJvghh5Shh3x-xXToyRla6-xC1MxshtV0'
        ]
    ],
    'socialKey'  => [
        'facebook' => [
            'clientId' => '337902925800694',
            'clientSecret' => 'ea9d2533cc1321dff69ad23e0653959a'
        ]
    ],
    'banner_type' => [
        1 => 'Banner app khách hàng',
        2 => 'Banner app agent',
        3 => 'Banner tin giao vặt',
    ],
    'status' => [
        0   => 'Ẩn',
        1   => 'Hiển thị',
    ],
    'product_origin' => [
        1   => 'Mới',
        2   => 'Đã sử dụng',
    ],
    'user_notify' => [ // Đối tượng nhận thông báo
        1 => 'Khách hàng',
        2 => 'Đại lý',
    ],
    'type_voucher' => [ // loai voucher
        1 => 'Giảm tiền',
        2 => 'Hoàn xu',
    ],
    'type_price' => [ // loai voucher
        1 => 'Giảm theo số tiền',
        2 => 'Giảm theo %',
    ],
    'status_order' => [
        0 => 'Đang chờ xử lý',
        1 => 'Đã xác nhận',
        2 => 'Đang giao hàng',
        3 => 'Đã mua hàng',
        4 => 'Hoàn tiền',
        5 => 'Đã hủy',
    ],
    'type_payment' => [
        1 => 'Chuyển khoản',
        2 => 'Thanh toán khi nhận hàng',
    ],
];
