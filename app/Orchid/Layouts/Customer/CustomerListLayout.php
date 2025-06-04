<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Customer;

use App\Models\Customer;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class CustomerListLayout extends Table
{
    /**
     * @var string
     */
    protected $target = 'customers';

    /**
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->filter()
                ->render(fn (Customer $customer) => $customer->id),

            TD::make('first_name', 'First Name')
                ->sort()
                ->filter()
                ->render(fn (Customer $customer) => $customer->first_name),

            TD::make('last_name', 'Last Name')
                ->sort()
                ->filter()
                ->render(fn (Customer $customer) => $customer->last_name),

            TD::make('email', 'Email')
                ->sort()
                ->filter()
                ->render(fn (Customer $customer) => $customer->email),

            TD::make('telegram_username', 'Telegram')
                ->sort()
                ->filter()
                ->render(fn (Customer $customer) => $customer->telegram_username),

            TD::make('created_at', 'Created')
                ->sort()
                ->render(fn (Customer $customer) => $customer->created_at->format('d.m.Y H:i')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Customer $customer) => Link::make(__('Edit'))
                    ->icon('bs.pencil')
                    ->route('platform.customers.edit', $customer)),
        ];
    }
}
