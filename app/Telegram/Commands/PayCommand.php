<?php

namespace App\Telegram\Commands;

use App\Models\Plan;
use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;

class PayCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /pay',
            'action' => 'Вызвал команду /pay',
        ]);

        if ($this->customer->hasActiveSubscription()) {
            $subscription = $this->customer->subscriptions()->latest()->first();

            $message = "✅ У вас уже есть активная подписка!\n\n".
                "Дата окончания: {$subscription->date_end}\n\n".
                'Если вы хотите продлить подписку, вы можете сделать это после окончания текущей.';

            $keyboard = [
                [['text' => '❓ Помощь', 'callback_data' => 'help']],
            ];

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

        $plan = $this->resolveDefaultMonthlyPlan();

        if (! $plan) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Ошибка: месячный тариф не найден.',
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $this->processPayment($plan->id);
    }

    private function resolveDefaultMonthlyPlan(): ?Plan
    {
        return Plan::query()
            ->where('active', true)
            ->where('period', 30)
            ->orderBy('id')
            ->first();
    }

    private function processPayment(int $plan_id): void
    {
        $plan = Plan::find($plan_id);

        if (! $plan) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Ошибка: тариф не найден.',
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        if ($this->update->getCallbackQuery()) {
            $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();
            Telegram::deleteMessage([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);
        }

        // отправляем транзакцию
        Telegram::sendInvoice([
            'chat_id' => $this->customer->telegram_id,
            'title' => 'Тариф: '.$plan->title,
            'description' => 'Подписка через Telegram Stars: '.$plan->description.' на '.$plan->period.' дней',
            'payload' => json_encode([
                'plan_id' => $plan->id,
            ]),
            'currency' => 'XTR',
            'prices' => [
                ['label' => $plan->title, 'amount' => $plan->stars],
            ],
        ]);
    }
}
