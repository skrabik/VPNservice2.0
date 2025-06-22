<?php

namespace App\Telegram\Commands;

use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use Telegram\Bot\Laravel\Facades\Telegram;

class PayCommand extends BaseCommand
{
    public function handle(): void
    {
        if (isset($this->params['payment_method_id']) && isset($this->params['plan_id'])) {
            $this->processPayment($this->params['plan_id'], $this->params['payment_method_id']);

            return;
        }

        if (isset($this->params['plan_id'])) {
            $this->showPaymentMethods($this->params['plan_id']);

            return;
        }

        $this->showPlans();
    }

    private function showPlans(): void
    {
        $plans = Plan::all();

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
                    'text' => "{$plan->title} - {$plan->price}₽",
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

        $paymentMethods = PaymentMethod::where('active', true)->get();

        if ($paymentMethods->isEmpty()) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => "❌ Нет доступных методов оплаты.\n\nПожалуйста, попробуйте позже или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $message = "💳 Выберите способ оплаты для тарифа <b>{$plan->title}</b>\n\n".
            "💰 Сумма к оплате: <b>{$plan->price}₽</b>\n\n";

        $keyboard = [];
        foreach ($paymentMethods as $method) {
            $keyboard[] = [
                [
                    'text' => "💳 {$method->title}",
                    'callback_data' => "/pay?plan_id={$plan_id}&payment_method_id={$method->id}",
                ],
            ];
        }

        $keyboard[] = [['text' => '⬅️ Назад к тарифам', 'callback_data' => '/plan']];

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
        $paymentMethod = PaymentMethod::find($payment_method_id);

        if (! $plan || ! $paymentMethod) {
            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Ошибка: тариф или метод оплаты не найден.',
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        Subscription::create([
            'customer_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'date_start' => now()->startOfDay(),
            'date_end' => now()->addDays($plan->period)->endOfDay(),
        ]);

        $message = "✅ Оплата прошла успешно!\n\n".
            "📋 Тариф: <b>{$plan->title}</b>\n".
            "💰 Сумма: <b>{$plan->price}₽</b>\n".
            "💳 Способ: <b>{$paymentMethod->title}</b>\n\n".
            '🔑 Теперь вы можете получить ключи VPN!';

        $keyboard = [
            [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']],
            [['text' => '🏠 Главное меню', 'callback_data' => 'start']],
        ];

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
}
