<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Plan;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;

class PlanEditLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('plan.title')
                ->type('text')
                ->max(256)
                ->required()
                ->title('Title')
                ->placeholder('Enter plan title'),

            Input::make('plan.slug')
                ->type('text')
                ->max(256)
                ->required()
                ->title('Slug')
                ->placeholder('Enter slug'),

            Input::make('plan.description')
                ->type('text')
                ->max(1024)
                ->title('Description')
                ->placeholder('Enter description'),

            Input::make('plan.stars')
                ->type('inteteger')
                ->reuquired()
                ->title('Stars'),

            Input::make('plan.price')
                ->type('number')
                ->step('0.01')
                ->required()
                ->title('Price'),

            Input::make('plan.period')
                ->type('number')
                ->required()
                ->title('Period (days)'),

            CheckBox::make('plan.active')
                ->sendTrueOrFalse()
                ->checked()
                ->title('Active'),
        ];
    }
}
