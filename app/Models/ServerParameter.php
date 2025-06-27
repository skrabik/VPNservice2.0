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

    public const SERVER_PARAMETER_URL_KEY = 'url';

    public const SERVER_TYPES_PARAMETERS = [
        Server::SERVER_TYPE_OUTLINE_KEY => [
            self::SERVER_PARAMETER_URL_KEY,
        ],
        Server::SERVER_TYPE_OPENVPN_KEY => [
            self::SERVER_PARAMETER_URL_KEY,
        ],
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
