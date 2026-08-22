<?php

return [
    'default' => 'web',

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'model',
            'model'  => 'App\\Models\\User',
        ],
    ],

    'passwords' => [
        'expire'   => 60,
        'throttle' => 60,
    ],
];
