<?php

namespace App\Telegram;

use Telegram\Bot\Objects\Update;

class TelegramManager
{
    public static function extractTelegramId(Update $update): ?string
    {
        if ($callback = $update->getCallbackQuery()) {
            return $callback->getFrom()->getId();
        } elseif ($preCheckout = $update->getPreCheckoutQuery()) {
            return $preCheckout->getFrom()->getId();
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
        if (! is_string($message_data) || $message_data === '') {
            return [
                'command_name' => '',
                'params' => [],
            ];
        }

        $message_data = trim($message_data);

        if (str_contains($message_data, ' ')) {
            [$command_name, $payload] = explode(' ', $message_data, 2);

            return [
                'command_name' => $command_name,
                'params' => self::parsePayloadString($payload),
            ];
        }

        $parts = explode('?', $message_data, 2);
        if (count($parts) < 2) {
            return [
                'command_name' => $parts[0],
                'params' => [],
            ];
        }
        [$command_name, $param_string] = $parts;
        $params = [];
        foreach (explode('&', $param_string) as $param) {
            $key_value = explode('=', $param, 2);
            if (count($key_value) < 2) {
                continue;
            }
            [$key, $value] = $key_value;
            $params[$key] = $value;
        }

        return [
            'command_name' => $command_name,
            'params' => $params,
        ];
    }

    public static function extractStartPayload(Update $update): ?string
    {
        $messageData = self::getMessageData($update);

        if (! is_string($messageData)) {
            return null;
        }

        $messageData = trim($messageData);

        if (! str_starts_with($messageData, '/start ')) {
            return null;
        }

        [, $payload] = explode(' ', $messageData, 2);

        return $payload !== '' ? trim($payload) : null;
    }

    public static function getMessageId(Update $update): ?string
    {
        try {
            return $update->getCallbackQuery()->getMessage()->getMessageId();
        } catch (\Exception $e) {
            return null;
        }
    }

    private static function parsePayloadString(string $payload): array
    {
        $payload = trim($payload);

        if ($payload === '') {
            return [];
        }

        if (! str_contains($payload, '=')) {
            return ['payload' => $payload];
        }

        $params = [];

        foreach (explode('&', $payload) as $param) {
            $keyValue = explode('=', $param, 2);

            if (count($keyValue) < 2) {
                continue;
            }

            [$key, $value] = $keyValue;
            $params[$key] = $value;
        }

        return $params;
    }
}
