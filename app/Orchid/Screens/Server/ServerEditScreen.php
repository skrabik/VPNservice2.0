<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Server;

use App\Models\Server;
use App\Models\ServerInbound;
use App\Models\ServerParameter;
use App\Orchid\Layouts\Server\ServerEditLayout;
use App\Orchid\Layouts\Server\ServerInboundClientPreviewLayout;
use App\Orchid\Layouts\Server\ServerInboundListLayout;
use App\Orchid\Layouts\Server\ServerParameterEditLayout;
use App\Services\Xui\ThreeXuiService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ServerEditScreen extends Screen
{
    /**
     * @var Server
     */
    public $server;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Server $server): iterable
    {
        return [
            'server' => $server,
            'parameters' => $server->parameters,
            'server_parameters' => $server->parameters,
            'server_inbounds' => $server->exists ? $server->inbounds()->orderByDesc('is_default')->orderBy('remark')->get() : collect(),
            'inbound_clients_preview' => $server->exists ? $this->buildInboundClientsPreview($server) : collect(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->server->exists ? 'Edit Server' : 'Create Server';
    }

    public function description(): ?string
    {
        return 'Server Information';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.servers',
        ];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Test 3X-UI Connection')
                ->icon('bs.activity')
                ->method('testConnection')
                ->novalidate()
                ->canSee($this->server->exists && $this->server->type === Server::SERVER_TYPE_3XUI_KEY),

            Button::make('Sync Inbounds')
                ->icon('bs.arrow-repeat')
                ->method('syncInbounds')
                ->novalidate()
                ->canSee($this->server->exists && $this->server->type === Server::SERVER_TYPE_3XUI_KEY),

            Button::make('Refresh Clients Preview')
                ->icon('bs.people')
                ->method('loadClientsPreview')
                ->novalidate()
                ->canSee($this->server->exists && $this->server->type === Server::SERVER_TYPE_3XUI_KEY),

            Button::make('Delete')
                ->icon('bs.trash')
                ->confirm('Are you sure you want to delete this server?')
                ->method('remove')
                ->canSee($this->server->exists),

            Button::make('Save')
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(ServerEditLayout::class)
                ->title('Server Information')
                ->description('Update server information.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),

            Layout::block(ServerParameterEditLayout::class)
                ->title('Server Parameters')
                ->description('Update server parameters.')
                ->commands(
                    Button::make('Save')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                ),

            Layout::block(ServerInboundListLayout::class)
                ->title('3X-UI Inbounds')
                ->description('Synchronized inbounds and default Telegram target.')
                ->canSee($this->server->exists && $this->server->type === Server::SERVER_TYPE_3XUI_KEY),

            Layout::block(ServerInboundClientPreviewLayout::class)
                ->title('Inbound Clients Preview')
                ->description('Clients from the last synced inbound payload.')
                ->canSee($this->server->exists && $this->server->type === Server::SERVER_TYPE_3XUI_KEY),
        ];
    }

    public function save(Server $server, Request $request)
    {
        $serverData = $request->get('server', []);
        $serverData['type'] = Server::SERVER_TYPE_3XUI_KEY;

        $server->fill($serverData)->save();

        if ($request->has('server_parameters')) {
            foreach ($request->get('server_parameters') as $parameterId => $parameterData) {
                $parameter = $server->parameters()->find($parameterId);
                if ($parameter) {
                    $value = $this->normalizeServerParameterValue(
                        $parameter->key,
                        $parameterData['value'] ?? null,
                        $parameter->value
                    );

                    if ($value !== $parameter->value) {
                        $parameter->update(['value' => $value]);
                    }
                }
            }
        }

        if ($request->has('new_server_parameters')) {
            foreach ($request->get('new_server_parameters') as $parameterKey => $parameterValue) {
                $value = $this->normalizeServerParameterValue($parameterKey, $parameterValue);

                if ($value === null) {
                    continue;
                }

                $server->parameters()->updateOrCreate(
                    ['key' => $parameterKey],
                    ['value' => $value]
                );
            }
        }

        Toast::info('Server was saved.');

        return redirect()->route('platform.servers');
    }

    public function remove(Server $server)
    {
        $server->delete();
        Toast::info('Server was removed');

        return redirect()->route('platform.servers');
    }

    public function testConnection(Server $server)
    {
        if ($server->type !== Server::SERVER_TYPE_3XUI_KEY) {
            Toast::warning('Connection test is available only for 3X-UI servers.');

            return;
        }

        $result = (new ThreeXuiService($server))->testConnection();

        if ($result['success']) {
            Toast::info("3X-UI connection is healthy. Inbounds found: {$result['count']}.");
        } else {
            Toast::error($result['message']);
        }
    }

    public function syncInbounds(Server $server)
    {
        if ($server->type !== Server::SERVER_TYPE_3XUI_KEY) {
            Toast::warning('Inbound sync is available only for 3X-UI servers.');

            return;
        }

        try {
            $count = $this->syncServerInbounds($server);
            Toast::info("Synchronized {$count} inbound(s).");
        } catch (\Throwable $exception) {
            Toast::error($exception->getMessage());
        }
    }

    public function loadClientsPreview(Server $server)
    {
        if ($server->type !== Server::SERVER_TYPE_3XUI_KEY) {
            Toast::warning('Clients preview is available only for 3X-UI servers.');

            return;
        }

        try {
            $count = $this->syncServerInbounds($server);
            Toast::info("Clients preview refreshed from {$count} inbound(s).");
        } catch (\Throwable $exception) {
            Toast::error($exception->getMessage());
        }
    }

    public function setDefaultInbound(Server $server, Request $request)
    {
        $serverInboundId = (int) $request->input('server_inbound_id');
        $inbound = $server->inbounds()->find($serverInboundId);

        if (! $inbound) {
            Toast::warning('Inbound not found.');

            return;
        }

        $server->inbounds()->update(['is_default' => false]);
        $inbound->update(['is_default' => true]);

        Toast::info('Default inbound updated.');
    }

    private function syncServerInbounds(Server $server): int
    {
        $service = new ThreeXuiService($server);
        $remoteInbounds = $service->listInbounds();
        $remoteIds = [];

        foreach ($remoteInbounds as $index => $remoteInbound) {
            $remoteIds[] = (int) $remoteInbound['id'];

            $existing = $server->inbounds()
                ->where('xui_inbound_id', (int) $remoteInbound['id'])
                ->first();

            $inbound = $server->inbounds()->updateOrCreate(
                ['xui_inbound_id' => (int) $remoteInbound['id']],
                [
                    'remark' => $remoteInbound['remark'] ?? null,
                    'protocol' => $remoteInbound['protocol'] ?? null,
                    'port' => isset($remoteInbound['port']) ? (int) $remoteInbound['port'] : null,
                    'tag' => $remoteInbound['tag'] ?? null,
                    'is_enabled' => (bool) ($remoteInbound['enable'] ?? false),
                    'is_default' => $existing?->is_default ?? $index === 0,
                    'settings_json' => $this->normalizeJsonPayload($remoteInbound['settings'] ?? null),
                    'stream_settings_json' => $this->normalizeJsonPayload($remoteInbound['streamSettings'] ?? null),
                    'sniffing_json' => $this->normalizeJsonPayload($remoteInbound['sniffing'] ?? null),
                    'raw_payload_json' => $this->normalizeJsonPayload($remoteInbound),
                    'last_synced_at' => now(),
                ]
            );

            if ($inbound->is_default) {
                $server->inbounds()
                    ->where('id', '!=', $inbound->id)
                    ->update(['is_default' => false]);
            }
        }

        $server->inbounds()
            ->whereNotIn('xui_inbound_id', $remoteIds)
            ->delete();

        if (! $server->inbounds()->where('is_default', true)->exists()) {
            $server->inbounds()
                ->orderByDesc('is_enabled')
                ->orderBy('id')
                ->limit(1)
                ->update(['is_default' => true]);
        }

        return count($remoteInbounds);
    }

    private function buildInboundClientsPreview(Server $server)
    {
        return $server->inbounds
            ->flatMap(function (ServerInbound $inbound) {
                $payload = $inbound->decodeRawPayload();
                $clientStats = $payload['clientStats'] ?? [];
                $settingsClients = $inbound->decodeSettings()['clients'] ?? [];

                if (! $clientStats && ! $settingsClients) {
                    return [];
                }

                if (! $clientStats) {
                    $clientStats = $settingsClients;
                }

                return collect($clientStats)->map(function (array $client) use ($inbound, $settingsClients) {
                    $settingsClient = collect($settingsClients)->first(function (array $candidate) use ($client) {
                        return ($candidate['id'] ?? null) === ($client['uuid'] ?? $client['id'] ?? null)
                            || ($candidate['email'] ?? null) === ($client['email'] ?? null);
                    }) ?? [];

                    $lastOnline = (int) ($client['lastOnline'] ?? 0);

                    return [
                        'inbound_remark' => $inbound->remark ?: ('Inbound #'.$inbound->xui_inbound_id),
                        'email' => $client['email'] ?? $settingsClient['email'] ?? '-',
                        'uuid' => $client['uuid'] ?? $client['id'] ?? $settingsClient['id'] ?? '-',
                        'sub_id' => $client['subId'] ?? $settingsClient['subId'] ?? '-',
                        'traffic_limit_bytes' => isset($client['total']) ? (int) $client['total'] : ($settingsClient['totalGB'] ?? null),
                        'traffic_used_bytes' => (int) ($client['allTime'] ?? 0),
                        'is_enabled' => (bool) ($client['enable'] ?? $settingsClient['enable'] ?? false),
                        'last_online_at' => $lastOnline > 0 ? date('d.m.Y H:i', (int) floor($lastOnline / 1000)) : null,
                    ];
                });
            })
            ->values();
    }

    private function normalizeJsonPayload(mixed $payload): ?string
    {
        if ($payload === null || $payload === '') {
            return null;
        }

        if (is_string($payload)) {
            return $payload;
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalizeServerParameterValue(string $key, mixed $value, ?string $currentValue = null): ?string
    {
        if ($value === null) {
            return $currentValue;
        }

        $value = is_string($value) ? trim($value) : (string) $value;

        if ($key === ServerParameter::SERVER_PARAMETER_PANEL_PASSWORD_KEY && $value === '') {
            return $currentValue;
        }

        return $value === '' ? null : $value;
    }
}
