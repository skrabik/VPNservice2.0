<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\PaymentMethod;

use App\Models\PaymentMethod;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PaymentMethodListLayout extends Table
{
    protected $target = 'payment_methods';

    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort()->filter(),
            TD::make('title', 'Title')->sort()->filter(),
            TD::make('slug', 'Slug')->sort()->filter(),
            TD::make('description', 'Description')->sort()->filter(),
            TD::make('active', 'Active')->render(fn (PaymentMethod $pm) => $pm->active ? 'Yes' : 'No'),
            TD::make('created_at', 'Created At')->render(fn (PaymentMethod $pm) => optional($pm->created_at)->format('d.m.Y H:i')),
            TD::make('Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (PaymentMethod $pm) => Link::make('Edit')
                    ->icon('bs.pencil')
                    ->route('platform.payment_methods.edit', $pm)),
        ];
    }
}
