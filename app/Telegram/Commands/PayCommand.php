<?php

namespace App\Telegram\Commands;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\TelegramCommandLog;
use App\Services\Payments\YooKassaPaymentService;
use App\Telegram\Helpers\SendTelegramInvoicePaymentService;
use App\Telegram\TelegramKeyboard;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class PayCommand extends BaseCommand
{
    public function handle(): void
    {
        Log::info('PayCommand handle started', [
            'customer_id' => $this->customer->id,
            'telegram_id' => $this->customer->telegram_id,
            'params' => $this->params,
            'has_callback_query' => (bool) $this->update->getCallbackQuery(),
        ]);

        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /pay',
            'action' => 'Вызвал команду /pay',
        ]);

        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'процессим платеж',
            'action' => 'process_payment',
        ]);

        $plan = Plan::resolveOrCreateDefaultMonthlyPlan();

        Log::info('PayCommand resolved monthly plan', [
            'customer_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'plan_period' => $plan->period,
            'plan_stars' => $plan->stars,
        ]);

        $activeSubscription = $this->customer->getActiveSubscription();

        if ($activeSubscription) {
            $message = "⚠️ У вас уже есть активная подписка до <b>{$activeSubscription->date_end->format('d.m.Y H:i')}</b>.\n\n".
                "Новая оплата не создаст вторую отдельную подписку, а <b>продлит текущую</b> ещё на {$plan->period} дней.";

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        }

        $method = $this->params['method'] ?? null;

        if (! is_string($method) || $method === '') {
            $this->showPaymentMethodSelection($plan);

            return;
        }

        match ($method) {
            'stars' => $this->processTelegramStarsPayment($plan),
            'yookassa' => $this->processYooKassaPayment($plan),
            default => $this->showPaymentMethodSelection($plan, '❌ Не удалось определить способ оплаты. Выберите вариант ниже.'),
        };
    }

    private function showPaymentMethodSelection(Plan $plan, ?string $prefixMessage = null): void
    {
        $this->deleteCallbackMessageIfExists();

        $message = $prefixMessage ? $prefixMessage."\n\n" : '';
        $message .= "Выберите способ оплаты для тарифа <b>{$plan->title}</b>.\n\n".
            "⭐ Telegram Stars: мгновенная оплата внутри Telegram.\n".
            "💳 ЮKassa: оплата на защищенной странице ЮKassa.";

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => TelegramKeyboard::inline(TelegramKeyboard::paymentMethods()),
        ]);
    }

    private function processTelegramStarsPayment(Plan $plan): void
    {
        $this->deleteCallbackMessageIfExists();

        SendTelegramInvoicePaymentService::sendInvoice($this->customer->telegram_id, $plan);
    }

    private function processYooKassaPayment(Plan $plan): void
    {
        $this->deleteCallbackMessageIfExists();

        try {
            $payment = app(YooKassaPaymentService::class)->createHostedPayment($this->customer, $plan);
            $paymentId = (string) ($payment['id'] ?? '');
            $confirmationUrl = (string) data_get($payment, 'confirmation.confirmation_url', '');

            if ($paymentId === '' || $confirmationUrl === '') {
                throw new \RuntimeException('YooKassa payment response is incomplete.');
            }

            Payment::query()->updateOrCreate(
                [
                    'provider' => Payment::PROVIDER_YOOKASSA,
                    'external_payment_id' => $paymentId,
                ],
                [
                    'customer_id' => $this->customer->id,
                    'subscription_id' => null,
                    'amount' => data_get($payment, 'amount.value', $plan->price),
                    'currency' => data_get($payment, 'amount.currency', 'RUB'),
                    'transaction_id' => $paymentId,
                    'payment_method' => (string) data_get($payment, 'payment_method.type', Payment::METHOD_YOOKASSA_REDIRECT),
                    'status' => (string) data_get($payment, 'status', Payment::STATUS_PENDING),
                    'payload' => $payment,
                ]
            );

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => "💳 <b>Оплата через ЮKassa</b>\n\n".
                    "Тариф: <b>{$plan->title}</b>\n".
                    "Сумма: <b>{$plan->price} RUB</b>\n\n".
                    'Нажмите на кнопку ниже, чтобы перейти к оплате. После подтверждения платежа подписка активируется автоматически.',
                'parse_mode' => 'HTML',
                'reply_markup' => TelegramKeyboard::inline([
                    [
                        ['text' => 'Перейти к оплате', 'url' => $confirmationUrl],
                    ],
                    [
                        ['text' => '⭐ Оплатить Stars', 'callback_data' => '/pay?method=stars'],
                    ],
                    TelegramKeyboard::backToMainMenu('🏠 Главное меню')[0],
                ]),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to create YooKassa payment', [
                'customer_id' => $this->customer->id,
                'plan_id' => $plan->id,
                'message' => $exception->getMessage(),
            ]);

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => '❌ Не удалось создать ссылку на оплату через ЮKassa. Попробуйте позже или выберите Telegram Stars.',
                'parse_mode' => 'HTML',
                'reply_markup' => TelegramKeyboard::inline(TelegramKeyboard::paymentMethods()),
            ]);
        }
    }

    private function deleteCallbackMessageIfExists(): void
    {
        if (! $this->update->getCallbackQuery()) {
            return;
        }

        $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

        Log::info('PayCommand deleting callback source message', [
            'customer_id' => $this->customer->id,
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
        ]);

        try {
            Telegram::deleteMessage([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('PayCommand failed to delete callback source message', [
                'customer_id' => $this->customer->id,
                'message_id' => $message_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
