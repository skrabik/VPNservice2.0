<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class PayCommand extends BaseCommand
{
    public function handle(): void
    {
        // Проверяем, есть ли уже активная подписка
        if ($this->customer->hasActiveSubscription()) {
            $message = "✅ У вас уже есть активная подписка!\n\n".
                      "Дата окончания: {$this->customer->subscription_end_date}\n\n".
                      'Если вы хотите продлить подписку, вы можете сделать это после окончания текущей.';

            $keyboard = [
                ['❓ Помощь'],
            ];

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]),
            ]);

            return;
        }

        // Если подписки нет, показываем варианты оплаты
        $message = "💳 Выберите тарифный план:\n\n".
                  "1️⃣ Месяц - 299₽\n".
                  "2️⃣ 3 месяца - 799₽\n".
                  "3️⃣ Год - 2499₽\n\n".
                  'Выберите номер тарифа для оплаты.';

        $keyboard = [
            ['1️⃣ Месяц', '2️⃣ 3 месяца'],
            ['3️⃣ Год'],
            ['❓ Помощь'],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
