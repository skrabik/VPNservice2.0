<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Models\SupportTicket;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class SupportTicketService
{
    public static function process(Update $update, Customer $customer): void
    {
        $message = $update->getMessage()->getText();

        SupportTicket::create([
            'customer_id' => $customer->id,
            'message' => $message,
        ]);

        Telegram::sendMessage([
            'chat_id' => $customer->telegram_id,
            'text' => 'Ваш тикет создан. Мы скоро свяжемся с вами.',
            'reply_markup' => json_encode([
                'keyboard' => [
                    ['Назад'],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
