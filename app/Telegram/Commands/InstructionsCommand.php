<?php

namespace App\Telegram\Commands;

use App\Models\TelegramCommandLog;
use App\Services\CustomerInstructionService;
use App\Telegram\TelegramKeyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class InstructionsCommand extends BaseCommand
{
    public function handle(): void
    {
        TelegramCommandLog::create([
            'customer_id' => $this->customer->id,
            'command_name' => 'Вызвал команду /instructions',
            'action' => 'Вызвал команду /instructions',
        ]);

        $this->sendInstructions();
    }

    private function sendInstructions(): void
    {
        $instructionService = new CustomerInstructionService;

        Telegram::sendMessage([
            'chat_id' => $this->customer->telegram_id,
            'text' => $instructionService->getInstructions($this->params['type'] ?? null),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => $this->getInstructionsKeyboard($instructionService),
        ]);
    }

    private function getInstructionsKeyboard(CustomerInstructionService $instructionService): string
    {
        return match ($instructionService->normalizeType($this->params['type'] ?? null)) {
            CustomerInstructionService::TYPE_ANDROID => TelegramKeyboard::inline([
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            CustomerInstructionService::TYPE_IPHONE => TelegramKeyboard::inline([
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            CustomerInstructionService::TYPE_WINDOWS => TelegramKeyboard::inline([
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            CustomerInstructionService::TYPE_MACOS => TelegramKeyboard::inline([
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
            default => TelegramKeyboard::inline([
                [['text' => '🤖 Android', 'callback_data' => '/instructions?type=android']],
                [['text' => '🍎 iPhone', 'callback_data' => '/instructions?type=iphone']],
                [['text' => '🪟 Windows', 'callback_data' => '/instructions?type=windows']],
                [['text' => '🖥️ macOS', 'callback_data' => '/instructions?type=macos']],
                [['text' => '⬅️ Назад', 'callback_data' => '/start']],
            ]),
        };
    }
}
