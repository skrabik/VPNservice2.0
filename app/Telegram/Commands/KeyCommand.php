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

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false,
                ]),
            ];

            $this->sendTelegramMessage($payload, 'key.no_active_subscription');

            return;
        }

        if (($this->params['mode'] ?? null) === 'view_current') {
            $this->showCurrentKey();

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
        $servers = Server::where('active', true)->get();

        if ($servers->isEmpty()) {
            $message = "❌ Нет доступных серверов.\n\n".
                'Пожалуйста, попробуйте позже или обратитесь к администратору.';

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ];

            Log::warning('No active servers available for key command', [
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
            ]);

            $this->sendTelegramMessage($payload, 'key.no_servers_available');

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

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
            ];

            $this->deleteTelegramMessage($payload, 'key.delete_previous_message_before_server_list');
        }

        $payload = [
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ];

        $this->sendTelegramMessage($payload, 'key.show_servers_list');
    }

    private function showCurrentKey(): void
    {
        $vpnKey = $this->customer->activeVpnKeys()
            ->with('server')
            ->latest('id')
            ->first();

        if (! $vpnKey) {
            $message = "❌ У вас пока нет активного ключа VPN.\n\n".
                'Нажмите кнопку ниже, чтобы создать новый ключ.';

            $keyboard = [
                [['text' => '🔑 Получить ключ VPN', 'callback_data' => '/key']],
                [['text' => '🏠 Главное меню', 'callback_data' => 'start']],
            ];

            if ($this->update->getCallbackQuery()) {
                $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

                $payload = [
                    'chat_id' => $this->customer->telegram_id,
                    'message_id' => $message_id,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => $keyboard,
                    ]),
                ];

                $this->editTelegramMessageText($payload, 'key.show_current_key_missing');

                return;
            }

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ];

            $this->sendTelegramMessage($payload, 'key.show_current_key_missing');

            return;
        }

        $serverName = $vpnKey->server?->hostname ?? 'неизвестного сервера';
        $message = "🔐 Ваш текущий ключ VPN для сервера {$serverName}:\n\n".
            "<code>{$vpnKey->access_key}</code>\n\n".
            'Если подключение перестало работать, вы можете выпустить новый ключ.';

        $keyboard = [
            [['text' => '🔑 Получить новый ключ VPN', 'callback_data' => '/key']],
            [['text' => '📱 Инструкции по подключению', 'callback_data' => '/instructions']],
            [['text' => '🏠 Главное меню', 'callback_data' => 'start']],
        ];

        if ($this->update->getCallbackQuery()) {
            $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $keyboard,
                ]),
            ];

            $this->editTelegramMessageText($payload, 'key.show_current_key');

            return;
        }

        $payload = [
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ];

        $this->sendTelegramMessage($payload, 'key.show_current_key');
    }

    private function createKeyForServer(int $server_id): void
    {
        $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

        $progressPayload = [
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => '⏳ Создаю ключ VPN...',
            'parse_mode' => 'HTML',
        ];

        $this->editTelegramMessageText($progressPayload, 'key.show_creation_progress');

        $server = Server::find($server_id);

        if (! $server) {
            Log::warning('Server not found for key creation', [
                'customer_id' => $this->customer->id,
                'telegram_id' => $this->customer->telegram_id,
                'server_id' => $server_id,
            ]);

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Сервер недоступен.\n\nПожалуйста, выберите другой сервер или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ];

            $this->editTelegramMessageText($payload, 'key.server_not_found');

            return;
        }

        $server->loadMissing(['defaultInbound', 'inbounds']);

        $activeSubscription = null;

        try {
            $accessManager = new VpnAccessManager;
            $accessManager->deleteCustomerKeys($this->customer);

            $activeSubscription = $this->customer->subscriptions()
                ->where('date_end', '>', now())
                ->latest('date_end')
                ->first();

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
                'access_key_length' => mb_strlen((string) $vpnKey->access_key),
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

            $payload = [
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Произошла ошибка при создании ключа VPN.\n\nПожалуйста, попробуйте позже или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ];

            $this->editTelegramMessageText($payload, 'key.creation_failed');

            return;
        }

        $message = "🔑 Ваш новый ключ VPN для сервера {$server->hostname}:\n\n".
            "<code>{$vpnKey->access_key}</code>\n\n".
            '⚠️ Храните его в безопасном месте и не передавайте третьим лицам.';

        $keyboard = [
            ['📱 Инструкции по подключению'],
            ['⬅️ Назад'],
        ];

        $payload = [
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ];

        $this->sendTelegramMessage($payload, 'key.send_created_key');
    }

    private function sendTelegramMessage(array $payload, string $action): void
    {
        try {
            Telegram::sendMessage($payload);
        } catch (\Throwable $exception) {
            Log::error('Telegram API request failed', [
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

    private function editTelegramMessageText(array $payload, string $action): void
    {
        try {
            Telegram::editMessageText($payload);
        } catch (\Throwable $exception) {
            Log::error('Telegram editMessageText failed', [
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

    private function deleteTelegramMessage(array $payload, string $action): void
    {
        try {
            Telegram::deleteMessage($payload);
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
