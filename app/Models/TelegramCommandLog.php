<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class TelegramCommandLog extends Model
{
    use AsSource, Filterable;

    protected $fillable = [
        'customer_id',
        'command_name',
        'action',
        'action_date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
