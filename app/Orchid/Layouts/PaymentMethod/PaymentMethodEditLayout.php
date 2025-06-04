<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\PaymentMethod;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Layouts\Rows;

class PaymentMethodEditLayout extends Rows
{
    public function fields(): array
    {
        return [
            Input::make('payment_method.title')
                ->type('text')
                ->max(256)
                ->required()
                ->title('Title')
                ->placeholder('Enter payment method title'),

            Input::make('payment_method.slug')
                ->type('text')
                ->max(256)
                ->required()
                ->title('Slug')
                ->placeholder('Enter slug'),

            Input::make('payment_method.description')
                ->type('text')
                ->max(1024)
                ->title('Description')
                ->placeholder('Enter description'),

            Switcher::make('payment_method.active')
                ->title('Active'),
        ];
    }
}
