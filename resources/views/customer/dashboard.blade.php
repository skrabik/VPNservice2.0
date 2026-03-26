@php
    $title = 'Личный кабинет';
@endphp
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <p class="text-sm uppercase tracking-[0.3em] text-indigo-300">Обзор</p>
            <h1 class="mt-3 text-3xl font-semibold text-white">Здравствуйте, {{ $customer->first_name }}.</h1>
            <p class="mt-3 max-w-2xl text-slate-300">
                Здесь собраны все основные действия из Telegram-бота: проверка подписки, получение ключей, инструкции и поддержка.
            </p>

            @if (! $overview['has_active_subscription'])
                <div class="mt-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-100">
                    <p class="font-medium text-white">
                        {{ $overview['subscription'] ? 'Срок вашей подписки закончился.' : 'Подписка пока не оформлена.' }}
                    </p>
                    <p class="mt-2 max-w-2xl text-sm text-amber-50/90">
                        {{ $overview['subscription']
                            ? 'Когда будете готовы, можно спокойно продлить доступ онлайн или через Telegram-бота.'
                            : 'Когда будете готовы, можно оформить доступ онлайн или через Telegram-бота.' }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('customer.pay') }}" class="inline-flex rounded-2xl bg-indigo-500 px-4 py-3 font-medium text-white hover:bg-indigo-400">
                            {{ $overview['subscription'] ? 'Продлить подписку' : 'Оформить подписку' }}
                        </a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="inline-flex rounded-2xl border border-slate-600 px-4 py-3 font-medium text-slate-100 hover:bg-slate-800">
                            Открыть Telegram-бота
                        </a>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-slate-800 p-5">
                    <p class="text-sm text-slate-400">Статус</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $overview['status_icon'] }} {{ $overview['status_text'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-800 p-5">
                    <p class="text-sm text-slate-400">Активные ключи</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $overview['active_keys_count'] }}</p>
                </div>
                <div class="rounded-2xl bg-slate-800 p-5">
                    <p class="text-sm text-slate-400">Осталось</p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ $overview['has_active_subscription'] ? $overview['days_left'].' дн. '.$overview['hours_left'].' ч.' : 'Нет подписки' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold text-white">Быстрые действия</h2>
            <div class="mt-5 space-y-3">
                @if (! $overview['has_active_subscription'])
                    <a href="{{ route('customer.pay') }}" class="block rounded-2xl bg-indigo-500 px-4 py-4 text-center font-medium text-white">
                        {{ $overview['subscription'] ? 'Продлить подписку' : 'Оформить подписку' }}
                    </a>
                @endif
                <a href="{{ route('customer.keys') }}" class="block rounded-2xl bg-slate-800 px-4 py-4 text-slate-100">Получить или перевыпустить VPN-ключ</a>
                <a href="{{ route('customer.instructions') }}" class="block rounded-2xl bg-slate-800 px-4 py-4 text-slate-100">Открыть инструкции по подключению</a>
                <a href="{{ route('customer.support') }}" class="block rounded-2xl bg-slate-800 px-4 py-4 text-slate-100">Написать в поддержку</a>
                <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="block rounded-2xl bg-indigo-500 px-4 py-4 text-center font-medium text-white">Открыть Telegram-бота</a>
            </div>
        </section>
    </div>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold text-white">Текущая подписка</h2>
            @if ($overview['subscription'])
                <div class="mt-4 space-y-2 text-slate-300">
                    <p>Тариф: <span class="font-medium text-white">{{ $overview['subscription']->plan?->title ?? 'Без тарифа' }}</span></p>
                    <p>Начало: <span class="font-medium text-white">{{ $overview['subscription']->date_start?->format('d.m.Y H:i') }}</span></p>
                    <p>Окончание: <span class="font-medium text-white">{{ $overview['subscription']->date_end?->format('d.m.Y H:i') }}</span></p>
                </div>
            @else
                <div class="mt-4 rounded-2xl border border-slate-700 bg-slate-800 p-5 text-slate-300">
                    <p>Активная подписка пока не найдена.</p>
                    <p class="mt-2 text-sm text-slate-400">При желании ее можно оформить на странице оплаты или через Telegram-бота.</p>
                </div>
            @endif
        </div>

        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold text-white">Текущий ключ</h2>
            @if ($overview['current_key'])
                <p class="mt-4 text-sm text-slate-400">Сервер: {{ $overview['current_key']->server?->hostname ?? 'Неизвестный сервер' }}</p>
                <x-vpn-key-display :value="$overview['current_key']->access_key" />
            @else
                <p class="mt-4 text-slate-300">Активного ключа пока нет. Создайте его на странице VPN-ключей.</p>
            @endif
        </div>
    </section>
@endsection
