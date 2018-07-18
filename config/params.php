<?php

if (YII_ENV_PROD) {
    $allowedDomains = [
        'https://www.tbt88.org'
    ];
    $portalUrl = "https://www.tbt88.org";
    $serverName = "www.tbt88.org";
} else if (YII_ENV_TEST) {
    $allowedDomains = ['https://uat.tbt88.org'];
    $portalUrl = "https://uat.tbt88.org";
    $serverName = "uat.tbt88.org";
} else {
    $allowedDomains = ['*'];
    $portalUrl = "http://localhost:9200";
    $serverName = "127.0.0.1";
}

return [
    'GLOBAL' => [
        'TOKEN_VALIDITY' => 86400,
        'SERVER_NAME' => $serverName,
        'SECRET_KEY' => 'gkFjY/HfejuhrECU4TZkF1V5ABqfeT+EyZYwxfu/fodWUcO31DXM1A2YkFJj4p70GULv9yv014nnZPJBYDleiQ==',
        'RECORDS_PER_PAGE' => 10,
        'EMAIL_FROM' => 'support@tbt88.org',
        'MUTEX_LOCK_TIMEOUT' => 60, //60 seconds,
        'JOIN_TYPE' => [
            'LEFT' => 'LEFT JOIN',
            'RIGHT' => 'RIGHT JOIN',
            'INNER' => 'INNER JOIN',
        ],
        'ALLOWED_DOMAINS' => $allowedDomains,
        'ALLOWED_REQUEST_HEADERS' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization', 'If-Modified-Since', 'Cache-Control', 'Pragma'],
        'PORTAL_URL' => $portalUrl
    ],
    'FORMAT' => [
        'DATETIME' => 'd-M-Y H:i:s',
        'DATE' => 'd-M-Y'
    ],
    'FILE_TEMPLATE' => [
        'MODE' => [
            'DOWNLOAD' => 1,
            'VIEW' => 2
        ],
        'REPORT_FOLDER' => '//tenant/report',
        'REPORT' => [
            'TENANT' => [
                'LAYOUT' => [
                    'GENERAL' => 1000,
                ],
                'PO' => [
                    'PO' => 1100,
                ],
            ]
        ]
    ],
    'USER' => [
        'TYPE' => [
            'ADMIN' => 1,
            'MASTER' => 2,
            'AGENT' => 3,
            'PLAYER' => 4
        ],
        'DETAIL' => [
            'BET_METHOD' => [
                'MULTIPLY' => 1,
                'DIVIDE' => 2
            ],
            'AUTO_TRANSFER_MODE' => [
                'DAILY' => 1,
                'WEEKLY' => 2
            ]
        ]
    ],
    'AUTH_ITEM' => [
        'TYPE' => [
            'ROLE' => 1,
            'PERMISSION' => 2
        ],
        'ROLE' => [ //Define 4 roles to be used in the whole system
            'ADMIN' => 'ADMIN',
            'MASTER' => 'MASTER',
            'AGENT' => 'AGENT',
            'PLAYER' => 'PLAYER'
        ]
    ],
    'BET' => [
        'STATUS' => [
            'NEW' => 1,
            'VOID' => 2
        ],
        'DETAIL' => [
            'STATUS' => [
                'ACCEPTED' => 1,
                'LIMITED' => 2,
                'REJECTED' => 3,
                'VOID' => 4,
                'LOST' => 5,
                'WON' => 6
            ],
            'WIN_PRIZE_TYPE' => [
                '4D_BIG_PRIZE_1' => 1,
                '4D_BIG_PRIZE_2' => 2,
                '4D_BIG_PRIZE_3' => 3,
                '4D_BIG_STARTERS' => 4,
                '4D_BIG_CONSOLATION' => 5,
                '4D_SMALL_PRIZE_1' => 6,
                '4D_SMALL_PRIZE_2' => 7,
                '4D_SMALL_PRIZE_3' => 8,
                '4D_4A_PRIZE' => 9,
                '4D_4B_PRIZE' => 10,
                '4D_4C_PRIZE' => 11,
                '4D_4D_PRIZE' => 12,
                '4D_4E_PRIZE' => 13,
                '4D_4F_PRIZE' => 14,
                'IBOX_BIG_24_PRIZE_1' => 15,
                'IBOX_BIG_24_PRIZE_2' => 16,
                'IBOX_BIG_24_PRIZE_3' => 17,
                'IBOX_BIG_24_STARTERS' => 18,
                'IBOX_BIG_24_CONSOLATION' => 19,
                'IBOX_BIG_12_PRIZE_1' => 20,
                'IBOX_BIG_12_PRIZE_2' => 21,
                'IBOX_BIG_12_PRIZE_3' => 22,
                'IBOX_BIG_12_STARTERS' => 23,
                'IBOX_BIG_12_CONSOLATION' => 24,
                'IBOX_BIG_6_PRIZE_1' => 25,
                'IBOX_BIG_6_PRIZE_2' => 26,
                'IBOX_BIG_6_PRIZE_3' => 27,
                'IBOX_BIG_6_STARTERS' => 28,
                'IBOX_BIG_6_CONSOLATION' => 29,
                'IBOX_BIG_4_PRIZE_1' => 30,
                'IBOX_BIG_4_PRIZE_2' => 31,
                'IBOX_BIG_4_PRIZE_3' => 32,
                'IBOX_BIG_4_STARTERS' => 33,
                'IBOX_BIG_4_CONSOLATION' => 34,
                'IBOX_SMALL_24_PRIZE_1' => 35,
                'IBOX_SMALL_24_PRIZE_2' => 36,
                'IBOX_SMALL_24_PRIZE_3' => 37,
                'IBOX_SMALL_12_PRIZE_1' => 38,
                'IBOX_SMALL_12_PRIZE_2' => 39,
                'IBOX_SMALL_12_PRIZE_3' => 40,
                'IBOX_SMALL_6_PRIZE_1' => 41,
                'IBOX_SMALL_6_PRIZE_2' => 42,
                'IBOX_SMALL_6_PRIZE_3' => 43,
                'IBOX_SMALL_4_PRIZE_1' => 44,
                'IBOX_SMALL_4_PRIZE_2' => 45,
                'IBOX_SMALL_4_PRIZE_3' => 46,
                'IBOX_4D_4A_24_PRIZE' => 47,
                'IBOX_4D_4A_12_PRIZE' => 48,
                'IBOX_4D_4A_6_PRIZE' => 49,
                'IBOX_4D_4A_4_PRIZE' => 50,
                '3D_ABC_PRIZE_1' => 51,
                '3D_ABC_PRIZE_2' => 52,
                '3D_ABC_PRIZE_3' => 53,
                '3D_3A_PRIZE' => 54,
                '3D_3B_PRIZE' => 55,
                '3D_3C_PRIZE' => 56,
                '3D_3D_PRIZE' => 57,
                '3D_3E_PRIZE' => 58,
                'GD_4D_BIG_PRIZE_1' => 59,
                'GD_4D_BIG_PRIZE_2' => 60,
                'GD_4D_BIG_PRIZE_3' => 61,
                'GD_4D_BIG_STARTERS' => 62,
                'GD_4D_BIG_CONSOLATION' => 63,
                'GD_4D_SMALL_PRIZE_1' => 64,
                'GD_4D_SMALL_PRIZE_2' => 65,
                'GD_4D_SMALL_PRIZE_3' => 66,
                'GD_4D_4A_PRIZE' => 67,
                'GD_4D_4B_PRIZE' => 68,
                'GD_4D_4C_PRIZE' => 69,
                'GD_4D_4D_PRIZE' => 70,
                'GD_4D_4E_PRIZE' => 71,
                'GD_4D_4F_PRIZE' => 72,
                'GD_IBOX_BIG_24_PRIZE_1' => 73,
                'GD_IBOX_BIG_24_PRIZE_2' => 74,
                'GD_IBOX_BIG_24_PRIZE_3' => 75,
                'GD_IBOX_BIG_24_STARTERS' => 76,
                'GD_IBOX_BIG_24_CONSOLATION' => 77,
                'GD_IBOX_BIG_12_PRIZE_1' => 78,
                'GD_IBOX_BIG_12_PRIZE_2' => 79,
                'GD_IBOX_BIG_12_PRIZE_3' => 80,
                'GD_IBOX_BIG_12_STARTERS' => 81,
                'GD_IBOX_BIG_12_CONSOLATION' => 82,
                'GD_IBOX_BIG_6_PRIZE_1' => 83,
                'GD_IBOX_BIG_6_PRIZE_2' => 84,
                'GD_IBOX_BIG_6_PRIZE_3' => 85,
                'GD_IBOX_BIG_6_STARTERS' => 86,
                'GD_IBOX_BIG_6_CONSOLATION' => 87,
                'GD_IBOX_BIG_4_PRIZE_1' => 88,
                'GD_IBOX_BIG_4_PRIZE_2' => 89,
                'GD_IBOX_BIG_4_PRIZE_3' => 90,
                'GD_IBOX_BIG_4_STARTERS' => 91,
                'GD_IBOX_BIG_4_CONSOLATION' => 92,
                'GD_IBOX_SMALL_24_PRIZE_1' => 93,
                'GD_IBOX_SMALL_24_PRIZE_2' => 94,
                'GD_IBOX_SMALL_24_PRIZE_3' => 95,
                'GD_IBOX_SMALL_12_PRIZE_1' => 96,
                'GD_IBOX_SMALL_12_PRIZE_2' => 97,
                'GD_IBOX_SMALL_12_PRIZE_3' => 98,
                'GD_IBOX_SMALL_6_PRIZE_1' => 99,
                'GD_IBOX_SMALL_6_PRIZE_2' => 100,
                'GD_IBOX_SMALL_6_PRIZE_3' => 101,
                'GD_IBOX_SMALL_4_PRIZE_1' => 102,
                'GD_IBOX_SMALL_4_PRIZE_2' => 103,
                'GD_IBOX_SMALL_4_PRIZE_3' => 104,
                'GD_IBOX_4D_4A_24_PRIZE' => 105,
                'GD_IBOX_4D_4A_12_PRIZE' => 106,
                'GD_IBOX_4D_4A_6_PRIZE' => 107,
                'GD_IBOX_4D_4A_4_PRIZE' => 108,
                'GD_3D_ABC_PRIZE_1' => 109,
                'GD_3D_ABC_PRIZE_2' => 110,
                'GD_3D_ABC_PRIZE_3' => 111,
                'GD_3D_3A_PRIZE' => 112,
                'GD_3D_3B_PRIZE' => 113,
                'GD_3D_3C_PRIZE' => 114,
                'GD_3D_3D_PRIZE' => 115,
                'GD_3D_3E_PRIZE' => 116,
                '5D_PRIZE_1' => 117,
                '5D_PRIZE_2' => 118,
                '5D_PRIZE_3' => 119,
                '5D_PRIZE_4' => 120,
                '5D_PRIZE_5' => 121,
                '5D_PRIZE_6' => 122,
                '6D_PRIZE_1' => 123,
                '6D_PRIZE_2' => 124,
                '6D_PRIZE_3' => 125,
                '6D_PRIZE_4' => 126,
                '6D_PRIZE_5' => 127
            ]
        ]
    ],
    'COMPANY' => [
        'CODE' => [
            'MAGNUM' => 'M',
            'PMP' => 'P',
            'TOTO' => 'T',
            'SINGAPORE' => 'S',
            'SABAH' => 'B',
            'SANDAKAN' => 'K',
            'SARAWAK' => 'W',
            'GD' => 'H',
        ]
    ],
    'CREDIT_TRANSACTION' => [
        'ACTION' => [
            'BET' => 1,
            'WIN' => 2,
            'RESET' => 3
        ]
    ],
    'PACKAGE' => [
        'RECOMMENDED_PRIZE' => [
            '4D_BIG_PRIZE_1' => 2500,
            '4D_BIG_PRIZE_2' => 1000,
            '4D_BIG_PRIZE_3' => 500,
            '4D_BIG_STARTERS' => 220,
            '4D_BIG_CONSOLATION' => 66,
            '4D_SMALL_PRIZE_1' => 3500,
            '4D_SMALL_PRIZE_2' => 2000,
            '4D_SMALL_PRIZE_3' => 1000,
            '4D_4A_PRIZE' => 6000,
            '4D_4B_PRIZE' => 6000,
            '4D_4C_PRIZE' => 6000,
            '4D_4D_PRIZE' => 600,
            '4D_4E_PRIZE' => 600,
            '4D_4F_PRIZE' => 2000,
            '3D_ABC_PRIZE_1' => 220,
            '3D_ABC_PRIZE_2' => 220,
            '3D_ABC_PRIZE_3' => 220,
            '3D_3A_PRIZE' => 660,
            '3D_3B_PRIZE' => 660,
            '3D_3C_PRIZE' => 660,
            '3D_3D_PRIZE' => 66,
            '3D_3E_PRIZE' => 66,
            'GD_4D_BIG_PRIZE_1' => 2625,
            'GD_4D_BIG_PRIZE_2' => 1050,
            'GD_4D_BIG_PRIZE_3' => 525,
            'GD_4D_BIG_STARTERS' => 210,
            'GD_4D_BIG_CONSOLATION' => 63,
            'GD_4D_SMALL_PRIZE_1' => 3675,
            'GD_4D_SMALL_PRIZE_2' => 2100,
            'GD_4D_SMALL_PRIZE_3' => 1050,
            'GD_4D_4A_PRIZE' => 6300,
            'GD_4D_4B_PRIZE' => 6300,
            'GD_4D_4C_PRIZE' => 6300,
            'GD_4D_4D_PRIZE' => 630,
            'GD_4D_4E_PRIZE' => 630,
            'GD_4D_4F_PRIZE' => 2100,
            'GD_3D_ABC_PRIZE_1' => 262.5,
            'GD_3D_ABC_PRIZE_2' => 220.5,
            'GD_3D_ABC_PRIZE_3' => 157.5,
            'GD_3D_3A_PRIZE' => 693,
            'GD_3D_3B_PRIZE' => 693,
            'GD_3D_3C_PRIZE' => 693,
            'GD_3D_3D_PRIZE' => 69.3,
            'GD_3D_3E_PRIZE' => 69.3,
            '5D_PRIZE_1' => 16500,
            '5D_PRIZE_2' => 5500,
            '5D_PRIZE_3' => 3300,
            '5D_PRIZE_4' => 550,
            '5D_PRIZE_5' => 22,
            '5D_PRIZE_6' => 5.5,
            '6D_PRIZE_1' => 110000,
            '6D_PRIZE_2' => 3300,
            '6D_PRIZE_3' => 330,
            '6D_PRIZE_4' => 33,
            '6D_PRIZE_5' => 4.4
        ]
    ]
];

