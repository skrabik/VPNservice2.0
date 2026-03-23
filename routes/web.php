<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\YooKassaWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/telegram/bot/get', [LandingController::class, 'clickStat'])->name('clickStat');

Route::post('telegram/webhook/{token}', [TelegramController::class, 'processWebhook'])
    ->name('process_webhook')
    ->withoutMiddleware(['web']);

Route::post('payments/yookassa/webhook', YooKassaWebhookController::class)
    ->name('yookassa.webhook')
    ->withoutMiddleware(['web']);

require __DIR__.'/auth.php';
require __DIR__.'/customer.php';
