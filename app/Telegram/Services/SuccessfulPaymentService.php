<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class SuccessfulPaymentService
{
    public static function process(Update $update, Customer $customer): void
    {
        try {
            $successful_payment = $update->getMessage()->getSuccessfulPayment();

            if (! $successful_payment) {
                Log::error('Successful payment not found in update');

                return;
            }

            $payload = json_decode($successful_payment->getInvoicePayload(), true);

            $plan = Plan::find((int) $payload['plan_id']);

            $payment_method = PaymentMethod::find((int) $payload['payment_method_id']);

            if (! $plan) {
                Log::error("Plan not found with ID: {$plan->id}");

                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => '❌ Ошибка: план не найден. Обратитесь к администратору.',
                    'parse_mode' => 'HTML',
                ]);

                return;
            }

            $new_subscription = $customer->subscriptions()->create([
                'plan_id' => $plan->id,
                'date_start' => now()->startOfDay(),
                'date_end' => now()->endOfDay()->addDays($plan->period),
            ]);

            $customer->payments()->create([
                'subscription_id' => $customer->subscriptions()->latest()->first()->id,
                'amount' => $plan->stars,
                'currency' => 'XTR',
                'transaction_id' => $successful_payment->getTelegramPaymentChargeId(),
                'payment_method_id' => $payment_method->id,
            ]);

            $message = "🎉 <b>Подписка успешно активирована!</b>\n\n".
                "✅ Ваша подписка на план <b>{$plan->name}</b> активна до {$new_subscription->date_end}\n\n".
                "🔑 Теперь вы можете получить ключ VPN, используя команду:\n".
                "/key\n\n".
                'После получения ключа следуйте инструкциям по подключению к VPN серверу.';

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $ex) {
            Log::error('Error processing successful payment: '.$ex->getMessage());

            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => '❌ Произошла ошибка при обработке платежа. Обратитесь к администратору.',
                'parse_mode' => 'HTML',
            ]);
        }
    }
}
