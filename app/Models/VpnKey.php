<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VpnKey extends Model
{
    protected $fillable = [
        'customer_id',
        'server_id',
        'server_inbound_id',
        'server_user_id',
        'access_key',
        'server_type',
        'external_uuid',
        'external_email',
        'external_sub_id',
        'traffic_limit_bytes',
        'traffic_used_bytes',
        'panel_payload_json',
        'is_active',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'traffic_limit_bytes' => 'integer',
        'traffic_used_bytes' => 'integer',
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

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(ServerInbound::class, 'server_inbound_id');
    }
}
