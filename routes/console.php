<?php

use App\Models\VpnKey;
use App\Services\Xui\ThreeXuiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vpn:client-limit {vpn_key_id : ID записи vpn_keys}', function () {
    $vpnKeyId = (int) $this->argument('vpn_key_id');

    $vpnKey = VpnKey::query()
        ->with(['server.parameters', 'inbound'])
        ->find($vpnKeyId);

    if (! $vpnKey) {
        $this->error("VPN key with ID [{$vpnKeyId}] not found.");

        return self::FAILURE;
    }

    if (! $vpnKey->server) {
        $this->error("Server is not attached to VPN key [{$vpnKeyId}].");

        return self::FAILURE;
    }

    $inbound = $vpnKey->inbound ?: $vpnKey->server->defaultInbound()->first();

    if (! $inbound) {
        $this->error("Inbound is not configured for VPN key [{$vpnKeyId}].");

        return self::FAILURE;
    }

    $clientId = $vpnKey->external_uuid ?: $vpnKey->server_user_id;
    $clientEmail = $vpnKey->external_email;

    if (! $clientId && ! $clientEmail) {
        $this->error("VPN key [{$vpnKeyId}] does not contain external client identifiers.");

        return self::FAILURE;
    }

    try {
        $service = new ThreeXuiService($vpnKey->server);
        $client = $service->findClientOnInbound($inbound, $clientId, $clientEmail);
    } catch (\Throwable $exception) {
        $this->error('Failed to fetch client from 3X-UI: '.$exception->getMessage());

        return self::FAILURE;
    }

    if (! $client) {
        $this->error("Client was not found on inbound [{$inbound->xui_inbound_id}].");

        return self::FAILURE;
    }

    $limitIp = $client['limitIp'] ?? null;

    $this->table(
        ['Field', 'Value'],
        [
            ['vpn_key_id', (string) $vpnKey->id],
            ['server_id', (string) $vpnKey->server->id],
            ['server_host', (string) ($vpnKey->server->hostname ?: $vpnKey->server->ip_address)],
            ['inbound_id', (string) $inbound->xui_inbound_id],
            ['client_email', (string) ($client['email'] ?? $clientEmail ?? '')],
            ['client_uuid', (string) ($client['uuid'] ?? $client['id'] ?? $clientId ?? '')],
            ['limitIp', $limitIp === null ? 'not set' : (string) $limitIp],
            ['enable', array_key_exists('enable', $client) ? ((bool) $client['enable'] ? 'true' : 'false') : 'unknown'],
            ['expiryTime', (string) ($client['expiryTime'] ?? '0')],
            ['totalGB', (string) ($client['totalGB'] ?? $client['total'] ?? '0')],
        ]
    );

    return self::SUCCESS;
})->purpose('Show 3X-UI client limitIp by VPN key ID');
