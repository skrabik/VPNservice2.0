<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Screen\AsSource;

class ServerParameter extends Model
{
    use AsSource;

    protected $fillable = [
        'server_id',
        'key',
        'value',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
