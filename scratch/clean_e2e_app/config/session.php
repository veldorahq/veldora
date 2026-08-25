<?php

return [
    'driver'          => env('SESSION_DRIVER', 'file'),
    'lifetime'        => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'cookie'          => 'veldora_session',
    'path'            => '/',
    'domain'          => env('SESSION_DOMAIN', null),
    'secure'          => env('SESSION_SECURE', false),
    'http_only'       => true,
    'same_site'       => 'lax',
];
