<?php 

namespace App\Telegram\Helpers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendTelegramInvoicePaymentService
{
    public static function sendInvoice(string $customer_telegram_id, Plan $plan): void
    {
        $invoicePayload = [
            'chat_id' => $customer_telegram_id,
            'title' => 'Тариф: '.$plan->title,
            'description' => 'Подписка через Telegram Stars: '.$plan->description.' на '.$plan->period.' дней',
            'payload' => json_encode([
                'plan_id' => $plan->id,
            ]),
            'currency' => 'XTR',
            'prices' => [
                ['label' => $plan->title, 'amount' => $plan->stars],
            ],
        ];

        Log::info('SendTelegramInvoicePaymentService: sending invoice', [
            'invoice_payload' => $invoicePayload,
        ]);
    
        $response = Telegram::sendInvoice($invoicePayload);

        $messageId = method_exists($response, 'getMessageId')
            ? $response->getMessageId()
            : data_get($response, 'message_id');

        if ($messageId) {
            Cache::put(
                self::getInvoiceMessageCacheKey($customer_telegram_id),
                $messageId,
                now()->addHour()
            );
        }

        Log::info('SendTelegramInvoicePaymentService: invoice sent response', ['response' => $response]);
    }

    public static function getInvoiceMessageCacheKey(string $customer_telegram_id): string
    {
        return 'telegram_invoice_message_'.$customer_telegram_id;
    }
}