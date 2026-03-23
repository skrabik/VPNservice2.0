<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Subscription;

class CustomerStatusService
{
    public function getOverview(Customer $customer): array
    {
        $subscription = $customer->subscriptions()
            ->with('plan')
            ->latest('date_end')
            ->first();

        $hasActiveSubscription = $subscription instanceof Subscription
            && $subscription->date_end
            && $subscription->date_end->isFuture();

        $daysLeft = $hasActiveSubscription
            ? max(0, (int) now()->diffInDays($subscription->date_end, false))
            : 0;

        $hoursLeft = $hasActiveSubscription
            ? max(0, (int) (now()->diffInHours($subscription->date_end, false) % 24))
            : 0;

        $statusIcon = $daysLeft > 7 ? '✅' : ($daysLeft > 3 ? '⚠️' : '🔴');
        $statusText = $daysLeft > 7 ? 'Активна' : ($daysLeft > 3 ? 'Заканчивается' : 'Истекает');

        if (! $hasActiveSubscription) {
            $statusIcon = '❌';
            $statusText = 'Нет активной подписки';
        }

        return [
            'subscription' => $subscription,
            'has_active_subscription' => $hasActiveSubscription,
            'days_left' => $daysLeft,
            'hours_left' => $hoursLeft,
            'status_icon' => $statusIcon,
            'status_text' => $statusText,
            'active_keys_count' => $customer->activeVpnKeys()->count(),
            'current_key' => $customer->activeVpnKeys()->with('server')->latest('id')->first(),
        ];
    }
}
