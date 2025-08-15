<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Customer;

use App\Models\Subscription;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;

class CustomerSubscriptionsLayout extends Table
{
    /**
     * @var string
     */
    protected $target = 'subscriptions';

    /**
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('subscription_id', 'ID')->render(fn ($s) => $s->id),
            TD::make('plan_id', 'Plan')->render(function (Subscription $subscription) {
                return optional($subscription->plan)->title;
            }),
            TD::make('date_start', 'Start Date')->render(fn ($s) => $s->date_start),
            TD::make('date_end', 'End Date')->render(fn ($s) => $s->date_end),
            TD::make('created_at', 'Created At')->render(fn ($s) => $s->created_at),
            TD::make('actions', 'Actions')->render(function (Subscription $subscription) {
                return Button::make(__('Delete'))
                    ->icon('bs.trash3')
                    ->confirm(__('Are you sure you want to delete this subscription?'))
                    ->method('deleteSubscription')
                    ->novalidate()
                    ->parameters([
                        'subscription_id' => $subscription->id,
                    ]);
            }),
        ];
    }
}
