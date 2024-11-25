<?php

return [
    'host' => env('REVERB_HOST', '127.0.0.1'),
    'port' => env('REVERB_PORT', 6001),
    'log'  => true,
    'apps' => [
        [
            'id'     => env('REVERB_APP_ID', '1'),
            'key'    => env('REVERB_APP_KEY', 'anyKey'),
            'secret' => env('REVERB_APP_SECRET', 'anySecret'),
            'name'   => env('REVERB_APP_NAME', 'OrderService'),
        ],
    ],
];

