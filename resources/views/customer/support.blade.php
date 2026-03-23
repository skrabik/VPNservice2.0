@php($title = 'Поддержка')
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h1 class="text-3xl font-semibold text-white">Поддержка</h1>
            <p class="mt-3 text-slate-300">Опишите проблему, и мы создадим тикет так же, как это делает Telegram-бот.</p>

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
                    <article class="rounded-2xl bg-slate-800 p-4">
                        <p class="text-sm text-slate-400">{{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                        <p class="mt-2 whitespace-pre-line text-slate-100">{{ $ticket->message }}</p>
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
