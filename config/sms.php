<?php

return [

    'default' => env('SMS_DRIVER', 'log'),

    'log_channel' => env('SMS_LOG_CHANNEL', 'stack'),

    'drivers' => [
        'log' => [
            'class' => App\Services\Sms\Drivers\LogDriver::class,
            'credentials' => [],
        ],
        'sabanovin' => [
            'class' => App\Services\Sms\Drivers\SabanovinDriver::class,
            'credentials' => [
                'api_key' => '',
                'webhook_token' => '',
            ],
        ],
    ],

];
