<?php

declare(strict_types=1);

namespace App\Orchid\Screens\PaymentMethods;

use App\Models\PaymentMethod;
use App\Orchid\Layouts\PaymentMethod\PaymentMethodListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class PaymentMethodListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'payment_methods' => PaymentMethod::defaultSort('id', 'desc')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Payment Methods';
    }

    public function description(): ?string
    {
        return 'List of all payment methods';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.payment_methods',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add')
                ->icon('bs.plus-circle')
                ->route('platform.payment_methods.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            PaymentMethodListLayout::class,
        ];
    }
}
