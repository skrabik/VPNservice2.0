<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Plans;

use App\Models\Plan;
use App\Orchid\Layouts\Plan\PlanListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class PlanListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'plans' => Plan::defaultSort('id', 'desc')->paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Plans';
    }

    public function description(): ?string
    {
        return 'List of all plans';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.plans',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add')
                ->icon('bs.plus-circle')
                ->route('platform.plans.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            PlanListLayout::class,
        ];
    }
}
