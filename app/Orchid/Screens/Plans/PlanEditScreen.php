<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Plans;

use App\Models\Plan;
use App\Orchid\Layouts\Plan\PlanEditLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class PlanEditScreen extends Screen
{
    public $plan;

    public function query(Plan $plan): iterable
    {
        return [
            'plan' => $plan,
        ];
    }

    public function name(): ?string
    {
        return $this->plan->exists ? 'Edit Plan' : 'Create Plan';
    }

    public function description(): ?string
    {
        return 'Plan Information';
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
            Button::make('Delete')
                ->icon('bs.trash3')
                ->confirm('Are you sure you want to delete this plan?')
                ->method('remove')
                ->canSee($this->plan->exists),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(PlanEditLayout::class)
                ->title('Plan Information')
                ->description('Update plan information.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),
        ];
    }

    public function save(Plan $plan, Request $request)
    {
        $plan->fill($request->get('plan'))->save();
        Toast::info('Plan was saved.');

        return redirect()->route('platform.plans');
    }

    public function remove(Plan $plan)
    {
        $plan->delete();
        Toast::info('Plan was removed');

        return redirect()->route('platform.plans');
    }
}
