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
                'route' => 'admin.games.index',
                'permission' => '',
            ],
            [
                'text'  => 'Old Result',
                'icon'  => 'clock',
                'route' => 'admin.games.create',
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
                'route' => 'admin.games.index',
                'permission' => '',
            ],
            [
                'text'  => 'View Winner',
                'icon'  => 'eye',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Withdrawal',
        'icon'  => 'dollar-sign',
        'id'    => 'collapseWithdrawal',
        'items' => [
            [
                'text'  => 'Withdrawal Request',
                'icon'  => 'arrow-up-right',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Withdrawal History',
                'icon'  => 'file-text',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
        ],
    ],
    [
        'text'  => 'HomePage',
        'icon'  => 'home',
        'route' => 'admin.dashboard',
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Pages',
        'icon'  => 'layers',
        'id'    => 'collapsePages',
        'items' => [
            [
                'text'  => 'Page List',
                'icon'  => 'list',
                'route' => 'admin.pages.index',
                'permission' => '',
            ],
            [
                'text'  => 'Add Pages',
                'icon'  => 'file-plus',
                'route' => 'admin.pages.create',
                'permission' => '',
            ],
        ],
    ],
    [
        'type'  => 'dropdown',
        'text'  => 'Reports',
        'icon'  => 'pie-chart',
        'id'    => 'collapseReports',
        'items' => [
            [
                'text'  => 'Bid History',
                'icon'  => 'rotate-ccw',
                'route' => 'admin.games.index',
                'permission' => '',
            ],
            [
                'text'  => 'Winner History',
                'icon'  => 'award',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Transaction History',
                'icon'  => 'repeat',
                'route' => 'admin.games.create',
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
                'text'  => 'Roles & Permissions',
                'icon'  => 'shield',
                'route' => 'admin.games.create',
                'permission' => '',
            ],
            [
                'text'  => 'Member & Roles',
                'icon'  => 'user-check',
                'route' => 'admin.games.create',
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
                'route' => 'admin.games.index',
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
