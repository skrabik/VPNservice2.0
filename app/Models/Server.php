<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Server extends Model
{
    use AsSource, Filterable, HasFactory;

    const SERVER_TYPE_3XUI_KEY = '3xui';

    const SERVER_TYPE_3XUI_NAME = '3X-UI';

    const SERVER_TYPE_OPTIONS = [
        self::SERVER_TYPE_3XUI_KEY => self::SERVER_TYPE_3XUI_NAME,
    ];

    protected $fillable = [
        'hostname',
        'ip_address',
        'location',
        'active',
        'max_users',
        'type',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $allowedFilters = [
        'id',
        'name',
        'type',
        'host',
        'port',
        'active',
        'created_at',
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'type',
        'host',
        'port',
        'active',
        'created_at',
    ];

    /**
     * Получить параметры сервера.
     */
    public function parameters(): HasMany
    {
        return $this->hasMany(ServerParameter::class);
    }

    public function inbounds(): HasMany
    {
        return $this->hasMany(ServerInbound::class);
    }

    public function defaultInbound(): HasOne
    {
        return $this->hasOne(ServerInbound::class)->where('is_default', true);
    }

    public function getParameterValue(string $key): ?string
    {
        return $this->parameters
            ->firstWhere('key', $key)
            ?->value;
    }
}
