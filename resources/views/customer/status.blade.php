@php($title = 'Статус подписки')
@extends('customer.layouts.app')

@section('content')
    <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
        <h1 class="text-3xl font-semibold text-white">Статус подписки</h1>

        @if (! $overview['has_active_subscription'])
            <div class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-100">
                Активной подписки сейчас нет. Для оплаты откройте Telegram-бота или дождитесь запуска веб-оплаты.
            </div>
        @endif

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl bg-slate-800 p-5">
                <p class="text-sm text-slate-400">Статус</p>
                <p class="mt-2 text-xl font-semibold">{{ $overview['status_icon'] }} {{ $overview['status_text'] }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800 p-5">
                <p class="text-sm text-slate-400">Осталось</p>
                <p class="mt-2 text-xl font-semibold">{{ $overview['days_left'] }} дн. {{ $overview['hours_left'] }} ч.</p>
            </div>
            <div class="rounded-2xl bg-slate-800 p-5">
                <p class="text-sm text-slate-400">Активные ключи</p>
                <p class="mt-2 text-xl font-semibold">{{ $overview['active_keys_count'] }}</p>
            </div>
        </div>

        @if ($overview['subscription'])
            <div class="mt-6 rounded-2xl bg-slate-800 p-6 text-slate-200">
                <div class="grid gap-3 md:grid-cols-2">
                    <p>Тариф: <span class="font-medium text-white">{{ $overview['subscription']->plan?->title ?? 'Не указан' }}</span></p>
                    <p>Стоимость: <span class="font-medium text-white">{{ $overview['subscription']->plan?->price ?? 0 }}₽</span></p>
                    <p>Дата начала: <span class="font-medium text-white">{{ $overview['subscription']->date_start?->format('d.m.Y H:i') }}</span></p>
                    <p>Дата окончания: <span class="font-medium text-white">{{ $overview['subscription']->date_end?->format('d.m.Y H:i') }}</span></p>
                </div>
            </div>
        @endif
    </section>
@endsection
