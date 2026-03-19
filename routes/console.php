<?php

use App\Models\Server;
use App\Models\VpnKey;
use App\Services\Xui\ThreeXuiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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

Artisan::command('vpn:cleanup-orphan-clients {--dry-run : Only show orphan clients without deleting them} {--server_id= : Limit cleanup to one server ID}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $serverId = $this->option('server_id');

    $serversQuery = Server::query()
        ->where('type', Server::SERVER_TYPE_3XUI_KEY)
        ->with(['parameters', 'inbounds']);

    if ($serverId !== null && $serverId !== '') {
        $serversQuery->whereKey((int) $serverId);
    }

    $servers = $serversQuery->get();

    if ($servers->isEmpty()) {
        $this->warn('No 3X-UI servers found for cleanup.');

        return self::SUCCESS;
    }

    $summary = [
        'dry_run' => $dryRun,
        'servers_scanned' => 0,
        'clients_scanned' => 0,
        'managed_clients' => 0,
        'orphans_found' => 0,
        'deleted' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    $rows = [];

    foreach ($servers as $server) {
        $summary['servers_scanned']++;
        $service = new ThreeXuiService($server);
        $localInboundsByRemoteId = $server->inbounds->keyBy(
            fn ($inbound) => (int) $inbound->xui_inbound_id
        );

        try {
            $remoteInbounds = $service->listInbounds();
        } catch (\Throwable $exception) {
            $summary['failed']++;
            $rows[] = [
                (string) $server->id,
                $server->hostname ?: $server->ip_address ?: '-',
                '-',
                '-',
                '-',
                'error',
                $exception->getMessage(),
            ];
            continue;
        }

        foreach ($remoteInbounds as $remoteInbound) {
            $settingsClients = data_get(
                json_decode((string) ($remoteInbound['settings'] ?? ''), true),
                'clients',
                []
            );

            if (! is_array($settingsClients)) {
                $settingsClients = [];
            }

            foreach ($settingsClients as $client) {
                $summary['clients_scanned']++;

                $clientEmail = trim((string) ($client['email'] ?? ''));
                $clientUuid = trim((string) ($client['id'] ?? $client['uuid'] ?? ''));
                $clientSubId = trim((string) ($client['subId'] ?? ''));
                $clientTgId = trim((string) ($client['tgId'] ?? ''));
                $remoteInboundId = (int) ($remoteInbound['id'] ?? 0);
                $localInbound = $localInboundsByRemoteId->get($remoteInboundId);

                $isManagedClient = ($clientTgId !== '' && $clientTgId !== '0')
                    || preg_match('/^tg\d+_/i', $clientEmail) === 1;

                if (! $isManagedClient) {
                    $summary['skipped']++;
                    continue;
                }

                $summary['managed_clients']++;

                if ($clientUuid === '' && $clientEmail === '' && $clientSubId === '') {
                    $summary['skipped']++;
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'skipped',
                        'Missing client identifiers',
                    ];
                    continue;
                }

                $existsInDatabase = VpnKey::query()
                    ->where('server_id', $server->id)
                    ->where(function ($query) use ($clientUuid, $clientEmail, $clientSubId) {
                        if ($clientUuid !== '') {
                            $query->orWhere('external_uuid', $clientUuid)
                                ->orWhere('server_user_id', $clientUuid);
                        }

                        if ($clientEmail !== '') {
                            $query->orWhere('external_email', $clientEmail);
                        }

                        if ($clientSubId !== '') {
                            $query->orWhere('external_sub_id', $clientSubId);
                        }
                    })
                    ->exists();

                if ($existsInDatabase) {
                    continue;
                }

                $summary['orphans_found']++;

                if (! $localInbound) {
                    $summary['failed']++;
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'failed',
                        'Inbound is not synced locally',
                    ];
                    continue;
                }

                if ($dryRun) {
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'dry-run',
                        'Would delete orphan client',
                    ];
                    continue;
                }

                $deleted = false;

                try {
                    $deleted = $clientUuid !== ''
                        ? $service->deleteClient($localInbound, $clientUuid)
                        : false;
                } catch (\Throwable $exception) {
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'failed',
                        $exception->getMessage(),
                    ];
                    $summary['failed']++;
                    continue;
                }

                if ($deleted) {
                    $summary['deleted']++;
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'deleted',
                        'Client removed from 3X-UI',
                    ];
                } else {
                    $summary['failed']++;
                    $rows[] = [
                        (string) $server->id,
                        $server->hostname ?: $server->ip_address ?: '-',
                        (string) $remoteInboundId,
                        $clientEmail ?: '-',
                        $clientUuid ?: '-',
                        'failed',
                        '3X-UI deletion returned false',
                    ];
                }
            }
        }
    }

    if ($rows !== []) {
        $this->table(
            ['server_id', 'server', 'inbound_id', 'email', 'uuid', 'status', 'details'],
            $rows
        );
    } else {
        $this->info('No orphan clients found.');
    }

    $this->table(
        ['Metric', 'Value'],
        array_map(
            fn ($key, $value) => [$key, is_bool($value) ? ($value ? 'true' : 'false') : (string) $value],
            array_keys($summary),
            $summary
        )
    );

    Log::info('3X-UI orphan cleanup completed', $summary);

    return self::SUCCESS;
})->purpose('Delete 3X-UI clients that are missing from vpn_keys');
