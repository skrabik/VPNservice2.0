<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use App\Models\Server;
use App\Models\TelegramCommandLog;
use App\Services\VpnProviders\VpnAccessManager;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class KeyCommand extends BaseCommand
{
    public function __construct(Update $update, Customer $customer, array $params)
    {
        parent::__construct($update, $customer, $params);
    }

    public function handle(): void
    {
        Log::info('Key command started', [
            'customer_id' => $this->customer->id,
            'telegram_id' => $this->customer->telegram_id,
            'params' => $this->params,
            'has_active_subscription' => $this->customer->hasActiveSubscription(),
            'has_callback_query' => (bool) $this->update->getCallbackQuery(),
            'message_text' => $this->update->getMessage()?->getText(),
            'callback_data' => $this->update->getCallbackQuery()?->getData(),
        ]);

        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /key',
            'action' => 'Вызвал команду /key',
        ]);

        if (! $this->customer->hasActiveSubscription()) {
            $message = "❌ У вас нет активной подписки!\n\n".
                "Для создания ключей VPN необходимо оформить подписку.\n\n".
                'Нажмите кнопку ниже, чтобы перейти к оплате.';

            $keyboard = [
                ['💳 Оплатить подписку'],
                ['❓ Помощь'],
            ];

            Log::info('Key command aborted: no active subscription', [
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
            ]);

            $this->sendTelegramMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]),
            ], 'key.no_active_subscription');

            return;
        }

        if (isset($this->params['server_id'])) {
            $this->createKeyForServer($this->params['server_id']);

            return;
        }

        $this->showServersList();
    }

    private function showServersList(): void
    {
        Log::info('Loading server list for key command', [
            'customer_id' => $this->customer->id,
            'telegram_id' => $this->customer->telegram_id,
        ]);

        $servers = Server::where('active', true)->get();

        Log::info('Loaded active servers for key command', [
            'customer_id' => $this->customer->id,
            'servers_count' => $servers->count(),
            'servers' => $servers->map(fn (Server $server) => [
                'id' => $server->id,
                'hostname' => $server->hostname,
                'type' => $server->type,
                'active' => $server->active,
            ])->values()->all(),
        ]);

        if ($servers->isEmpty()) {
            $message = "❌ Нет доступных серверов.\n\n".
                'Пожалуйста, попробуйте позже или обратитесь к администратору.';

            $this->sendTelegramMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ], 'key.no_servers_available');

            return;
        }

        $keyboard = [];
        foreach ($servers as $server) {
            $keyboard[] = [
                [
                    'text' => "🌐 {$server->hostname}",
                    'callback_data' => "/key?server_id={$server->id}",
                ],
            ];
        }

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => '/start']];

        $message = '🔑 Выберите сервер для создания ключа VPN:';

        // Удаляем предыдущее сообщение если это callback
        if ($this->update->getCallbackQuery()) {
            $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

            Log::info('Deleting previous message before server list', [
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ]);

            $this->deleteTelegramMessage([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ], 'key.delete_previous_message_before_server_list');
        }

        $this->sendTelegramMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ], 'key.show_servers_list');
    }

    private function createKeyForServer(int $server_id): void
    {
        $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

        Log::info('Key creation requested for server', [
            'customer_id' => $this->customer->id,
            'telegram_id' => $this->customer->telegram_id,
            'server_id' => $server_id,
            'message_id' => $message_id,
        ]);

        $this->editTelegramMessageText([
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => '⏳ Создаю ключ VPN...',
            'parse_mode' => 'HTML',
        ], 'key.show_creation_progress');

        $server = Server::find($server_id);

        if (! $server) {
            Log::warning('Server not found for key creation', [
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'server_id' => $server_id,
            ]);

            $this->editTelegramMessageText([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Сервер недоступен.\n\nПожалуйста, выберите другой сервер или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ], 'key.server_not_found');

            return;
        }

        $server->loadMissing(['defaultInbound', 'inbounds']);

        Log::info('Server loaded for key creation', [
            'customer_id' => $this->customer->id,
            'server_id' => $server->id,
            'server_hostname' => $server->hostname,
            'server_type' => $server->type,
            'inbounds_count' => $server->inbounds->count(),
            'default_inbound_id' => $server->defaultInbound?->id,
        ]);

        try {
            $accessManager = new VpnAccessManager;
            $existingKeysCount = $this->customer->vpnKeys()->count();

            Log::info('Deleting existing customer VPN keys before creating a new one', [
                'customer_id' => $this->customer->id,
                'server_id' => $server->id,
                'existing_keys_count' => $existingKeysCount,
            ]);

            $accessManager->deleteCustomerKeys($this->customer);

            $activeSubscription = $this->customer->subscriptions()
                ->where('date_end', '>', now())
                ->latest('date_end')
                ->first();

            Log::info('Resolved active subscription for key creation', [
                'customer_id' => $this->customer->id,
                'server_id' => $server->id,
                'subscription_id' => $activeSubscription?->id,
                'subscription_date_end' => $activeSubscription?->date_end?->toDateTimeString(),
            ]);

            Log::info('Creating VPN key through access manager', [
                'customer_id' => $this->customer->id,
                'server_id' => $server->id,
                'server_type' => $server->type,
                'expires_at' => $activeSubscription?->date_end?->toDateTimeString(),
            ]);

            $vpnKey = $accessManager->createForCustomer($server, $this->customer, [
                'expires_at' => $activeSubscription?->date_end,
            ]);

            Log::info('VPN key created successfully', [
                'customer_id' => $this->customer->id,
                'server_id' => $server->id,
                'vpn_key_id' => $vpnKey->id,
                'server_user_id' => $vpnKey->server_user_id,
                'server_inbound_id' => $vpnKey->server_inbound_id,
                'server_type' => $vpnKey->server_type,
            ]);
        } catch (\Throwable $exception) {
            Log::error('VPN key creation failed', [
                'customer_id' => $this->customer->id,
                'server_id' => $server_id,
                'server_hostname' => $server->hostname,
                'server_type' => $server->type,
                'default_inbound_id' => $server->defaultInbound?->id,
                'inbounds_count' => $server->inbounds->count(),
                'subscription_id' => $activeSubscription?->id ?? null,
                'subscription_date_end' => $activeSubscription?->date_end?->toDateTimeString(),
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $this->editTelegramMessageText([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Произошла ошибка при создании ключа VPN.\n\nПожалуйста, попробуйте позже или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ], 'key.creation_failed');

            return;
        }

        $message = "🔑 Ваш новый ключ VPN для сервера {$server->hostname}:\n\n".
            "<code>{$vpnKey->access_key}</code>\n\n".
            '⚠️ Храните его в безопасном месте и не передавайте третьим лицам.';

        $keyboard = [
            ['📱 Инструкции по подключению'],
            ['⬅️ Назад'],
        ];

        $this->sendTelegramMessage([
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ], 'key.send_created_key');
    }

    private function sendTelegramMessage(array $payload, string $action): void
    {
        try {
            Log::info('Telegram API request started', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'payload_keys' => array_keys($payload),
                'message_id' => $payload['message_id'] ?? null,
                'text_preview' => mb_substr((string) ($payload['text'] ?? ''), 0, 120),
            ]);

            $response = Telegram::sendMessage($payload);

            Log::info('Telegram API request completed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'telegram_message_id' => method_exists($response, 'getMessageId') ? $response->getMessageId() : null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Telegram API request failed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'payload_keys' => array_keys($payload),
                'message_id' => $payload['message_id'] ?? null,
                'text_preview' => mb_substr((string) ($payload['text'] ?? ''), 0, 120),
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    private function editTelegramMessageText(array $payload, string $action): void
    {
        try {
            Log::info('Telegram editMessageText started', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
                'text_preview' => mb_substr((string) ($payload['text'] ?? ''), 0, 120),
            ]);

            Telegram::editMessageText($payload);

            Log::info('Telegram editMessageText completed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Telegram editMessageText failed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
                'text_preview' => mb_substr((string) ($payload['text'] ?? ''), 0, 120),
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    private function deleteTelegramMessage(array $payload, string $action): void
    {
        try {
            Log::info('Telegram deleteMessage started', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
            ]);

            Telegram::deleteMessage($payload);

            Log::info('Telegram deleteMessage completed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Telegram deleteMessage failed', [
                'action' => $action,
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'message_id' => $payload['message_id'] ?? null,
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }
}
