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

    public const SERVER_PARAMETER_PANEL_URL_KEY = 'panel_url';

    public const SERVER_PARAMETER_PANEL_USERNAME_KEY = 'panel_username';

    public const SERVER_PARAMETER_PANEL_PASSWORD_KEY = 'panel_password';

    public const SERVER_PARAMETER_PANEL_BASE_PATH_KEY = 'panel_base_path';

    public const SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY = 'default_client_flow';

    public const SERVER_PARAMETER_DEFAULT_REALITY_FINGERPRINT_KEY = 'default_reality_fingerprint';

    public const SERVER_PARAMETER_DEFAULT_REALITY_SPIDER_X_KEY = 'default_reality_spider_x';

    public const OPTIONAL_SERVER_PARAMETERS = [
        self::SERVER_PARAMETER_PANEL_BASE_PATH_KEY,
        self::SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY,
        self::SERVER_PARAMETER_DEFAULT_REALITY_FINGERPRINT_KEY,
        self::SERVER_PARAMETER_DEFAULT_REALITY_SPIDER_X_KEY,
    ];

    public const SERVER_PARAMETER_LABELS = [
        self::SERVER_PARAMETER_URL_KEY => 'API URL',
        self::SERVER_PARAMETER_PANEL_URL_KEY => 'Panel URL',
        self::SERVER_PARAMETER_PANEL_USERNAME_KEY => 'Panel Username',
        self::SERVER_PARAMETER_PANEL_PASSWORD_KEY => 'Panel Password',
        self::SERVER_PARAMETER_PANEL_BASE_PATH_KEY => 'Panel Base Path',
        self::SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY => 'Default Client Flow',
        self::SERVER_PARAMETER_DEFAULT_REALITY_FINGERPRINT_KEY => 'Default Reality Fingerprint',
        self::SERVER_PARAMETER_DEFAULT_REALITY_SPIDER_X_KEY => 'Default Reality Spider X',
    ];

    public const SERVER_TYPES_PARAMETERS = [
        Server::SERVER_TYPE_3XUI_KEY => [
            self::SERVER_PARAMETER_PANEL_URL_KEY,
            self::SERVER_PARAMETER_PANEL_USERNAME_KEY,
            self::SERVER_PARAMETER_PANEL_PASSWORD_KEY,
            self::SERVER_PARAMETER_PANEL_BASE_PATH_KEY,
            self::SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY,
            self::SERVER_PARAMETER_DEFAULT_REALITY_FINGERPRINT_KEY,
            self::SERVER_PARAMETER_DEFAULT_REALITY_SPIDER_X_KEY,
        ],
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
