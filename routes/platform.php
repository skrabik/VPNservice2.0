<?php

declare(strict_types=1);

use App\Orchid\Screens\AdminNotification\AdminNotificationScreen;
use App\Orchid\Screens\Customers\CustomerEditScreen;
use App\Orchid\Screens\Customers\CustomerListScreen;
use App\Orchid\Screens\PaymentMethods\PaymentMethodEditScreen;
use App\Orchid\Screens\PaymentMethods\PaymentMethodListScreen;
use App\Orchid\Screens\Plans\PlanEditScreen;
use App\Orchid\Screens\Plans\PlanListScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Server\ServerEditScreen;
use App\Orchid\Screens\Server\ServerListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Orchid\Support\Facades\Dashboard;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

Route::screen('info', AdminNotificationScreen::class)
    ->name('platform.notifications');

Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

Route::prefix('customers')->group(function () {
    Route::screen('/', CustomerListScreen::class)
        ->name('platform.customers')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Customers'), route('platform.customers')));

    Route::screen('create', CustomerEditScreen::class)
        ->name('platform.customers.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.customers')
            ->push(__('Create'), route('platform.customers.create')));

    Route::screen('{customer}/edit', CustomerEditScreen::class)
        ->name('platform.customers.edit')
        ->breadcrumbs(fn (Trail $trail, $customer) => $trail
            ->parent('platform.customers')
            ->push($customer->first_name, route('platform.customers.edit', $customer)));
});

Route::prefix('users')->group(function () {
    Route::screen('/', UserListScreen::class)
        ->name('platform.systems.users')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Users'), route('platform.systems.users')));

    Route::screen('create', UserEditScreen::class)
        ->name('platform.systems.users.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.systems.users')
            ->push(__('Create'), route('platform.systems.users.create')));

    Route::screen('{user}/edit', UserEditScreen::class)
        ->name('platform.systems.users.edit')
        ->breadcrumbs(fn (Trail $trail, $user) => $trail
            ->parent('platform.systems.users')
            ->push($user->name, route('platform.systems.users.edit', $user)));
});

Route::prefix('roles')->group(function () {
    Route::screen('', RoleListScreen::class)
        ->name('platform.systems.roles')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Roles'), route('platform.systems.roles')));

    Route::screen('create', RoleEditScreen::class)
        ->name('platform.systems.roles.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.systems.roles')
            ->push(__('Create'), route('platform.systems.roles.create')));

    Route::screen('{role}/edit', RoleEditScreen::class)
        ->name('platform.systems.roles.edit')
        ->breadcrumbs(fn (Trail $trail, $role) => $trail
            ->parent('platform.systems.roles')
            ->push($role->name, route('platform.systems.roles.edit', $role)));
});

Route::prefix('plans')->group(function () {
    Route::screen('/', PlanListScreen::class)
        ->name('platform.plans')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Plans'), route('platform.plans')));

    Route::screen('create', PlanEditScreen::class)
        ->name('platform.plans.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.plans')
            ->push(__('Create'), route('platform.plans.create')));

    Route::screen('{plan}/edit', PlanEditScreen::class)
        ->name('platform.plans.edit')
        ->breadcrumbs(fn (Trail $trail, $plan) => $trail
            ->parent('platform.plans')
            ->push($plan->title, route('platform.plans.edit', $plan)));
});

Route::prefix('payment_methods')->group(function () {
    Route::screen('/', PaymentMethodListScreen::class)
        ->name('platform.payment_methods')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Payment Methods'), route('platform.payment_methods')));

    Route::screen('create', PaymentMethodEditScreen::class)
        ->name('platform.payment_methods.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.payment_methods')
            ->push(__('Create'), route('platform.payment_methods.create')));

    Route::screen('{payment_method}/edit', PaymentMethodEditScreen::class)
        ->name('platform.payment_methods.edit')
        ->breadcrumbs(fn (Trail $trail, $payment_method) => $trail
            ->parent('platform.payment_methods')
            ->push($payment_method->title, route('platform.payment_methods.edit', $payment_method)));
});

Route::prefix('servers')->group(function () {
    Route::screen('/', ServerListScreen::class)
        ->name('platform.servers')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.index')
            ->push(__('Servers'), route('platform.servers')));

    Route::screen('create', ServerEditScreen::class)
        ->name('platform.servers.create')
        ->breadcrumbs(fn (Trail $trail) => $trail
            ->parent('platform.servers')
            ->push(__('Create'), route('platform.servers.create')));

    Route::screen('{server}/edit', ServerEditScreen::class)
        ->name('platform.servers.edit')
        ->breadcrumbs(fn (Trail $trail, $server) => $trail
            ->parent('platform.servers')
            ->push(__('Edit'), route('platform.servers.edit', $server)));
});
