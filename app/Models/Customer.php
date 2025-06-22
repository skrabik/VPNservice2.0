<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;

class Customer extends Model
{
    use Filterable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'telegram_id',
        'telegram_username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id',
        'first_name',
        'last_name',
        'email',
        'telegram_id',
        'telegram_username',
        'created_at',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'first_name',
        'last_name',
        'email',
        'telegram_id',
        'telegram_username',
        'created_at',
    ];

    /**
     * Получить все ключи VPN клиента
     */
    public function vpnKeys(): HasMany
    {
        return $this->hasMany(VpnKey::class);
    }

    /**
     * Получить активные ключи VPN клиента
     */
    public function activeVpnKeys(): HasMany
    {
        return $this->hasMany(VpnKey::class)->where('is_active', true);
    }

    /**
     * Получить сервер клиента
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Проверить, есть ли активная подписка
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('date_end', '>', now())
            ->exists();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
