<?php

namespace App\Telegram\Commands;

use App\Models\Plan;
use Telegram\Bot\Laravel\Facades\Telegram;

class PlanCommand extends BaseCommand
{
    public function handle(): void
    {
        if ($this->customer->hasActiveSubscription()) {
            $message = "✅ У вас уже есть активная подписка!\n\n".
                "Дата окончания: {$this->customer->subscription_end_date}\n\n".
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
