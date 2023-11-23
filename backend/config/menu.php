<?php
/**
 * Created by Visual code.
 * User: ngochan
 * Date: 12/03/20
 * Time: 22:10 PM
 */

$group_dashboard = [
    [
        'label' => 'Dashboard',
        'icon' => 'fal fa-home',
        'url' => '/'
    ],
];

$group_package = [
    [
        'label' => 'Quản lý gói dịch vụ',
        'icon' => 'fal fa-list-alt',
        'url' => '/package/index'
    ]
];
$group_promotion = [
    [
        'label' => 'Quản lý ưu đãi',
        'icon' => 'fal fa-gift',
        'url' => '/promotion/index'
    ]
];
$group_gift_code = [
    [
        'label' => 'Quản lý mã khuyến mại',
        'icon' => 'fal fa-gift',
        'url' => '/gift-code/index'
    ]
];
$group_banner = [
    [
        'label' => 'Quản lý banner',
        'icon' => 'fal fa-image',
        'url' => '/banner/index'
    ]
];
$group_customer = [
    [
        'label' => 'Quản lý khách hàng',
        'icon' => 'fal fa-user',
        'url' => '/customer/index'
    ]
];
$group_setting = [
    [
        'label' => 'Cấu hình',
        'icon' => 'fal fa-cog',
        'url' => '/config/index'
    ]
];
$group_feedback = [
    [
        'label' => 'Feedback',
        'icon' => 'fal fa-comments',
        'url' => '/feedback/index'
    ]
];
$group_contact = [
    [
        'label' => 'Liên hệ',
        'icon' => 'fal fa-comments',
        'url' => '/contact/index'
    ]
];
$group_product = [
    [
        'label' => 'Dụng cụ bán lẻ',
        'icon' => 'fal fa-cart-plus',
        'url' => '/house-cleaning-tools/index'
    ],
    [
        'label' => 'Sản phẩm dịch vụ',
        'icon' => 'fal fa-cart-plus',
        'url' => '/caundry-product/index'
    ]
];
$group_order = [
    [
        'label' => 'Dọn nhà',
        'icon' => 'fal fa-cart-plus',
        'url' => '/order-work/index?service_id=1'
    ],
    [
        'label' => 'Giặt là',
        'icon' => 'fal fa-cart-plus',
        'url' => '/order-work/index?service_id=2'
    ],
    [
        'label' => 'Shipper',
        'icon' => 'fal fa-cart-plus',
        'url' => '/order-work/index?service_id=3'
    ],
    [
        'label' => 'Phun thuốc muỗi',
        'icon' => 'fal fa-cart-plus',
        'url' => '/order-work/index?service_id=7'
    ],
    
];

$group_notify = [
    [
        'label' => 'Quản lý thông báo',
        'icon' => 'fal fa-bell',
        'url' => '/notify/index'
    ]
];


$group_staff = [
    [
        'label' => 'Quản lý nhân viên',
        'icon' => 'fal fa-user-md',
        'url' => '/staff/index'
    ]
];
$group_news = [
    [
        'label' => 'Quản lý bài viết',
        'icon' => 'fal fa-newspaper',
        'url' => '/news/index'
    ]
];

$group_question = [
    [
        'label' => 'Quản lý câu hỏi',
        'icon' => 'fal fa-question',
        'url' => '/question/index'
    ]
];
$group_calendar     = [
    [
        'label' => 'Lịch làm việc',
        'icon' => 'fal fa-calendar',
        'url' => '/work-order/index'
    ]
];
$menu_group_controller = [
    [
        'label' => 'Dashboard',
        'icon'  => 'fal fa-home',
        'controller' => 'site',
        'child_action' => $group_dashboard
    ],
    [
        'label' => 'Quản lý thông báo',
        'icon'  => 'fal fa-bell',
        'controller' => 'notify',
        'child_action' => $group_notify
    ],
    [
        'label' => 'Quản lý đơn hàng',
        'icon'  => 'fal fa-cart-plus',
        'controller' => 'order-work',
        'child_action' => $group_order
    ],
    [
        'label' => 'Quản lý sản phẩm',
        'icon'  => 'fal fa-cart-plus',
        'controller' => 'house-cleaning-tools',
        'child_action' => $group_product
    ],
    [
        'label' => 'Quản lý banner',
        'controller' => 'banner',
        'child_action' => $group_banner
    ],
    // [
    //     'label' => 'Quản lý gói dịch vụ',
    //     'icon'  => 'fal fa-bus',
    //     'controller' => 'package',
    //     'child_action' => $group_package
    // ],
    [
        'label' => 'Quản lý mã khuyến mại',
        'icon'  => 'fal fa-gift',
        'controller' => 'gift-code',
        'child_action' => $group_gift_code
    ],
    [
        'label' => 'Quản lý ưu đãi',
        'icon'  => 'fal fa-newspaper',
        'controller' => 'promotion',
        'child_action' => $group_promotion
    ],
    // [
    //     'label' => 'Quản lý bài viết',
    //     'icon'  => 'fal fa-newspaper',
    //     'controller' => 'news',
    //     'child_action' => $group_news
    // ],
    [
        'label' => 'Quản lý khách hàng',
        'icon'  => 'fal fa-user',
        'controller' => 'customer',
        'child_action' => $group_customer
    ],
    [
        'label' => 'Quản lý nhân viên',
        'icon'  => 'fal fa-user-secret',
        'controller' => 'staff',
        'child_action' => $group_staff
    ],
    [
        'label' => 'Lịch làm việc',
        'icon'  => 'fal fa-calendar',
        'controller' => 'work-order',
        'child_action' => $group_calendar
    ],
    [
        'label' => 'Quản lý Feedback',
        'icon'  => 'fal fa-comments',
        'controller' => 'feedback',
        'child_action' => $group_feedback
    ],
    [
        'label' => 'Quản lý liên hệ',
        'icon'  => 'fal fa-comments',
        'controller' => 'contact',
        'child_action' => $group_contact
    ],
    [
        'label' => 'Quản lý câu hỏi',
        'icon'  => 'fal fa-question',
        'controller' => 'question',
        'child_action' => $group_question
    ],
    [
        'label' => 'Cấu hình',
        'icon'  => 'fal fa-cog',
        'controller' => 'config',
        'child_action' => $group_setting
    ]
];

return [
    "menu" => $menu_group_controller
];
?>