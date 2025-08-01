<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Models\TelegramCommandLog;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class PreCheckoutQueryService
{
    public static function process(Update $update, Customer $customer): void
    {
        try {
            $pre_checkout_query = $update->getPreCheckoutQuery();

            Telegram::answerPreCheckoutQuery([
                'pre_checkout_query_id' => $pre_checkout_query->getId(),
                'ok' => false,
                'error_message' => 'Нет возможности оплатить',
            ]);

            TelegramCommandLog::create([
                'customer_id' => $customer->id,
                'command_name' => 'человек оплатил',
                'action' => 'Пользователь оплатил, но нет возможности оплатить',
            ]);

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => 'Извините, сейчас оплата не доступна. Мы сообщим вам, когда всё заработает)',
                'parse_mode' => 'HTML',
            ]);

            return;

            if ($customer->hasActiveSubscription()) {
                $subscription = $customer->subscriptions()->latest()->first();

                $message = "✅ У вас уже есть активная подписка!\n\n".
                    "Дата окончания: {$subscription->date_end}\n\n".
                    'Если вы хотите продлить подписку, вы можете сделать это после окончания текущей.';

                $keyboard = [
                    [['text' => '❓ Помощь', 'callback_data' => 'help']],
                ];

                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard,
                    ]),
                ]);

                Telegram::answerPreCheckoutQuery([
                    'pre_checkout_query_id' => $pre_checkout_query->getId(),
                    'ok' => false,
                    'error_message' => 'У вас есть активная подписка',
                ]);

                return;
            }

            Telegram::answerPreCheckoutQuery([
                'pre_checkout_query_id' => $pre_checkout_query->getId(),
                'ok' => true,
            ]);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '❌ Произошла ошибка при обработке платежа. Обратитесь к администратору.',
                'parse_mode' => 'HTML',
            ]);

            return;
        }
    }
}
