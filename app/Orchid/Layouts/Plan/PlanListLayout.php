<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Plan;

use App\Models\Plan;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PlanListLayout extends Table
{
    /**
     * @var string
     */
    protected $target = 'plans';

    /**
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')->sort()->filter(),
            TD::make('title', 'Title')->sort()->filter(),
            TD::make('slug', 'Slug')->sort()->filter(),
            TD::make('price', 'Price')->sort()->filter(),
            TD::make('period', 'Period (days)')->sort()->filter(),
            TD::make('active', 'Active')->render(fn (Plan $plan) => $plan->active ? 'Yes' : 'No'),
            TD::make('created_at', 'Created At')->render(fn (Plan $plan) => optional($plan->created_at)->format('d.m.Y H:i')),
            TD::make('Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Plan $plan) => Link::make('Edit')
                    ->icon('bs.pencil')
                    ->route('platform.plans.edit', $plan)),
        ];
    }
}
