<?php

namespace App\Telegram\Commands;

use App\Models\Plan;
use App\Models\TelegramCommandLog;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Telegram\Helpers\SendTelegramInvoicePaymentService;

class PayCommand extends BaseCommand
{
    public function handle(): void
    {
        Log::info('PayCommand handle started', [
            'customer_id' => $this->customer->id,
            'telegram_id' => $this->customer->telegram_id,
            'params' => $this->params,
            'has_callback_query' => (bool) $this->update->getCallbackQuery(),
        ]);

        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /pay',
            'action' => 'Вызвал команду /pay',
        ]);

        if ($this->customer->hasActiveSubscription()) {
            $subscription = $this->customer->subscriptions()->latest()->first();

            Log::info('PayCommand aborted because subscription is already active', [
                'customer_id' => $this->customer->id,
                'subscription_id' => $subscription?->id,
                'date_end' => $subscription?->date_end?->toDateTimeString(),
            ]);

            $message = "✅ У вас уже есть активная подписка!\n\n".
                "Дата окончания: {$subscription->date_end}\n\n".
                'Если вы хотите продлить подписку, вы можете сделать это после окончания текущей.';

            $keyboard = [
                [['text' => '❓ Помощь', 'callback_data' => 'help']],
            ];

            Log::info('PayCommand test mode: skipped Telegram::sendMessage for active subscription notice', [
                'customer_id' => $this->customer->id,
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
            ]);

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);

            return;
        }

        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'процессим платеж',
            'action' => 'process_payment',
        ]);

        $plan = Plan::resolveOrCreateDefaultMonthlyPlan();

        Log::info('PayCommand resolved monthly plan', [
            'customer_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'plan_period' => $plan->period,
            'plan_stars' => $plan->stars,
        ]);

        $this->processPayment($plan);
    }

    private function processPayment(Plan $plan): void
    {
        if ($this->update->getCallbackQuery()) {
            $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

            Log::info('PayCommand test mode: skipped Telegram::deleteMessage before invoice', [
                'customer_id' => $this->customer->id,
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);

            Telegram::deleteMessage([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);
        }

        SendTelegramInvoicePaymentService::sendInvoice($this->customer->telegram_id, $plan);
    }
}
