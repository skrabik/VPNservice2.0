<?php

namespace App\Telegram\Commands;

use App\Models\PaymentMethod;
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

        if (isset($this->params['payment_method_id']) && isset($this->params['plan_id'])) {
            TelegramCommandLog::create([
                'customer_id' => $this->customer->id,
                'command_name' => 'процессим платеж',
                'action' => 'process_payment',
            ]);

            $this->processPayment($this->params['plan_id'], $this->params['payment_method_id']);

            return;
        }

        if (isset($this->params['plan_id'])) {
            TelegramCommandLog::create([
                'customer_id' => $this->customer->id,
                'command_name' => 'показываем платёжные методы',
                'action' => 'show_payment_methods',
            ]);

            $this->showPaymentMethods($this->params['plan_id']);

            return;
        }

        $this->showPlans();
    }

    private function showPlans(): void
    {
        $plans = Plan::where('active', true)->get();

        if ($plans->isEmpty()) {
            $message = "❌ Нет доступных тарифов.\n\n".
                'Пожалуйста, попробуйте позже или обратитесь к администратору.';

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $message = "💳 Выберите тарифный план:\n\n";

        $keyboard = [];
        foreach ($plans as $plan) {
            $keyboard[] = [
                [
                    'text' => "{$plan->title} {$plan->stars}🌟",
                    'callback_data' => "/pay?plan_id={$plan->id}",
                ],
            ];
        }

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'start']];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function showPaymentMethods(int $plan_id): void
    {
        $plan = Plan::find($plan_id);

        if (! $plan) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Тарифный план не найден.',
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $payment_methods = PaymentMethod::where('active', true)->get();

        if ($payment_methods->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => "❌ Нет доступных методов оплаты.\n\nПожалуйста, попробуйте позже или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $message = "💳 Выберите способ оплаты для тарифа <b>{$plan->title}</b>\n\n".
            "💰 Сумма к оплате: <b>{$plan->price}₽ ({$plan->stars}🌟)</b>\n\n";

        $keyboard = [];
        foreach ($payment_methods as $method) {
            $keyboard[] = [
                [
                    'text' => "💳 {$method->title}",
                    'callback_data' => "/pay?plan_id={$plan_id}&payment_method_id={$method->id}",
                ],
            ];
        }

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => '/plan']];

        if ($this->update->getCallbackQuery()) {
            $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();
            Telegram::deleteMessage([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);
        }

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function processPayment(int $plan_id, int $payment_method_id): void
    {
        $plan = Plan::find($plan_id);
        $payment_method = PaymentMethod::find($payment_method_id);

        if (! $plan || ! $payment_method) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Ошибка: тариф или метод оплаты не найден.',
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
            'description' => 'Подписка: '.$plan->description.' на '.$plan->period.' дней',
            'payload' => json_encode([
                'plan_id' => $plan->id,
                'payment_method_id' => $payment_method->id,
            ]),
            'currency' => 'XTR',
            'prices' => [
                ['label' => $plan->title, 'amount' => $plan->stars],
            ],
        ]);
    }
}
