<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Server extends Model
{
    use AsSource, Filterable, HasFactory;

    const SERVER_TYPE_OUTLINE_KEY = 'outline';

    const SERVER_TYPE_OUTLINE_NAME = 'Outline VPN';

    const SERVER_TYPE_OPENVPN_KEY = 'openvpn';

    const SERVER_TYPE_OPENVPN_NAME = 'OpenVPN';

    const SERVER_TYPE_OPTIONS = [
        self::SERVER_TYPE_OUTLINE_KEY => self::SERVER_TYPE_OUTLINE_NAME,
        self::SERVER_TYPE_OPENVPN_KEY => self::SERVER_TYPE_OPENVPN_NAME,
    ];

    protected $fillable = [
        'name',
        'type',
        'host',
        'port',
        'username',
        'password',
        'active',
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
}
