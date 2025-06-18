<?php

namespace App\Telegram\Commands;

use App\Models\Customer;
use App\Models\Server;
use App\Models\VpnKey;
use App\Services\OutlineService;
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
        if (isset($this->params['server_id'])) {
            $this->createKeyForServer($this->params['server_id']);

            return;
        }

        $this->showServersList();
    }

    private function showServersList(): void
    {
        $servers = Server::all();

        if ($servers->isEmpty()) {
            $message = "❌ Нет доступных серверов.\n\n".
                      'Пожалуйста, попробуйте позже или обратитесь к администратору.';

            Telegram::sendMessage([
                'chat_id' => $this->customer->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

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

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'start']];

        $message = '🔑 Выберите сервер для создания ключа VPN:';

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ]);
    }

    private function createKeyForServer(int $server_id): void
    {
        $message_id = $this->update->getCallbackQuery()->getMessage()->getMessageId();

        Telegram::editMessageText([
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => '⏳ Создаю ключ VPN...',
            'parse_mode' => 'HTML',
        ]);

        $server = Server::find($server_id);

        if (! $server) {
            Telegram::editMessageText([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Сервер недоступен.\n\nПожалуйста, выберите другой сервер или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $outline_service = new OutlineService($server);

        $password = $this->customer->telegram_id.'_'.time();

        $user = $outline_service->createUser($password);

        if (! $user) {
            Telegram::editMessageText([
                'chat_id' => $this->customer->telegram_id,
                'message_id' => $message_id,
                'text' => "❌ Произошла ошибка при создании ключа VPN.\n\nПожалуйста, попробуйте позже или обратитесь к администратору.",
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        VpnKey::create([
            'customer_id' => $this->customer->id,
            'server_id' => $server_id,
            'server_user_id' => $user['id'],
            'access_key' => $user['accessUrl'],
            'server_type' => $server->type,
            'is_active' => true,
        ]);

        $message = "🔑 Ваш новый ключ VPN для сервера {$server->name}:\n\n".
                  "<code>{$user['accessUrl']}</code>\n\n".
                  '⚠️ Храните его в безопасном месте и не передавайте третьим лицам.';

        $keyboard = [
            ['⬅️ Назад'],
        ];

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ]),
        ]);
    }
}
