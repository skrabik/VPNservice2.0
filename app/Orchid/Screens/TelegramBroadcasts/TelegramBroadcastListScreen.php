<?php

declare(strict_types=1);

namespace App\Orchid\Screens\TelegramBroadcasts;

use App\Models\TelegramBroadcast;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TelegramBroadcastListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'broadcasts' => TelegramBroadcast::with('createdBy')
                ->latest()
                ->paginate(25),
        ];
    }

    public function name(): ?string
    {
        return 'Telegram рассылки';
    }

    public function description(): ?string
    {
        return 'История массовых и тестовых рассылок Telegram-бота.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.telegram-broadcasts',
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать рассылку')
                ->icon('bs.plus-circle')
                ->route('platform.telegram-broadcasts.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('broadcasts', [
                TD::make('id', 'ID')
                    ->sort()
                    ->render(fn (TelegramBroadcast $broadcast) => Link::make("#{$broadcast->id}")
                        ->route('platform.telegram-broadcasts.edit', $broadcast)),

                TD::make('status', 'Статус')
                    ->sort()
                    ->render(fn (TelegramBroadcast $broadcast) => $this->renderStatus($broadcast)),

                TD::make('is_test', 'Режим')
                    ->sort()
                    ->render(fn (TelegramBroadcast $broadcast) => $broadcast->is_test ? 'Тестовая' : 'Массовая'),

                TD::make('target_count', 'Получателей')
                    ->sort(),

                TD::make('success_count', 'Успешно')
                    ->sort(),

                TD::make('failed_count', 'Ошибок')
                    ->sort(),

                TD::make('created_by', 'Создал')
                    ->render(fn (TelegramBroadcast $broadcast) => $broadcast->createdBy?->name ?? 'System'),

                TD::make('created_at', 'Создана')
                    ->sort()
                    ->render(fn (TelegramBroadcast $broadcast) => $broadcast->created_at?->format('d.m.Y H:i') ?? '-'),

                TD::make('finished_at', 'Завершена')
                    ->sort()
                    ->render(fn (TelegramBroadcast $broadcast) => $broadcast->finished_at?->format('d.m.Y H:i') ?? '-'),
            ]),
        ];
    }

    private function renderStatus(TelegramBroadcast $broadcast): string
    {
        return match ($broadcast->status) {
            TelegramBroadcast::STATUS_DRAFT => 'Черновик',
            TelegramBroadcast::STATUS_QUEUED => 'В очереди',
            TelegramBroadcast::STATUS_SENDING => 'Отправляется',
            TelegramBroadcast::STATUS_COMPLETED => 'Завершена',
            TelegramBroadcast::STATUS_FAILED => 'Ошибка',
            default => $broadcast->status,
        };
    }
}
