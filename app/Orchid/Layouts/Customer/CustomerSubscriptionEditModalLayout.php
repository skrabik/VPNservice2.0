<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Customer;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Rows;

class CustomerSubscriptionEditModalLayout extends Rows
{
    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            DateTimer::make('subscription.date_start')
                ->title(__('Start date'))
                ->enableTime()
                ->format24hr()
                ->required()
                ->static(),

            DateTimer::make('subscription.date_end')
                ->title(__('End date'))
                ->enableTime()
                ->format24hr()
                ->required()
                ->static(),
        ];
    }
}
