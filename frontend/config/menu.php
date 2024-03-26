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
        'icon' => 'fal fa-chart-pie',
        'url' => '/'
    ],
];

$group_course = [
    [
        'label' => 'Danh sách khoá học',
        'icon' => 'fal fa-list-alt',
        'url' => '/course/index'
    ],
    [
        'label' => 'Chuyên mục khoá học',
        'icon' => 'fal fa-tags',
        'url' => '/course-category/index'
    ],
    [
        'label' => 'Danh sách bài học',
        'icon' => 'fal fa-book',
        'url' => '/course-lesson/index'
    ],
    [
        'label' => 'Danh sách phần học',
        'icon' => 'fal fa-th',
        'url' => '/course-section/index'
    ],
    [
        'label' => 'Danh sách HLV',
        'icon' => 'fal fa-user-md',
        'url' => '/coach-course/index'
    ],
];
$group_users = [
    [
        'label' => 'Danh sách khách hàng',
        'icon' => 'fal fa-users',
        'url' => '/customer/index'
    ],
    [
        'label' => 'Đăng ký dùng thử',
        'icon' => 'fal fa-tags',
        'url' => '/register-practice-try/index'
    ],
    [
        'label' => 'Giao dịch mua khoá học',
        'icon' => 'fal fa-money-bill-alt',
        'url' => '/history-transaction/index'
    ]
];
$group_setting = [
    [
        'label' => 'Quản lý tài khoản',
        'icon' => 'fal fa-user',
        'url' => '#'
    ],
    [
        'label' => 'Quản lý vai trò',
        'icon' => 'fal fa-users',
        'url' => '/role/index'
    ],
    [
        'label' => 'Quản lý task',
        'icon' => 'fal fa-tasks',
        'url' => '/permission/index'
    ]
];
$group_news = [
    [
        'label' => 'Danh sách bài viết',
        'icon' => 'fal fa-list',
        'url' => '/news/index'
    ],
    [
        'label' => 'Chuyên mục & tags',
        'icon' => 'fal fa-tags',
        'url' => '/category-tags/index'
    ]
];
$menu_group_controller = [
    [
        'label' => 'Dashboard',
        'controller' => 'site',
        'child_action' => $group_dashboard
    ],
    [
        'label' => 'Khoá học',
        'icon'  => 'fal fa-window',
        'controller' => 'course,coach-course,coach-section,course-category,course-lesson',
        'child_action' => $group_course
    ],
    [
        'label' => 'Tin tức',
        'icon'  => 'fal fa-newspaper',
        'controller' => 'news,category-tags',
        'child_action' => $group_news
    ],
    [
        'label' => 'Khách hàng',
        'icon'  => 'fal fa-users',
        'controller' => 'users,user-course,register-practice-try,history-transaction',
        'child_action' => $group_users
    ],
    // [
    //     'label' => 'Cấu hình',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => $group_setting
    // ]
];

return [
    "menu" => $menu_group_controller
];
?>