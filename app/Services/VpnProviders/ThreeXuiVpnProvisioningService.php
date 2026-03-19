<?php

namespace App\Services\VpnProviders;

use App\Models\Customer;
use App\Models\Server;
use App\Models\ServerInbound;
use App\Models\ServerParameter;
use App\Models\VpnKey;
use App\Services\Xui\ThreeXuiService;
use RuntimeException;

class ThreeXuiVpnProvisioningService implements VpnProvisioningServiceInterface
{
    public function createAccess(Server $server, Customer $customer, array $context = []): array
    {
        $inbound = $this->resolveInbound($server, $context);
        $service = new ThreeXuiService($server);

        $expiresAt = $context['expires_at'] ?? null;
        $expiryTime = $expiresAt?->getTimestamp() ? $expiresAt->getTimestamp() * 1000 : 0;
        $trafficLimitBytes = (int) ($context['traffic_limit_bytes'] ?? 0);
        $comment = trim(implode(' ', array_filter([
            $customer->first_name,
            $customer->last_name,
        ])));

        $clientPayload = $service->makeClientPayload([
            'email' => strtolower('tg'.$customer->id.'_'.substr(md5((string) microtime(true)), 0, 8)),
            'flow' => $server->getParameterValue(ServerParameter::SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY) ?? '',
            'limitIp' => 1,
            'expiryTime' => $expiryTime,
            'totalGB' => $trafficLimitBytes,
            'tgId' => $customer->telegram_id ?? '',
            'comment' => $comment,
        ]);

        $created = $service->createClient($inbound, $clientPayload);
        $remoteClient = $created['client'];

        return [
            'server_inbound_id' => $inbound->id,
            'server_user_id' => (string) ($remoteClient['uuid'] ?? $remoteClient['id'] ?? $clientPayload['id']),
            'access_key' => $service->buildVlessUri($inbound, array_merge($clientPayload, $remoteClient)),
            'server_type' => $server->type,
            'external_uuid' => $remoteClient['uuid'] ?? $remoteClient['id'] ?? $clientPayload['id'],
            'external_email' => $remoteClient['email'] ?? $clientPayload['email'],
            'external_sub_id' => $remoteClient['subId'] ?? $clientPayload['subId'],
            'traffic_limit_bytes' => (int) ($remoteClient['total'] ?? $remoteClient['totalGB'] ?? $trafficLimitBytes),
            'traffic_used_bytes' => (int) ($remoteClient['allTime'] ?? 0),
            'panel_payload_json' => json_encode([
                'inbound' => $inbound->decodeRawPayload(),
                'request' => $created['request'],
                'response' => $created['response'],
                'client' => $remoteClient,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'expires_at' => $expiresAt,
        ];
    }

    public function deleteAccess(Server $server, VpnKey $vpnKey): bool
    {
        $inbound = $vpnKey->inbound ?: $server->defaultInbound;

        if (! $inbound) {
            return false;
        }

        $clientId = $vpnKey->external_uuid ?: $vpnKey->server_user_id;

        if (! $clientId) {
            return true;
        }

        $service = new ThreeXuiService($server);

        return $service->deleteClient($inbound, $clientId);
    }

    private function resolveInbound(Server $server, array $context): ServerInbound
    {
        if (($context['server_inbound'] ?? null) instanceof ServerInbound) {
            return $context['server_inbound'];
        }

        if (! empty($context['server_inbound_id'])) {
            $inbound = $server->inbounds()->find($context['server_inbound_id']);

            if ($inbound) {
                return $inbound;
            }
        }

        $inbound = $server->defaultInbound()->first();

        if (! $inbound) {
            throw new RuntimeException('Default 3X-UI inbound is not configured for this server.');
        }

        return $inbound;
    }
}
