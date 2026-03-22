<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Telegram\Commands\CommandFactory;
use App\Telegram\TelegramManager;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class CommandService
{
    public static function process(Update $update, Customer $customer): void
    {
        extract(TelegramManager::parseMessageData($update));

        $command_class = CommandFactory::getCommandClass($command_name);

        Log::info('Telegram command resolved', [
            'customer_id' => $customer->id,
            'telegram_id' => $customer->telegram_id,
            'command_name' => $command_name,
            'params' => $params,
            'command_class' => $command_class,
            'has_callback_query' => (bool) $update->getCallbackQuery(),
            'has_message' => (bool) $update->getMessage(),
        ]);

        $command = new $command_class($update, $customer, $params);

        try {
            $command->handle();
        } catch (\Exception $ex) {
            Log::error('Telegram command failed', [
                'customer_id' => $customer->id,
                'telegram_id' => $customer->telegram_id,
                'command_name' => $command_name,
                'params' => $params,
                'command_class' => $command_class,
                'message' => $ex->getMessage(),
                'exception_class' => $ex::class,
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
            ]);

            return;
        }
    }
}
