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

            TelegramCommandLog::create([
                'customer_id' => $customer->id,
                'command_name' => 'pre_checkout_query',
                'action' => 'Пользователь начал оплату подписки',
            ]);

            Log::info('Pre-checkout approved for subscription payment', [
                'customer_id' => $customer->id,
                'telegram_id' => $customer->telegram_id,
                'has_active_subscription' => $customer->hasActiveSubscription(),
            ]);

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
