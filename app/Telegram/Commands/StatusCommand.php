<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Laravel\Facades\Telegram;

class StatusCommand extends BaseCommand
{
    public function handle(): void
    {
        $subscription = $this->customer->subscriptions()->latest()->first();

        if (! $subscription || $subscription->date_end < now()) {
            $message = "❌ У вас нет активной подписки!\n\n".
                "Для использования VPN необходимо оформить подписку.\n\n".
                'Нажмите кнопку ниже, чтобы перейти к оплате.';

            $keyboard = [
                [['text' => '💳 Оплатить подписку', 'callback_data' => '/pay']],
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

            return;
        }

        $daysLeft = now()->diffInDays($subscription->date_end, false);
        $hoursLeft = now()->diffInHours($subscription->date_end, false) % 24;

        $statusIcon = $daysLeft > 7 ? '✅' : ($daysLeft > 3 ? '⚠️' : '🔴');
        $statusText = $daysLeft > 7 ? 'Активна' : ($daysLeft > 3 ? 'Заканчивается' : 'Истекает');

        $message = "📊 <b>Статус подписки</b>\n\n".
            "{$statusIcon} Статус: <b>{$statusText}</b>\n".
            "📋 Тариф: <b>{$subscription->plan->title}</b>\n".
            "💰 Стоимость: <b>{$subscription->plan->price}₽</b>\n".
            "📅 Дата начала: <b>{$subscription->date_start->format('d.m.Y')}</b>\n".
            "📅 Дата окончания: <b>{$subscription->date_end->format('d.m.Y H:i')}</b>\n\n";

        if ($daysLeft > 0) {
            $message .= "⏰ Осталось: <b>{$daysLeft} дн. {$hoursLeft} ч.</b>\n\n";
        } else {
            $message .= "⏰ Подписка истекла\n\n";
        }

        $activeKeys = $this->customer->activeVpnKeys()->count();
        $message .= "🔑 Активных ключей VPN: <b>{$activeKeys}</b>\n\n";

        if ($daysLeft > 0) {
            $message .= '💡 Вы можете получить ключи VPN или продлить подписку.';
        } else {
            $message .= '💡 Для продолжения работы необходимо продлить подписку.';
        }

        $keyboard = [];

        if ($daysLeft > 0) {
            $keyboard[] = [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']];
        }

        if ($daysLeft <= 7) {
            $keyboard[] = [['text' => '💳 Продлить подписку', 'callback_data' => '/pay']];
        }

        $keyboard[] = [['text' => '🏠 Главное меню', 'callback_data' => 'start']];

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
