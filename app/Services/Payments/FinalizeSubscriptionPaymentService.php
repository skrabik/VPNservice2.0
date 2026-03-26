<?php

namespace App\Services\Payments;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Plan;
use App\Telegram\TelegramKeyboard;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class FinalizeSubscriptionPaymentService
{
    public static function process(Customer $customer, Plan $plan, array $paymentAttributes): void
    {
        $provider = $paymentAttributes['provider'] ?? null;
        $externalPaymentId = $paymentAttributes['external_payment_id'] ?? null;

        if (! $provider || ! $externalPaymentId) {
            throw new \InvalidArgumentException('Payment provider and external payment id are required.');
        }

        $payment = Payment::query()
            ->where('provider', $provider)
            ->where('external_payment_id', $externalPaymentId)
            ->first();

        if ($payment?->subscription_id) {
            Log::info('Skipping duplicated payment finalization', [
                'provider' => $provider,
                'external_payment_id' => $externalPaymentId,
                'payment_id' => $payment->id,
                'subscription_id' => $payment->subscription_id,
            ]);

            return;
        }

        $activeSubscription = $customer->getActiveSubscription();
        $wasExtended = $activeSubscription !== null;

        if ($activeSubscription) {
            $activeSubscription->plan_id = $plan->id;
            $activeSubscription->date_end = $activeSubscription->date_end->copy()->addDays($plan->period);
            $activeSubscription->expiry_reminder_sent_at = null;
            $activeSubscription->save();

            $subscription = $activeSubscription->fresh();
        } else {
            $subscription = $customer->subscriptions()->create([
                'plan_id' => $plan->id,
                'date_start' => now()->startOfDay(),
                'date_end' => now()->endOfDay()->addDays($plan->period),
            ]);
        }

        $paymentData = [
            'subscription_id' => $subscription->id,
            'amount' => $paymentAttributes['amount'],
            'currency' => $paymentAttributes['currency'] ?? null,
            'transaction_id' => $paymentAttributes['transaction_id'] ?? $externalPaymentId,
            'provider' => $provider,
            'payment_method' => $paymentAttributes['payment_method'] ?? null,
            'status' => $paymentAttributes['status'] ?? Payment::STATUS_SUCCEEDED,
            'external_payment_id' => $externalPaymentId,
            'payload' => $paymentAttributes['payload'] ?? null,
        ];

        if ($payment) {
            $payment->update($paymentData);
        } else {
            $customer->payments()->create($paymentData);
        }

        self::sendSuccessMessage($customer, $plan, $subscription->date_end?->format('d.m.Y H:i'), $wasExtended);
    }

    public static function sendSuccessMessage(Customer $customer, Plan $plan, ?string $dateEnd, bool $wasExtended = false): void
    {
        if (! $customer->telegram_id) {
            return;
        }

        $message = $wasExtended
            ? "🎉 <b>Подписка успешно продлена!</b>\n\n".
                "✅ Подписка на план <b>{$plan->title}</b> теперь активна до {$dateEnd}\n\n".
                "🔑 При необходимости вы можете перевыпустить VPN-ключ:\n"
            : "🎉 <b>Подписка успешно активирована!</b>\n\n".
                "✅ Ваша подписка на план <b>{$plan->title}</b> активна до {$dateEnd}\n\n".
                "🔑 Теперь вы можете получить ключ VPN, используя команду:\n".
                "/key\n\n".
                'После получения ключа следуйте инструкциям по подключению к VPN серверу.';

        try {
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => TelegramKeyboard::inline([
                    [['text' => '🔑 Получить ключ', 'callback_data' => '/key']],
                    [['text' => '⬅️ Назад', 'callback_data' => '/start']],
                ]),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send Telegram success payment message', [
                'customer_id' => $customer->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
