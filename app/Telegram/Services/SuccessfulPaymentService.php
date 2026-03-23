<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payments\FinalizeSubscriptionPaymentService;
use App\Telegram\Helpers\SendTelegramInvoicePaymentService;
use Illuminate\Support\Facades\Cache;
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

            $planId = (int) ($payload['plan_id'] ?? 0);
            $plan = $planId > 0
                ? Plan::find($planId)
                : null;

            $plan ??= Plan::resolveOrCreateDefaultMonthlyPlan();

            if (! $plan) {
                Log::error('Plan not found while processing payment', [
                    'plan_id' => $payload['plan_id'] ?? null,
                ]);

                Telegram::sendMessage([
                    'chat_id' => $customer->telegram_id,
                    'text' => '❌ Ошибка: план не найден. Обратитесь к администратору.',
                    'parse_mode' => 'HTML',
                ]);

                return;
            }

            FinalizeSubscriptionPaymentService::process($customer, $plan, [
                'amount' => $plan->stars,
                'currency' => 'XTR',
                'transaction_id' => $successful_payment->getTelegramPaymentChargeId(),
                'provider' => Payment::PROVIDER_TELEGRAM,
                'payment_method' => Payment::METHOD_TELEGRAM_STARS,
                'status' => Payment::STATUS_SUCCEEDED,
                'external_payment_id' => $successful_payment->getTelegramPaymentChargeId(),
                'payload' => [
                    'provider_payment_charge_id' => $successful_payment->getProviderPaymentChargeId(),
                    'telegram_payment_charge_id' => $successful_payment->getTelegramPaymentChargeId(),
                    'invoice_payload' => $payload,
                ],
            ]);

            $invoiceMessageCacheKey = SendTelegramInvoicePaymentService::getInvoiceMessageCacheKey((string) $customer->telegram_id);
            $invoiceMessageId = Cache::pull($invoiceMessageCacheKey);

            if ($invoiceMessageId) {
                try {
                    Telegram::deleteMessage([
                        'chat_id' => $customer->telegram_id,
                        'message_id' => $invoiceMessageId,
                    ]);
                } catch (\Throwable $exception) {
                    Log::warning('Failed to delete Telegram invoice message after successful payment', [
                        'chat_id' => $customer->telegram_id,
                        'message_id' => $invoiceMessageId,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

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
