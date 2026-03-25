<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TelegramBroadcast extends Model
{
    use AsSource, Filterable;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'message',
        'is_test',
        'status',
        'target_count',
        'success_count',
        'failed_count',
        'batch_id',
        'queued_at',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'is_test' => 'boolean',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $allowedFilters = [
        'id',
        'status',
        'is_test',
        'created_at',
        'started_at',
        'finished_at',
    ];

    protected $allowedSorts = [
        'id',
        'status',
        'is_test',
        'target_count',
        'success_count',
        'failed_count',
        'created_at',
        'started_at',
        'finished_at',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
