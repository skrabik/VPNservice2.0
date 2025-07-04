<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::post('telegram/webhook/{token}', [TelegramController::class, 'processWebhook'])
    ->name('process_webhook')
    ->withoutMiddleware(['web']);

require __DIR__.'/auth.php';
