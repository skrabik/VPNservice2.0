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

        $command = new $command_class($update, $customer, $params);

        try {
            $command->handle();
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());

            return;
        }
    }
}
