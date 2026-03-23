<?php

return [
    'base_url' => env('YOOKASSA_BASE_URL', 'https://api.yookassa.ru/v3'),
    'shop_id' => env('YOOKASSA_SHOP_ID'),
    'secret_key' => env('YOOKASSA_SECRET_KEY'),
    'return_url' => env('YOOKASSA_RETURN_URL', env('APP_URL')),
    'enabled' => (bool) env('YOOKASSA_ENABLED', false),
];
