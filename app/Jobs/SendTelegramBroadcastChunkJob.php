<?php

namespace App\Jobs;

use App\Models\TelegramBroadcast;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendTelegramBroadcastChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<string>  $chatIds
     */
    public function __construct(
        private readonly int $broadcastId,
        private readonly array $chatIds,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $broadcast = TelegramBroadcast::query()->find($this->broadcastId);

        if (! $broadcast) {
            return;
        }

        foreach ($this->chatIds as $chatId) {
            try {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $broadcast->message,
                    'parse_mode' => 'HTML',
                ]);

                TelegramBroadcast::query()
                    ->whereKey($broadcast->id)
                    ->increment('success_count');
            } catch (\Throwable $exception) {
                Log::warning('Telegram broadcast delivery failed', [
                    'broadcast_id' => $broadcast->id,
                    'chat_id' => $chatId,
                    'message' => $exception->getMessage(),
                ]);

                TelegramBroadcast::query()
                    ->whereKey($broadcast->id)
                    ->increment('failed_count');
            }
        }
    }
}
