@php($title = 'Поддержка')
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h1 class="text-3xl font-semibold text-white">Поддержка</h1>
            <p class="mt-3 text-slate-300">Опишите проблему одним сообщением. Мы создадим тикет, а ответы поддержки появятся здесь.</p>

            <form method="POST" action="{{ route('customer.support.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="message" class="mb-1 block text-sm font-medium text-slate-300">Сообщение</label>
                    <textarea id="message" name="message" rows="8" required class="w-full rounded-2xl border border-slate-700 bg-slate-800 px-4 py-3 text-white">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-2xl bg-indigo-500 px-4 py-3 font-medium text-white">Отправить обращение</button>
            </form>
        </section>

        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold text-white">Последние тикеты</h2>
            <div class="mt-5 space-y-4">
                @forelse ($tickets as $ticket)
                    @php
                        $statusClasses = [
                            'new' => 'bg-amber-500/15 text-amber-200 border border-amber-500/20',
                            'answered' => 'bg-emerald-500/15 text-emerald-200 border border-emerald-500/20',
                            'closed' => 'bg-slate-700 text-slate-200 border border-slate-600',
                        ];
                        $statusLabels = [
                            'new' => 'Новый',
                            'answered' => 'Есть ответ',
                            'closed' => 'Закрыт',
                        ];
                    @endphp

                    <article class="rounded-2xl bg-slate-800 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm text-slate-400">Тикет #{{ $ticket->id }} от {{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                                <p class="mt-2 whitespace-pre-line text-slate-100">{{ $ticket->message }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$ticket->status] ?? $statusClasses['new'] }}">
                                {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                            </span>
                        </div>

                        @if ($ticket->replies->isNotEmpty())
                            <div class="mt-4 space-y-3 border-t border-slate-700 pt-4">
                                <p class="text-sm font-medium text-slate-300">Ответы поддержки</p>
                                @foreach ($ticket->replies as $reply)
                                    <div class="rounded-2xl border border-slate-700 bg-slate-900/70 p-4">
                                        <div class="flex flex-col gap-1 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                                            <span>{{ $reply->user?->name ?: 'Поддержка' }}</span>
                                            <span>{{ $reply->created_at?->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-line text-slate-100">{{ $reply->message }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl bg-slate-800 p-4 text-slate-300">
                        Обращений пока не было.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
