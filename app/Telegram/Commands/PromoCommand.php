<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class PromoCommand extends BaseCommand
{
    public function __construct(Update $update, Customer $customer, array $params)
    {
        parent::__construct($update, $customer, $params);
    }

    public function handle(): void
    {
        $message = trim($this->update->getMessage()->getText());

        if ($message === '/promo' || $message === '🎁 Ввести промокод') {

            $text = "🎁 <b>Введите промокод</b>\n\n".
                "Если у вас есть промокод, введите его ниже для получения бесплатного VPN на неделю.\n\n".
                '💡 Промокоды можно использовать многократно.';

            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => 'start']],
            ];

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ]);

            return;
        }

        $this->processPromoCode($message);
    }

    private function processPromoCode(string $code): void
    {
        $promo_codes = $this->getPromoCodes();

        if (empty($promo_codes)) {
            $this->sendError("❌ Система промокодов временно недоступна.\n\nПожалуйста, попробуйте позже.");

            return;
        }

        if (! in_array($code, $promo_codes)) {
            $this->sendError("❌ Неверный промокод!\n\nПроверьте правильность ввода и попробуйте снова.");

            return;
        }

        if ($this->customer->hasActiveSubscription()) {
            $this->sendError("❌ У вас уже есть активная подписка!\n\nПромокод можно использовать только при отсутствии активной подписки.");

            return;
        }

        $this->createPromoSubscription();
    }

    private function createPromoSubscription(): void
    {
        $promo_plan = Plan::where('slug', 'promo')->first();

        if (! $promo_plan) {
            $this->sendError("❌ План для промокода не найден.\n\nПожалуйста, обратитесь к администратору.");

            return;
        }

        $subscription = Subscription::create([
            'customer_id' => $this->customer->id,
            'plan_id' => $promo_plan->id,
            'date_start' => now(),
            'date_end' => now()->addDays($promo_plan->period),
        ]);

        $message = "🎉 <b>Промокод активирован!</b>\n\n".
            "✅ Вам предоставлен бесплатный VPN на <b>7 дней</b>\n".
            "📅 Дата окончания: <b>{$subscription->date_end->format('d.m.Y H:i')}</b>\n\n".
            "🔑 Теперь вы можете получить ключ VPN, используя команду:\n".
            "/key\n\n".
            '💡 После окончания бесплатного периода вы можете оформить платную подписку.';

        $keyboard = [
            [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']],
            [['text' => '📊 Статус подписки', 'callback_data' => '/status']],
            [['text' => '🏠 Главное меню', 'callback_data' => 'start']],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function getPromoCodes(): array
    {
        $promo_codes_json = env('TELEGRAM_PROMO_CODES', '[]');
        $promo_codes = json_decode($promo_codes_json, true);

        return is_array($promo_codes) ? $promo_codes : [];
    }

    private function sendError(string $message): void
    {
        $keyboard = [
            [['text' => '🔄 Попробовать снова', 'callback_data' => '/promo']],
            [['text' => '⬅️ Назад', 'callback_data' => 'start']],
        ];

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
