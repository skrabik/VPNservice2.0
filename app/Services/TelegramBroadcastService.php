<?php

namespace App\Services;

use App\Jobs\SendTelegramBroadcastChunkJob;
use App\Models\Customer;
use App\Models\TelegramBroadcast;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class TelegramBroadcastService
{
    private const CHUNK_SIZE = 50;

    /**
     * @throws ValidationException
     */
    public function queue(TelegramBroadcast $broadcast): TelegramBroadcast
    {
        if (! $broadcast->exists) {
            throw ValidationException::withMessages([
                'broadcast' => 'Сначала сохраните рассылку.',
            ]);
        }

        if (! $broadcast->isDraft()) {
            throw ValidationException::withMessages([
                'broadcast' => 'В очередь можно поставить только черновик рассылки.',
            ]);
        }

        $chatIds = $this->resolveTargetChatIds($broadcast);

        $broadcast->forceFill([
            'status' => TelegramBroadcast::STATUS_QUEUED,
            'target_count' => count($chatIds),
            'success_count' => 0,
            'failed_count' => 0,
            'batch_id' => null,
            'queued_at' => now(),
            'started_at' => $chatIds === [] ? null : now(),
            'finished_at' => null,
        ])->save();

        if ($chatIds === []) {
            $broadcast->forceFill([
                'status' => TelegramBroadcast::STATUS_COMPLETED,
                'finished_at' => now(),
            ])->save();

            return $broadcast->fresh();
        }

        $jobs = collect(array_chunk($chatIds, self::CHUNK_SIZE))
            ->map(fn (array $chunk) => new SendTelegramBroadcastChunkJob($broadcast->id, $chunk))
            ->all();

        $broadcastId = $broadcast->id;

        $batch = Bus::batch($jobs)
            ->name("telegram_broadcast_{$broadcastId}")
            ->then(function (Batch $batch) use ($broadcastId): void {
                TelegramBroadcast::query()
                    ->whereKey($broadcastId)
                    ->update([
                        'status' => TelegramBroadcast::STATUS_COMPLETED,
                        'finished_at' => now(),
                    ]);
            })
            ->catch(function (Batch $batch, Throwable $exception) use ($broadcastId): void {
                Log::error('Telegram broadcast batch failed', [
                    'broadcast_id' => $broadcastId,
                    'batch_id' => $batch->id,
                    'message' => $exception->getMessage(),
                ]);

                TelegramBroadcast::query()
                    ->whereKey($broadcastId)
                    ->update([
                        'status' => TelegramBroadcast::STATUS_FAILED,
                        'finished_at' => now(),
                    ]);
            })
            ->dispatch();

        TelegramBroadcast::query()
            ->whereKey($broadcastId)
            ->update([
                'batch_id' => $batch->id,
            ]);

        TelegramBroadcast::query()
            ->whereKey($broadcastId)
            ->where('status', TelegramBroadcast::STATUS_QUEUED)
            ->update([
                'status' => TelegramBroadcast::STATUS_SENDING,
            ]);

        return $broadcast->fresh();
    }

    /**
     * @return list<string>
     *
     * @throws ValidationException
     */
    public function resolveTargetChatIds(TelegramBroadcast $broadcast): array
    {
        if ($broadcast->is_test) {
            $testChatId = (string) config('telegram.test_chat_id');

            if ($testChatId === '') {
                throw ValidationException::withMessages([
                    'broadcast.is_test' => 'Для тестовой рассылки заполните TELEGRAM_TEST_CHAT_ID в .env.',
                ]);
            }

            return [$testChatId];
        }

        return Customer::query()
            ->whereNotNull('telegram_id')
            ->where('telegram_id', '!=', '')
            ->orderBy('id')
            ->pluck('telegram_id')
            ->map(fn ($chatId): string => (string) $chatId)
            ->unique()
            ->values()
            ->all();
    }
}
