<?php

return [
    'default' => 'main',
    'test_chat_id' => env('TELEGRAM_TEST_CHAT_ID'),

    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
        ],
    ],
];
