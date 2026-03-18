<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Screen\AsSource;

class ServerInbound extends Model
{
    use AsSource;

    protected $fillable = [
        'server_id',
        'xui_inbound_id',
        'remark',
        'protocol',
        'port',
        'tag',
        'is_enabled',
        'is_default',
        'settings_json',
        'stream_settings_json',
        'sniffing_json',
        'raw_payload_json',
        'last_synced_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_default' => 'boolean',
        'last_synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function vpnKeys(): HasMany
    {
        return $this->hasMany(VpnKey::class);
    }

    public function decodeSettings(): array
    {
        return $this->decodeJsonColumn($this->settings_json);
    }

    public function decodeStreamSettings(): array
    {
        return $this->decodeJsonColumn($this->stream_settings_json);
    }

    public function decodeSniffing(): array
    {
        return $this->decodeJsonColumn($this->sniffing_json);
    }

    public function decodeRawPayload(): array
    {
        return $this->decodeJsonColumn($this->raw_payload_json);
    }

    private function decodeJsonColumn(null|string|array $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
