<?php

if (YII_ENV_PROD) {
    $allowedDomains = [
        'https://www.betfourd.com'
    ];
    $portalUrl = "https://www.betfourd.com";
    $serverName = "www.betfourd.com";
} else if (YII_ENV_TEST) {
    $allowedDomains = ['*'];
    $portalUrl = "https://uat.betfourd.com";
    $serverName = "uat.betfourd.com";
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
        'EMAIL_FROM' => 'support@betfourd.com',
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
        'TENANT_DOCUMENT_FOLDER' => '//tenant/document', //For view rendering usage (render()), the view name starts with double slahes - @app/views/pdf/
        'TENANT_REPORT_FOLDER' => '//tenant/report',
        'SUPPLIER_DOCUMENT_FOLDER' => '//supplier/document', //For view rendering usage (render()), the view name starts with double slahes - @app/views/pdf/
        'DOCUMENT' => [
            'TENANT' => [
                'LAYOUT' => [
                    'GENERAL' => 1000,
                ],
                'PR' => 1100,
                'PO' => 1101,
                'QUOTATION' => 1102,
                'AWARD_LETTER' => 1103,
                'SAMPLE_REQUEST' => 1104
            ]
        ],
        'REPORT' => [
            'TENANT' => [
                'LAYOUT' => [
                    'GENERAL' => 2000,
                ],
                'PO' => [
                    'PO' => 2100,
                    'PO_ITEMS' => 2101,
                    'PO_BY_SUPPLIER' => 2102,
                    'PO_DELIVERY_STATUS' => 2103,
                    'PO_TOP_ITEM' => 2104,
                    'PO_ITEM_PRICE_CHANGE' => 2105,
                    'PO_OUTSTANDING' => 2106,
                ],
                'GRN' => [
                    'GRN' => 2200,
                ],
                'TENDER' => [
                    'SUPPLIER_TENDER_QUOTE' => 2300,
                    'SUPPLIER_TENDER_NEGOTIATION' => 2301,
                    'TENDER_DETAILS' => 2302,
                    'TENDER_NEGOTIATION' => 2303,
                    'TENDER_ITEM_LISTING' => 2304,
                    'TENDER_SUPPLIER_LISTING' => 2305,
                    'TENDER_LOG' => 2306,
                    'TENDER_CHANGE_OF_AWARD' => 2307,
                    'MASTER_TENDER_LOG' => 2308
                ]
            ]
        ]
    ],
    'USER' => [
        'TYPE' => [
            'ADMIN' => 1, //Admin Portal Users
            'TENANT' => 2, //Tenant Portal Users
            'SUPPLIER' => 3, //Supplier Portal Users
        ]
    ],
    'EMAIL' => [
        'TYPE' => [
            'RESET_PASSWORD' => 1,
            'SUPPLIER_ACTIVATION' => 2
        ]
    ],
    'AUTH_ITEM' => [
        'TYPE' => [
            'ROLE' => 1,
            'PERMISSION' => 2
        ],
        'ROLE_PREFIX' => 'ROLE_',
        'PERMISSION_PREFIX' => 'PERMISSION_'
    ]
];
