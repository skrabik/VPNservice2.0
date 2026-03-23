<?php

use App\Http\Controllers\Customer\CabinetController;
use App\Http\Controllers\Customer\KeyController;
use App\Http\Controllers\Customer\SupportController;
use App\Http\Controllers\CustomerAuth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerAuth\ClaimRegistrationController;
use App\Http\Controllers\CustomerAuth\RegisteredUserController;
use App\Http\Controllers\CustomerAuth\TelegramAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->name('customer.')->group(function () {
    Route::middleware('customer.guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::post('telegram-login', [TelegramAuthenticatedSessionController::class, 'store'])->name('telegram.store');

        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

        Route::middleware('signed')->group(function () {
            Route::get('claim/{customer}', [ClaimRegistrationController::class, 'create'])->name('claim.create');
            Route::post('claim/{customer}', [ClaimRegistrationController::class, 'store'])->name('claim.store');
        });
    });

    Route::middleware('customer.auth')->group(function () {
        Route::get('/', [CabinetController::class, 'dashboard'])->name('dashboard');
        Route::get('status', [CabinetController::class, 'status'])->name('status');
        Route::get('instructions', [CabinetController::class, 'instructions'])->name('instructions');
        Route::get('pay', [CabinetController::class, 'pay'])->name('pay');

        Route::get('keys', [KeyController::class, 'index'])->name('keys');
        Route::post('keys', [KeyController::class, 'store'])->name('keys.store');

        Route::get('support', [SupportController::class, 'index'])->name('support');
        Route::post('support', [SupportController::class, 'store'])->name('support.store');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
