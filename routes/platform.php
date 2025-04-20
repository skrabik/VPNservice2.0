<?php

declare(strict_types=1);

use App\Orchid\Screens\AdminNotifications\AdminNotificationScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
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

Route::screen('notifications', AdminNotificationScreen::class)
    ->name('platform.notifications');

Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

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
    Route::screen('roles', RoleListScreen::class)
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
