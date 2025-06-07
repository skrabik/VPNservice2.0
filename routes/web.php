<?php

use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::post('telegram/webhook/{token}', [TelegramController::class, 'processWebhook'])
    ->name('process_webhook')
    ->withoutMiddleware(['web']);

require __DIR__.'/auth.php';
