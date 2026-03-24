<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Models\SupportTicket;
use App\Services\CustomerSupportService;
use App\Telegram\TelegramKeyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class SupportTicketService
{
    public static function process(Update $update, Customer $customer): void
    {
        $message = $update->getMessage()->getText();

        $ticket = (new CustomerSupportService)->createTicket($customer, $message, SupportTicket::CHANNEL_TELEGRAM);

        Telegram::sendMessage([
            'chat_id' => $customer->telegram_id,
            'text' => "Ваш тикет создан. Номер обращения: #{$ticket->id}\n\nМы скоро свяжемся с вами.",
            'reply_markup' => TelegramKeyboard::inline(TelegramKeyboard::backToMainMenu('⬅️ Назад')),
        ]);
    }
}
