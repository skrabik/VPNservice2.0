<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VpnKey extends Model
{
    protected $fillable = [
        'customer_id',
        'server_id',
        'server_user_id',
        'access_key',
        'server_type',
        'is_active',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Получить клиента, которому принадлежит ключ
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Получить сервер, на котором создан ключ
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
