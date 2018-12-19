<?php

if (YII_ENV_PROD) {
    $allowedDomains = [
        'http://www.tbt88.net'
    ];
    $portalUrl = "http://www.tbt88.net";
    $serverName = "www.tbt88.net";
} else if (YII_ENV_TEST) {
    $allowedDomains = ['http://staging.tbt88.net'];
    $portalUrl = "http://staging.tbt88.net";
    $serverName = "staging.tbt88.net";
} else {
    $allowedDomains = ['*'];
    $portalUrl = "http://localhost:9200";
    $serverName = "127.0.0.1";
}

return [
    'GLOBAL' => [
        'API_VERSION' => '1.2.0',//API Release Version
        'TOKEN_VALIDITY' => 86400,
        'SERVER_NAME' => $serverName,
        'SECRET_KEY' => 'gkFjY/HfejuhrECU4TZkF1V5ABqfeT+EyZYwxfu/fodWUcO31DXM1A2YkFJj4p70GULv9yv014nnZPJBYDleiQ==',
        'RECORDS_PER_PAGE' => 50,
        'EMAIL_FROM' => 'support@tbt88.org',
        'MUTEX_LOCK_TIMEOUT' => 60, //60 seconds,
        'JOIN_TYPE' => [
            'LEFT' => 'LEFT JOIN',
            'RIGHT' => 'RIGHT JOIN',
            'INNER' => 'INNER JOIN',
        ],
        'ALLOWED_DOMAINS' => $allowedDomains,
        'ALLOWED_REQUEST_HEADERS' => ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'Authorization', 'If-Modified-Since', 'Cache-Control', 'Pragma'],
        'PORTAL_URL' => $portalUrl,
        'BET_VOID_ALLOW_MINUTES' => 5,
        'BET_AMOUNT_ACCEPTABLE_ERROR' => 0.1
    ],
    'FORMAT' => [
        'DATETIME' => 'd-m-Y H:i:s',
        'DATE' => 'd-m-Y'
    ],
    'FILE_TEMPLATE' => [
        'MODE' => [
            'DOWNLOAD' => 1,
            'VIEW' => 2
        ],
        'REPORT' => [
            'WIN_LOSS_DETAILS' => 1000,
            'DRAW_WINNING_NUMBER' => 1001,
            'COMPANY_DRAW_RESULTS' => 1002,
            'MASTER' => [ //Starts with 2000

            ],
            'ADMIN' => [ //Starts with 3000

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
                'MULTIPLE' => 1,
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
            'PROCESSED' => 2 //Processed Results
        ],
        'NUMBER' => [
            'OPTION' => [
                'SINGLE' => 1,
                'RETURN' => 2,
                'BOX' => 3,
                'IBOX' => 4,
                'PH' => 5
            ],
            'STATUS' => [
                'ACCEPTED' => 1,
                'LIMITED' => 2,
                'REJECTED' => 3
            ]
        ],
        'DETAIL' => [
            'STATUS' => [
                'ACCEPTED' => 1,
                'LIMITED' => 2,
                'REJECTED' => 3,
                'VOIDED' => 4
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
                '3D_ABC_PRIZE_1' => 15,
                '3D_ABC_PRIZE_2' => 16,
                '3D_ABC_PRIZE_3' => 17,
                '3D_3A_PRIZE' => 18,
                '3D_3B_PRIZE' => 19,
                '3D_3C_PRIZE' => 20,
                '3D_3D_PRIZE' => 21,
                '3D_3E_PRIZE' => 22,
                'GD_4D_BIG_PRIZE_1' => 23,
                'GD_4D_BIG_PRIZE_2' => 24,
                'GD_4D_BIG_PRIZE_3' => 25,
                'GD_4D_BIG_STARTERS' => 26,
                'GD_4D_BIG_CONSOLATION' => 27,
                'GD_4D_SMALL_PRIZE_1' => 28,
                'GD_4D_SMALL_PRIZE_2' => 29,
                'GD_4D_SMALL_PRIZE_3' => 30,
                'GD_4D_4A_PRIZE' => 31,
                'GD_4D_4B_PRIZE' => 32,
                'GD_4D_4C_PRIZE' => 33,
                'GD_4D_4D_PRIZE' => 34,
                'GD_4D_4E_PRIZE' => 35,
                'GD_4D_4F_PRIZE' => 36,
                'GD_3D_ABC_PRIZE_1' => 37,
                'GD_3D_ABC_PRIZE_2' => 38,
                'GD_3D_ABC_PRIZE_3' => 39,
                'GD_3D_3A_PRIZE' => 40,
                'GD_3D_3B_PRIZE' => 41,
                'GD_3D_3C_PRIZE' => 42,
                'GD_3D_3D_PRIZE' => 43,
                'GD_3D_3E_PRIZE' => 44,
                '5D_PRIZE_1' => 45,
                '5D_PRIZE_2' => 46,
                '5D_PRIZE_3' => 47,
                '5D_PRIZE_4' => 48,
                '5D_PRIZE_5' => 49,
                '5D_PRIZE_6' => 50,
                '6D_PRIZE_1' => 51,
                '6D_PRIZE_2' => 52,
                '6D_PRIZE_3' => 53,
                '6D_PRIZE_4' => 54,
                '6D_PRIZE_5' => 55
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
        ],
        'DRAW' => [
            'STATUS' => [
                'NEW' => 1,
                'DRAWN' => 2
            ]
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

