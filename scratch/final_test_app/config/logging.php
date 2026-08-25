<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | Supported: "daily", "single"
    |
    */
    'default' => env('LOG_CHANNEL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/veldora.log'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs'),
            'days' => 14,
        ],
    ],
];
