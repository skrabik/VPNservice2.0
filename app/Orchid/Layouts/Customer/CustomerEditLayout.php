<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Customer;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class CustomerEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('customer.first_name')
                ->type('text')
                ->max(256)
                ->required()
                ->title(__('First Name'))
                ->placeholder(__('Enter first name')),

            Input::make('customer.last_name')
                ->type('text')
                ->max(256)
                ->title(__('Last Name'))
                ->placeholder(__('Enter last name')),

            Input::make('customer.email')
                ->type('email')
                ->max(256)
                ->required()
                ->title(__('Email'))
                ->placeholder(__('Enter email')),

            Input::make('customer.password')
                ->type('password')
                ->max(256)
                ->title(__('Password'))
                ->placeholder(__('Enter password')),

            Input::make('customer.telegram_id')
                ->type('text')
                ->max(256)
                ->title(__('Telegram ID'))
                ->placeholder(__('Enter Telegram ID')),

            Input::make('customer.telegram_username')
                ->type('text')
                ->max(256)
                ->title(__('Telegram Username'))
                ->placeholder(__('Enter Telegram username')),

            Select::make('customer.status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->title(__('Status'))
                ->help(__('Select customer status')),
        ];
    }
}
