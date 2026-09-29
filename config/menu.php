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
                'permission' => '',
            ],
            [
                'text'  => 'Add Game',
                'icon'  => 'plus-circle',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Result',
        'icon'  => 'award',
        'id'    => 'collapseResult',
        'items' => [
            [
                'text'  => 'Today Result',
                'icon'  => 'calendar',
                'route' => 'admin.results.index',
                'permission' => '',
            ],
        ],
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
                'permission' => '',
            ],
            [
                'text'  => 'Add User',
                'icon'  => 'user-plus',
                'route' => 'admin.users.create',
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
                'permission' => '',
            ],
            [
                'text'  => 'View Winner',
                'icon'  => 'eye',
                'route' => 'admin.winner.index',
                'permission' => '',
            ],
            [
                'text'  => 'Profit / Loss',
                'icon'  => 'trending-up',
                'route' => 'admin.games.create',
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
                'permission' => '',
            ],
            [
                'text'  => 'Withdrawal Request',
                'icon'  => 'arrow-up-right',
                'route' => 'admin.wallet.request-withdraw',
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
                'permission' => '',
            ],
            [
                'text'  => 'HomePage',
                'icon'  => 'home',
                'route' => 'admin.dashboard',
            ],
            [
                'text'  => 'Faqs',
                'icon'  => 'help-circle',
                'route' => 'admin.faqs.index',
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
                'route' => 'admin.games.index',
                'permission' => '',
            ],
            [
                'text'  => 'Member & Roles',
                'icon'  => 'user-check',
                'route' => 'admin.member.index',
                'permission' => '',
            ],
            [
                'text'  => 'Roles & Permissions',
                'icon'  => 'shield',
                'route' => 'admin.roles.index',
                'permission' => '',
            ],
            [
                'text'  => 'Email Setting',
                'icon'  => 'mail',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Global SEO',
                'icon'  => 'globe',
                'route' => 'admin.games.create',
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
                'permission' => '',
            ],
            [
                'text'  => 'Marque',
                'icon'  => 'type',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Contact Details',
                'icon'  => 'phone',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Set limits',
                'icon'  => 'sliders',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Admin Notice',
                'icon'  => 'bell',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Live Chat',
                'icon'  => 'message-square',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
        ],
    ],
    [
        'text'  => 'Logout',
        'icon'  => 'log-out',
        'route' => 'admin.dashboard',
    ],
];
