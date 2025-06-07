<?php

namespace App\Telegram;

use Telegram\Bot\Objects\Update;

class TelegramManager
{
    public static function extractTelegramId(Update $update): ?string
    {
        if ($callback = $update->getCallbackQuery()) {
            return $callback->getFrom()->getId();
        } elseif ($message = $update->getMessage()) {
            try {
                return $message->getFrom()->getId();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public static function getMessageData(Update $update): ?string
    {
        if ($callback = $update->getCallbackQuery()) {
            return $callback->getData();
        } elseif ($message = $update->getMessage()) {
            try {
                return $message->getText();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public static function parseMessageData(Update $update): array
    {
        $message_data = self::getMessageData($update);
        $parts = explode(' ', $message_data);
        $command_name = $parts[0];
        $params = array_slice($parts, 1);

        return [
            'command_name' => $command_name,
            'params' => $params,
        ];
    }

    public static function getMessageId(Update $update): ?string
    {
        try {
            return $update->getCallbackQuery()->getMessage()->getMessageId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
