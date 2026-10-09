<?php

return [
    [
        'type' => 'heading',
        'text' => 'Core',
    ],
    [
        'text'  => 'Dashboards',
        'icon'  => 'activity',
        'route' => 'admin.dashboard',
    ],
    [
        'type' => 'heading',
        'text' => 'Game Section',
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Games',
        'icon'  => 'play-circle',
        'id'    => 'collapseGames',
        'items' => [
            [
                'text'       => 'Game List',
                'icon'       => 'list',
                'route'      => 'admin.games.index',
                'route_name' => 'admin.games.*',
                'permission' => '',
            ],
            [
                'text'  => 'Add Game',
                'icon'  => 'plus-circle',
                'route' => 'admin.games.create',
                'route_name' => 'admin.games.*',
                'permission' => '',
            ],
        ],
    ],
    [
        'text'  => 'Result',
        'icon'  => 'award',
        'route' => 'admin.results.index'
    ],
    [
        'text' => 'Push Notifications',
        'icon' => 'bell',
        'route' => 'admin.push-notifications.index',
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Users',
        'icon'  => 'users',
        'id'    => 'collapseUsers',
        'items' => [
            [
                'text'  => 'User List',
                'icon'  => 'user-check',
                'route' => 'admin.users.index',
                'route_name' => 'admin.users.*',
                'permission' => '',
            ],
            [
                'text'  => 'Add User',
                'icon'  => 'user-plus',
                'route' => 'admin.users.create',
                'route_name' => 'admin.users.*',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Core Function',
        'icon'  => 'cpu',
        'id'    => 'collapseFunction',
        'items' => [
            [
                'text'  => 'View Bids',
                'icon'  => 'eye',
                'route' => 'admin.bidding-desk.index',
                'route_name' => 'admin.bidding-desk.index',
                'permission' => '',
            ],
            [
                'text'  => 'View Winner',
                'icon'  => 'eye',
                'route' => 'admin.winner.index',
                'route_name' => 'admin.winner.*',
                'permission' => '',
            ],
            [
                'text'  => 'Profit / Loss',
                'icon'  => 'trending-up',
                'route' => 'admin.bidding-desk.profit_loss',
                'route_name' => 'admin.bidding-desk.profit_loss',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Money Request',
        'icon'  => 'dollar-sign',
        'id'    => 'collapseWithdrawal',
        'items' => [
            [
                'text'  => 'Add Money Request',
                'icon'  => 'arrow-up-right',
                'route' => 'admin.wallet.request-add',
                'route_name' => 'admin.wallet.*',
                'permission' => '',
            ],
            [
                'text'  => 'Withdrawal Request',
                'icon'  => 'arrow-up-right',
                'route' => 'admin.wallet.request-withdraw',
                'route_name' => 'admin.wallet.*',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Content Section',
        'icon'  => 'layers',
        'id'    => 'collapsePages',
        'items' => [
            [
                'text'  => 'Pages',
                'icon'  => 'list',
                'route' => 'admin.pages.index',
                'route_name' => 'admin.pages.*',
                'permission' => '',
            ],
            [
                'text'  => 'HomePage',
                'icon'  => 'home',
                'route' => 'admin.home-page.index',
                'route_name' => 'admin.home-page.*',
            ],
            [
                'text'  => 'Faqs',
                'icon'  => 'help-circle',
                'route' => 'admin.faqs.index',
                'route_name' => 'admin.faqs.*',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Settings',
        'icon'  => 'settings',
        'id'    => 'collapseSettings',
        'items' => [
            [
                'text'  => 'Global Config',
                'icon'  => 'sliders',
                'route' => 'admin.setting.create',
                'route_name' => 'admin.users.*',
                'permission' => '',
            ],
            [
                'text'  => 'Member & Roles',
                'icon'  => 'user-check',
                'route' => 'admin.member.index',
                'route_name' => 'admin.member.*',
                'permission' => '',
            ],
            [
                'text'  => 'Roles & Permissions',
                'icon'  => 'shield',
                'route' => 'admin.roles.index',
                'route_name' => 'admin.roles.*',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Mobile App',
        'icon'  => 'smartphone',
        'id'    => 'collapseMobileApp',
        'items' => [
            [
                'text'  => 'Banner',
                'icon'  => 'image',
                'route' => 'admin.banner.index',
                'route_name' => 'admin.banner.*',
                'permission' => '',
            ],
            [
                'text'  => 'Marque',
                'icon'  => 'type',
                'route' => 'admin.mobile-app.marque',
                'route_name' => 'admin.mobile-app.marque',
                'permission' => '',
            ],
            [
                'text'  => 'Set limits',
                'icon'  => 'sliders',
                'route' => 'admin.mobile-app.limits',
                'route_name' => 'admin.mobile-app.limits',
                'permission' => '',
            ],
            [
                'text'  => 'Admin Notice',
                'icon'  => 'bell',
                'route' => 'admin.mobile-app.notice',
                'route_name' => 'admin.mobile-app.notice',
                'permission' => '',
            ],
            [
                'text'  => 'Live Chat',
                'icon'  => 'message-square',
                'route' => 'admin.mobile-app.live_chat',
                'route_name' => 'admin.mobile-app.live_chat',
                'permission' => '',
            ],
        ],
    ],
    [
        'text'  => 'Logout',
        'icon'  => 'log-out',
        'route' => 'admin.logout',
    ],
];
