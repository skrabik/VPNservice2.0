<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;

class SupportTicketReply extends Model
{
    use AsSource, HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'sent_to_telegram_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'sent_to_telegram_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
