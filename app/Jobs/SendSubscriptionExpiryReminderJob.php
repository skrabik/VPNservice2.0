<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Telegram\TelegramKeyboard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendSubscriptionExpiryReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $subscriptionId
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('subscription:expiry-reminder:'.$this->subscriptionId, 120);

        if (! $lock->get()) {
            return;
        }

        try {
            $subscription = Subscription::query()
                ->with(['customer', 'plan'])
                ->find($this->subscriptionId);

            if (! $subscription || ! $subscription->customer || ! $subscription->plan) {
                return;
            }

            $customer = $subscription->customer;
            $telegramId = $customer->telegram_id;

            if ($telegramId === null || $telegramId === '') {
                return;
            }

            if ($subscription->expiry_reminder_sent_at !== null) {
                return;
            }

            if ($subscription->date_end === null || ! $subscription->date_end->isFuture()) {
                return;
            }

            $tomorrow = now()->addDay()->toDateString();
            if ($subscription->date_end->toDateString() !== $tomorrow) {
                return;
            }

            $dateEnd = $subscription->date_end->format('d.m.Y H:i');
            $planTitle = $subscription->plan->title;
            $message = "⏳ <b>Подписка скоро закончится</b>\n\n".
                "📅 План <b>{$planTitle}</b> действует до <b>{$dateEnd}</b>.\n\n".
                'Чтобы не потерять доступ, продлите подписку заранее:';

            Telegram::sendMessage([
                'chat_id' => $telegramId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => TelegramKeyboard::inline([
                    [['text' => '💳 Продлить подписку', 'callback_data' => '/pay']],
                    [['text' => '⬅️ Назад', 'callback_data' => '/start']],
                ]),
            ]);

            $subscription->forceFill(['expiry_reminder_sent_at' => now()])->save();
        } catch (\Throwable $exception) {
            Log::warning('Failed to send subscription expiry reminder', [
                'subscription_id' => $this->subscriptionId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $lock->release();
        }
    }
}
