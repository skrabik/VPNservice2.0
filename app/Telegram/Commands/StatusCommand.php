<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use Telegram\Bot\Laravel\Facades\Telegram;

class StatusCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /status',
            'action' => 'Вызвал команду /status',
        ]);

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

        $days_left = now()->diffInDays($subscription->date_end, false);
        $hours_left = now()->diffInHours($subscription->date_end, false) % 24;

        $days_left = round($days_left);

        $status_icon = $days_left > 7 ? '✅' : ($days_left > 3 ? '⚠️' : '🔴');
        $status_text = $days_left > 7 ? 'Активна' : ($days_left > 3 ? 'Заканчивается' : 'Истекает');

        $message = "📊 <b>Статус подписки</b>\n\n".
            "{$status_icon} Статус: <b>{$status_text}</b>\n".
            "📋 Тариф: <b>{$subscription->plan->title}</b>\n".
            "💰 Стоимость: <b>{$subscription->plan->price}₽</b>\n".
            "📅 Дата начала: <b>{$subscription->date_start->format('d.m.Y')}</b>\n".
            "📅 Дата окончания: <b>{$subscription->date_end->format('d.m.Y H:i')}</b>\n\n";

        if ($days_left > 0) {
            $message .= "⏰ Осталось: <b>{$days_left} дн. {$hours_left} ч.</b>\n\n";
        } else {
            $message .= "⏰ Подписка истекла\n\n";
        }

        $active_keys = $this->customer->activeVpnKeys()->count();
        $message .= "🔑 Активных ключей VPN: <b>{$active_keys}</b>\n\n";

        if ($days_left > 0) {
            $message .= '💡 Вы можете получить ключи VPN или продлить подписку.';
        } else {
            $message .= '💡 Для продолжения работы необходимо продлить подписку.';
        }

        $keyboard = [];

        if ($days_left > 0 && $active_keys > 0) {
            $keyboard[] = [['text' => '🔐 Мой ключ VPN', 'callback_data' => '/key?mode=view_current']];
        }

        if ($days_left > 0) {
            $keyboard[] = [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']];
        }

        if ($days_left <= 7) {
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
