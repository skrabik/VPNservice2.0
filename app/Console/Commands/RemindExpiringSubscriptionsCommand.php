<?php

namespace App\Console\Commands;

use App\Jobs\SendSubscriptionExpiryReminderJob;
use App\Models\Subscription;
use Illuminate\Console\Command;

class RemindExpiringSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:remind-expiring';

    protected $description = 'Queue Telegram reminders for subscriptions ending tomorrow';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $query = Subscription::query()
            ->whereNull('expiry_reminder_sent_at')
            ->whereNotNull('date_end')
            ->where('date_end', '>', now())
            ->whereDate('date_end', $tomorrow)
            ->whereHas('customer', function ($q) {
                $q->whereNotNull('telegram_id')
                    ->whereRaw("TRIM(telegram_id) <> ''");
            });

        $count = 0;

        $query->orderBy('id')->chunkById(100, function ($subscriptions) use (&$count) {
            foreach ($subscriptions as $subscription) {
                SendSubscriptionExpiryReminderJob::dispatch($subscription->id);
                $count++;
            }
        });

        $this->info("Dispatched {$count} reminder job(s) for date_end on {$tomorrow}.");

        return self::SUCCESS;
    }
}
