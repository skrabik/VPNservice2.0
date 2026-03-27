<?php

namespace App\Services\Xui;

use App\Models\Server;
use App\Models\ServerInbound;
use App\Models\ServerParameter;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class ThreeXuiService
{
    private Server $server;

    private CookieJar $cookieJar;

    private bool $authenticated = false;

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->cookieJar = new CookieJar;
    }

    public function testConnection(): array
    {
        try {
            $inbounds = $this->listInbounds();

            return [
                'success' => true,
                'message' => 'Connection established successfully.',
                'count' => count($inbounds),
            ];
        } catch (\Throwable $exception) {
            Log::error('3X-UI connection test failed', [
                'server_id' => $this->server->id,
                'message' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function listInbounds(): array
    {
        $response = $this->request('get', 'panel/api/inbounds/list');
        $payload = $response->json();

        if (! ($payload['success'] ?? false)) {
            throw new RuntimeException($payload['msg'] ?? '3X-UI did not return inbound list.');
        }

        return array_values($payload['obj'] ?? []);
    }

    public function createClient(ServerInbound $inbound, array $client): array
    {
        $payload = [
            'id' => $inbound->xui_inbound_id,
            'settings' => json_encode([
                'clients' => [$client],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        $response = $this->request('post', 'panel/api/inbounds/addClient', $payload);
        $body = $response->json();

        if (! ($body['success'] ?? false)) {
            throw new RuntimeException($body['msg'] ?? '3X-UI failed to create client.');
        }

        $clientFromPanel = $this->findClientOnInbound($inbound, $client['id'], $client['email']);

        return [
            'request' => $client,
            'response' => $body,
            'client' => $clientFromPanel ?: $client,
        ];
    }

    public function deleteClient(ServerInbound $inbound, string $clientId): bool
    {
        if ($clientId === '') {
            return true;
        }

        $response = $this->request(
            'post',
            sprintf('panel/api/inbounds/%s/delClient/%s', $inbound->xui_inbound_id, $clientId)
        );

        $body = $response->json();

        return (bool) ($body['success'] ?? false);
    }

    public function buildVlessUri(ServerInbound $inbound, array $client): string
    {
        $serverHost = $this->resolveServerHost();
        $clientId = $client['uuid'] ?? $client['id'] ?? null;
        $streamSettings = $inbound->decodeStreamSettings();
        $realitySettings = $streamSettings['realitySettings'] ?? [];
        $realityInnerSettings = $realitySettings['settings'] ?? [];
        $displayName = $this->buildKeyDisplayName($inbound);

        if (! is_string($clientId) || trim($clientId) === '') {
            throw new RuntimeException('3X-UI client UUID is missing for VLESS URI generation.');
        }

        $query = array_filter([
            'type' => $streamSettings['network'] ?? 'tcp',
            'security' => $streamSettings['security'] ?? null,
            'encryption' => 'none',
            'flow' => $client['flow'] ?: $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_DEFAULT_CLIENT_FLOW_KEY),
            'pbk' => $realityInnerSettings['publicKey'] ?? null,
            'fp' => $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_DEFAULT_REALITY_FINGERPRINT_KEY)
                ?: ($realityInnerSettings['fingerprint'] ?? null),
            'sni' => $realityInnerSettings['serverName']
                ?: ($realitySettings['serverNames'][0] ?? null)
                ?: $this->extractTargetHost($realitySettings['target'] ?? null),
            'sid' => $this->pickRealityShortId($realitySettings['shortIds'] ?? []),
            'spx' => $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_DEFAULT_REALITY_SPIDER_X_KEY)
                ?: ($realityInnerSettings['spiderX'] ?? null),
        ], static fn ($value) => $value !== null && $value !== '');

        return sprintf(
            'vless://%s@%s:%s?%s#%s',
            $clientId,
            $serverHost,
            $inbound->port,
            http_build_query($query),
            rawurlencode($displayName)
        );
    }

    public function makeClientPayload(array $attributes = []): array
    {
        return [
            'id' => $attributes['id'] ?? (string) Str::uuid(),
            'email' => $attributes['email'] ?? strtolower(Str::random(10)),
            'enable' => $attributes['enable'] ?? true,
            'flow' => $attributes['flow'] ?? '',
            'limitIp' => (int) ($attributes['limitIp'] ?? 0),
            'expiryTime' => (int) ($attributes['expiryTime'] ?? 0),
            'totalGB' => (int) ($attributes['totalGB'] ?? 0),
            'subId' => $attributes['subId'] ?? strtolower(Str::random(16)),
            'tgId' => (string) ($attributes['tgId'] ?? ''),
            'comment' => $attributes['comment'] ?? '',
            'reset' => (int) ($attributes['reset'] ?? 0),
        ];
    }

    public function findClientOnInbound(ServerInbound $inbound, ?string $clientId = null, ?string $clientEmail = null): ?array
    {
        $remoteInbound = collect($this->listInbounds())
            ->first(fn (array $item) => (int) ($item['id'] ?? 0) === (int) $inbound->xui_inbound_id);

        if (! $remoteInbound) {
            return null;
        }

        $clients = data_get($remoteInbound, 'clientStats', []);
        if (! is_array($clients)) {
            $clients = [];
        }

        foreach ($clients as $client) {
            if ($clientId && ($client['uuid'] ?? null) === $clientId) {
                return $client;
            }

            if ($clientEmail && ($client['email'] ?? null) === $clientEmail) {
                return $client;
            }
        }

        $settingsClients = data_get(json_decode((string) ($remoteInbound['settings'] ?? ''), true), 'clients', []);

        foreach ($settingsClients as $client) {
            if ($clientId && ($client['id'] ?? null) === $clientId) {
                return $client;
            }

            if ($clientEmail && ($client['email'] ?? null) === $clientEmail) {
                return $client;
            }
        }

        return null;
    }

    private function request(string $method, string $path, array $payload = []): Response
    {
        $this->authenticate();

        $request = $this->httpClient();
        $url = $this->buildUrl($path);

        return match (strtolower($method)) {
            'get' => $request->get($url),
            'post' => $request->post($url, $payload),
            default => throw new RuntimeException("Unsupported HTTP method [$method]."),
        };
    }

    private function authenticate(): void
    {
        if ($this->authenticated) {
            return;
        }

        $response = $this->httpClient()
            ->asForm()
            ->post($this->buildUrl('login'), [
                'username' => $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_PANEL_USERNAME_KEY),
                'password' => $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_PANEL_PASSWORD_KEY),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to authenticate in 3X-UI panel.');
        }

        $payload = $response->json();

        if (is_array($payload) && ! ($payload['success'] ?? false)) {
            throw new RuntimeException($payload['msg'] ?? '3X-UI authentication failed.');
        }

        $this->authenticated = true;
    }

    private function httpClient(): PendingRequest
    {
        return Http::withoutVerifying()
            ->acceptJson()
            ->contentType('application/json')
            ->withOptions([
                'cookies' => $this->cookieJar,
            ]);
    }

    private function buildUrl(string $path): string
    {
        $baseUrl = rtrim((string) $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_PANEL_URL_KEY), '/');
        $basePath = trim((string) $this->server->getParameterValue(ServerParameter::SERVER_PARAMETER_PANEL_BASE_PATH_KEY), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('3X-UI panel URL is not configured.');
        }

        $segments = [$baseUrl];

        if ($basePath !== '') {
            $segments[] = $basePath;
        }

        $segments[] = ltrim($path, '/');

        return implode('/', $segments);
    }

    private function extractTargetHost(?string $target): ?string
    {
        if (! $target) {
            return null;
        }

        return explode(':', $target)[0] ?? null;
    }

    private function pickRealityShortId(array $shortIds): ?string
    {
        foreach ($shortIds as $shortId) {
            if (is_string($shortId) && $shortId !== '') {
                return $shortId;
            }
        }

        return null;
    }

    private function buildKeyDisplayName(ServerInbound $inbound): string
    {
        $serverLabel = trim((string) ($this->server->hostname ?: $this->server->ip_address));
        $inboundLabel = trim((string) $inbound->remark);

        return implode(' | ', array_filter([
            'NerpaVPN',
            $serverLabel !== '' ? $serverLabel : null,
            $inboundLabel !== '' ? $inboundLabel : null,
        ]));
    }

    private function resolveServerHost(): string
    {
        $ipAddress = trim((string) $this->server->ip_address);
        if ($ipAddress !== '') {
            return $ipAddress;
        }

        $hostname = trim((string) $this->server->hostname);
        if ($hostname !== '') {
            return $hostname;
        }

        throw new RuntimeException('3X-UI server host is not configured.');
    }
}
