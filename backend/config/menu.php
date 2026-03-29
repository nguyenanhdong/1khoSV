<?php

$menu_group_controller = [
    [
        'label' => 'Banner',
        'icon'  => 'fal fa-cog',
        'controller' => 'assignment,role,permission',
        'child_action' => [
            [
                'label' => 'Banner',
                'icon' => 'fal fa-image',
                'url' => '/banner/index'
            ]
        ]
    ],
    [
        'label' => 'Chuyên mục',
        'icon'  => 'fal fa-cog',
        'controller' => 'assignment,role,permission',
        'child_action' => [
            [
                'label' => 'Chuyên mục',
                'icon' => 'fal fa-list',
                'url' => '/category/index'
            ]
        ]
    ],
    [
        'label' => 'Sản phẩm',
        'icon'  => 'fal fa-credit-card',
        'controller' => 'assignment,role,permission',
        'child_action' => [
            [
                'label' => 'Sản phẩm',
                'icon' => 'fal fa-credit-card',
                'url' => '/product/index'
            ],
        ]
    ],
    [
        'label' => 'Thông báo',
        'icon'  => 'fal fa-credit-card',
        'controller' => 'assignment,role,permission',
        'child_action' => [
            [
                'label' => 'Thông báo',
                'icon' => 'fal fa-credit-card',
                'url' => '/notify/index'
            ],
        ]
    ],
    [
        'label' => 'Voucher',
        'icon'  => 'fal fa-credit-card',
        'controller' => 'assignment,role,permission',
        'child_action' => [
            [
                'label' => 'Voucher',
                'icon' => 'fal fa-credit-card',
                'url' => '/voucher/index'
            ],
        ]
    ],
    // [
    //     'label' => 'Product VAS',
    //     'icon'  => 'fal fa-credit-card',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Product VAS',
    //             'icon' => 'fal fa-credit-card',
    //             'url' => '/product-vas/index'
    //         ],
    //     ]
    // ],
    // [
    //     'label' => 'Package',
    //     'icon'  => 'fal fa-credit-card',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Package',
    //             'icon' => 'fal fa-credit-card',
    //             'url' => '/package/index'
    //         ],
    //     ]
    // ],
    // // [
    // //     'label' => 'Product lucky draw',
    // //     'icon'  => 'fal fa-question-circle',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Product lucky draw',
    // //             'icon' => 'fal fa-credit-card',
    // //             'url' => '/product-lucky-draw/index'
    // //         ],
    // //     ]
    // // ],
    // // [
    // //     'label' => 'Brand',
    // //     'icon'  => 'fal fa-question-circle',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Brand',
    // //             'icon' => 'fal fa-credit-card',
    // //             'url' => '/brand/index'
    // //         ],
    // //     ]
    // // ],
    // [
    //     'label' => 'Config',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         // [
    //         //     'label' => 'Terms',
    //         //     'icon' => 'fal fa-list',
    //         //     'url' => '/config/terms'
    //         // ],
    //         [
    //             'label' => 'Sold out image',
    //             'icon' => 'fal fa-credit-card',
    //             'url' => '/config/sold-out-image'
    //         ],
    //     ]
    // ],
    // // [
    // //     'label' => 'Get coin',
    // //     'icon'  => 'fal fa-cog',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Get coin',
    // //             'icon' => 'fal fa-copyright',
    // //             'url' => '/get-coin/index'
    // //         ],
    // //     ]
    // // ],
    // // [
    // //     'label' => 'Accumulate points',
    // //     'icon'  => 'fal fa-bars',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Accumulate points list',
    // //             'icon' => 'fal fa-list',
    // //             'url' => '/tasks/index'
    // //         ],
    // //         [
    // //             'label' => 'Accumulate points item',
    // //             'icon' => 'fal fa-list-alt',
    // //             'url' => '/task-item/index'
    // //         ],
    // //     ]
    // // ],
    // [
    //     'label' => 'Report',
    //     'icon'  => 'fal fa-file-alt',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Transactions',
    //             'icon' => 'fal fa-history',
    //             'url' => '/report/report-transactions'
    //         ],
    //         [
    //             'label' => 'Receiving gift',
    //             'icon' => 'fal fa-gift',
    //             'url' => '/report/report-receiving-gift'
    //         ],
    //     ]
    // ],
    // // [
    // //     'label' => 'Config Currency',
    // //     'icon'  => 'fal fa-cog',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Currency',
    // //             'icon' => 'fal fa-copyright',
    // //             'url' => '/currency/index'
    // //         ],
    // //         [
    // //             'label' => 'Currency Exchange rate',
    // //             'icon' => 'fal fa-copyright',
    // //             'url' => '/currency-exchange-rate/index'
    // //         ],
    // //     ]
    // // ],
    // // [
    // //     'label' => 'Faq',
    // //     'icon'  => 'fal fa-question-circle',
    // //     'controller' => 'assignment,role,permission',
    // //     'child_action' => [
    // //         [
    // //             'label' => 'Faq',
    // //             'icon' => 'fal fa-question-circle',
    // //             'url' => '/faq/index'
    // //         ],
    // //     ]
    // // ],
    // [
    //     'label' => 'Account Management',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Account Management',
    //             'icon' => 'fal fa-user',
    //             'url' => '/assignment/index'
    //         ],
    //     ]
    // ],
    // [
    //     'label' => 'Config Home Regalos',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Config Home Regalos',
    //             'icon' => 'fal fa-home',
    //             'url' => '/config-home-page/index'
    //         ],
    //     ]
    // ],
    // [
    //     'label' => 'Config Home Beneficios',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Config Home Beneficios',
    //             'icon' => 'fal fa-home',
    //             'url' => '/config-home-page-beneficios/index'
    //         ],
    //     ]
    // ],
    // [
    //     'label' => 'Config Home VAS',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Config Home VAS',
    //             'icon' => 'fal fa-home',
    //             'url' => '/config-home-page-vas/index'
    //         ],
    //     ]
    // ],
    // [
    //     'label' => 'Config Package Page',
    //     'icon'  => 'fal fa-cog',
    //     'controller' => 'assignment,role,permission',
    //     'child_action' => [
    //         [
    //             'label' => 'Config Package Page',
    //             'icon' => 'fal fa-home',
    //             'url' => '/product-category/config-category-home-package'
    //         ],
    //     ]
    // ],
];

return [
    "menu" => $menu_group_controller
];
