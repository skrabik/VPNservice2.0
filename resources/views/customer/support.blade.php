@php
    $title = 'Поддержка';
@endphp
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="customer-panel customer-panel-hero rounded-3xl p-6">
            <h1 class="customer-page-title text-3xl font-semibold">Поддержка</h1>
            <p class="customer-page-text mt-3">Опишите проблему одним сообщением. Мы создадим тикет, а ответы поддержки появятся здесь.</p>

            <form method="POST" action="{{ route('customer.support.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="message" class="customer-field-label mb-1 block text-sm font-medium">Сообщение</label>
                    <textarea id="message" name="message" rows="8" required class="customer-textarea rounded-2xl px-4 py-3">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="customer-button-primary w-full rounded-2xl px-4 py-3 font-medium">Отправить обращение</button>
            </form>
        </section>

        <section class="customer-panel rounded-3xl p-6">
            <h2 class="customer-page-title text-xl font-semibold">Последние тикеты</h2>
            <div class="mt-5 space-y-4">
                @forelse ($tickets as $ticket)
                    @php
                        $statusClasses = [
                            'new' => 'customer-status-badge-new',
                            'answered' => 'customer-status-badge-answered',
                            'closed' => 'customer-status-badge-closed',
                        ];
                        $statusLabels = [
                            'new' => 'Новый',
                            'answered' => 'Есть ответ',
                            'closed' => 'Закрыт',
                        ];
                    @endphp

                    <article class="customer-metric rounded-2xl p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="customer-soft-text text-sm">Тикет #{{ $ticket->id }} от {{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                                <p class="mt-2 whitespace-pre-line text-white">{{ $ticket->message }}</p>
                            </div>
                            <span class="customer-status-badge inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$ticket->status] ?? $statusClasses['new'] }}">
                                {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                            </span>
                        </div>

                        @if ($ticket->replies->isNotEmpty())
                            <div class="mt-4 space-y-3 border-t border-white/10 pt-4">
                                <p class="customer-page-text text-sm font-medium">Ответы поддержки</p>
                                @foreach ($ticket->replies as $reply)
                                    <div class="customer-ticket-reply rounded-2xl p-4">
                                        <div class="customer-soft-text flex flex-col gap-1 text-sm sm:flex-row sm:items-center sm:justify-between">
                                            <span>{{ $reply->user?->name ?: 'Поддержка' }}</span>
                                            <span>{{ $reply->created_at?->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-line text-white">{{ $reply->message }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="customer-empty-state rounded-2xl p-4">
                        Обращений пока не было.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
