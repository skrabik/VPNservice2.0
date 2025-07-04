<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make(__('Notifications'))
                ->icon('bs.info-circle')
                ->route('platform.notifications')
                ->permission('platform.notifications'),

            Menu::make(__('Customers'))
                ->icon('bs.person-arms-up')
                ->route('platform.customers')
                ->permission('platform.customers'),

            Menu::make('Servers')
                ->icon('server')
                ->route('platform.servers')
                ->title('Servers')
                ->permission('platform.servers'),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make(__('Plans'))
                ->icon('bs.list')
                ->route('platform.plans')
                ->permission('platform.plans'),

            Menu::make(__('Payment Methods'))
                ->icon('bs.credit-card')
                ->route('platform.payment_methods')
                ->permission('platform.payment_methods'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group(__('Platform'))
                ->addPermission('platform.notifications', __('Admin Notifications'))
                ->addPermission('platform.servers', __('Servers'))
                ->addPermission('platform.customers', __('Customers'))
                ->addPermission('platform.plans', __('Plans'))
                ->addPermission('platform.payment_methods', __('Payment Methods')),
        ];
    }
}
