<?php

namespace App\Telegram\Services;

use App\Models\Customer;
use App\Telegram\Commands;
use App\Telegram\TelegramManager;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Objects\Update;

class CommandService
{
    public static function process(Update $update, Customer $customer): void
    {
        extract(TelegramManager::parseMessageData($update));

        $commandClass = self::getCommandClass($command_name);

        $command = new $commandClass($update, $customer, $params);

        try {
            $command->handle();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return;
        }
    }

    private static function getCommandClass($command_name): string
    {
        return match ($command_name) {
            '/start' => Commands\StartCommand::class,
            default => Commands\StartCommand::class,
        };
    }
}
