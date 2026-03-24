<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SupportTicketReplyDeliveryService
{
    public function deliverToTelegram(SupportTicketReply $reply): bool
    {
        if ($reply->sent_to_telegram_at) {
            return true;
        }

        $reply->loadMissing('supportTicket.customer');

        $ticket = $reply->supportTicket;
        $customer = $ticket?->customer;

        if (! $ticket || ! $customer) {
            Log::warning('Support ticket reply delivery skipped: missing ticket or customer', [
                'reply_id' => $reply->id,
            ]);

            return false;
        }

        if ($ticket->source_channel !== SupportTicket::CHANNEL_TELEGRAM || ! $customer->telegram_id) {
            return false;
        }

        try {
            Telegram::sendMessage([
                'chat_id' => $customer->telegram_id,
                'text' => "Ответ по тикету #{$ticket->id}:\n\n{$reply->message}",
            ]);

            $reply->forceFill([
                'sent_to_telegram_at' => now(),
            ])->save();

            return true;
        } catch (\Throwable $exception) {
            Log::error('Support ticket reply delivery failed', [
                'reply_id' => $reply->id,
                'ticket_id' => $ticket->id,
                'customer_id' => $customer->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
