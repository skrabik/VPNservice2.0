<?php

declare(strict_types=1);

namespace App\Orchid\Screens\TelegramBroadcasts;

use App\Models\TelegramBroadcast;
use App\Orchid\Layouts\TelegramBroadcast\TelegramBroadcastEditLayout;
use App\Services\TelegramBroadcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TelegramBroadcastEditScreen extends Screen
{
    public TelegramBroadcast $broadcast;

    public function query(TelegramBroadcast $broadcast): iterable
    {
        $broadcast->loadMissing('createdBy');

        return [
            'broadcast' => $broadcast,
        ];
    }

    public function name(): ?string
    {
        return $this->broadcast->exists
            ? "Рассылка #{$this->broadcast->id}"
            : 'Создать рассылку';
    }

    public function description(): ?string
    {
        if (! $this->broadcast->exists) {
            return 'Сохраните черновик, затем отправьте его в очередь.';
        }

        $mode = $this->broadcast->is_test ? 'Тестовая' : 'Массовая';

        return sprintf(
            '%s рассылка. Статус: %s. Получателей: %d. Успешно: %d. Ошибок: %d.',
            $mode,
            $this->renderStatus($this->broadcast),
            $this->broadcast->target_count,
            $this->broadcast->success_count,
            $this->broadcast->failed_count,
        );
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
            Link::make('Назад к списку')
                ->icon('bs.arrow-left')
                ->route('platform.telegram-broadcasts'),

            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save')
                ->canSee(! $this->broadcast->exists || $this->broadcast->isDraft()),

            Button::make('Отправить')
                ->icon('bs.send')
                ->type(Color::BASIC)
                ->method('queueBroadcast')
                ->confirm('Поставить эту рассылку в очередь на отправку?')
                ->canSee($this->broadcast->exists && $this->broadcast->isDraft()),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block(TelegramBroadcastEditLayout::class)
                ->title('Параметры рассылки')
                ->description('Telegram получит текст как HTML. После отправки рассылка останется в истории и больше не должна редактироваться.')
                ->commands(
                    Button::make('Сохранить')
                        ->type(Color::BASIC)
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee(! $this->broadcast->exists || $this->broadcast->isDraft())
                ),
        ];
    }

    public function save(TelegramBroadcast $broadcast, Request $request): RedirectResponse
    {
        if ($broadcast->exists && ! $broadcast->isDraft()) {
            Toast::warning('Отправленную рассылку нельзя изменить.');

            return redirect()->route('platform.telegram-broadcasts.edit', $broadcast);
        }

        $validated = $request->validate([
            'broadcast.message' => ['required', 'string', 'max:4096'],
            'broadcast.is_test' => ['nullable', 'boolean'],
        ]);

        $broadcast->fill([
            'message' => $validated['broadcast']['message'],
            'is_test' => (bool) ($validated['broadcast']['is_test'] ?? false),
        ]);

        if (! $broadcast->exists) {
            $broadcast->status = TelegramBroadcast::STATUS_DRAFT;
            $broadcast->created_by = $request->user()?->id;
        }

        $broadcast->save();

        Toast::info('Рассылка сохранена.');

        return redirect()->route('platform.telegram-broadcasts.edit', $broadcast);
    }

    public function queueBroadcast(
        TelegramBroadcast $broadcast,
        TelegramBroadcastService $broadcastService,
    ): RedirectResponse {
        $broadcastService->queue($broadcast);

        Toast::info('Рассылка поставлена в очередь.');

        return redirect()->route('platform.telegram-broadcasts.edit', $broadcast);
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
