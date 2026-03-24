<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;

class CustomerSupportService
{
    public function createTicket(Customer $customer, string $message, string $sourceChannel = SupportTicket::CHANNEL_WEB): SupportTicket
    {
        return SupportTicket::create([
            'customer_id' => $customer->id,
            'message' => trim($message),
            'source_channel' => $sourceChannel,
            'status' => SupportTicket::STATUS_NEW,
        ]);
    }

    public function createReply(SupportTicket $ticket, string $message, ?User $user = null): SupportTicketReply
    {
        $reply = $ticket->replies()->create([
            'user_id' => $user?->id,
            'message' => trim($message),
        ]);

        $now = now();

        $ticket->forceFill([
            'assigned_user_id' => $user?->id ?? $ticket->assigned_user_id,
            'status' => SupportTicket::STATUS_ANSWERED,
            'answered_at' => $ticket->answered_at ?? $now,
            'last_reply_at' => $now,
            'closed_at' => null,
        ])->save();

        return $reply;
    }
}
