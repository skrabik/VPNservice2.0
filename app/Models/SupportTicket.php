<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

class SupportTicket extends Model
{
    use AsSource, HasFactory;

    public const CHANNEL_TELEGRAM = 'telegram';
    public const CHANNEL_WEB = 'web';

    public const STATUS_NEW = 'new';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'customer_id',
        'source_channel',
        'message',
        'status',
        'assigned_user_id',
        'answered_at',
        'closed_at',
        'last_reply_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'last_reply_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->oldest('created_at');
    }

    public function markAnswered(): void
    {
        $now = now();

        $this->forceFill([
            'status' => self::STATUS_ANSWERED,
            'answered_at' => $this->answered_at ?? $now,
            'last_reply_at' => $now,
            'closed_at' => null,
        ])->save();
    }

    public function markClosed(): void
    {
        $this->forceFill([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
        ])->save();
    }
}
