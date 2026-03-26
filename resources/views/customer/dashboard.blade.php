@php
    $title = 'Личный кабинет';
@endphp
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <section class="customer-panel customer-panel-hero rounded-3xl p-6">
            <p class="customer-kicker text-sm">Обзор</p>
            <h1 class="customer-page-title mt-3 text-3xl font-semibold">Здравствуйте, {{ $customer->first_name }}.</h1>
            <p class="customer-page-text mt-3 max-w-2xl">
                Здесь собраны все основные действия из Telegram-бота: проверка подписки, получение ключей, инструкции и поддержка.
            </p>

            @if (! $overview['has_active_subscription'])
                <div class="customer-alert customer-alert-warning mt-6 rounded-2xl p-5">
                    <p class="font-medium text-white">
                        {{ $overview['subscription'] ? 'Срок вашей подписки закончился.' : 'Подписка пока не оформлена.' }}
                    </p>
                    <p class="mt-2 max-w-2xl text-sm">
                        {{ $overview['subscription']
                            ? 'Когда будете готовы, можно спокойно продлить доступ онлайн или через Telegram-бота.'
                            : 'Когда будете готовы, можно оформить доступ онлайн или через Telegram-бота.' }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('customer.pay') }}" class="customer-button-primary inline-flex rounded-2xl px-4 py-3 font-medium">
                            {{ $overview['subscription'] ? 'Продлить подписку' : 'Оформить подписку' }}
                        </a>
                        <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="customer-button-secondary inline-flex rounded-2xl px-4 py-3 font-medium">
                            Открыть Telegram-бота
                        </a>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="customer-metric rounded-2xl p-5">
                    <p class="customer-metric-label text-sm">Статус</p>
                    <p class="customer-metric-value mt-2 text-2xl font-semibold">{{ $overview['status_icon'] }} {{ $overview['status_text'] }}</p>
                </div>
                <div class="customer-metric rounded-2xl p-5">
                    <p class="customer-metric-label text-sm">Активные ключи</p>
                    <p class="customer-metric-value mt-2 text-2xl font-semibold">{{ $overview['active_keys_count'] }}</p>
                </div>
                <div class="customer-metric rounded-2xl p-5">
                    <p class="customer-metric-label text-sm">Осталось</p>
                    <p class="customer-metric-value mt-2 text-2xl font-semibold">
                        {{ $overview['has_active_subscription'] ? $overview['days_left'].' дн. '.$overview['hours_left'].' ч.' : 'Нет подписки' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="customer-panel rounded-3xl p-6">
            <h2 class="customer-page-title text-xl font-semibold">Быстрые действия</h2>
            <div class="mt-5 space-y-3">
                @if (! $overview['has_active_subscription'])
                    <a href="{{ route('customer.pay') }}" class="customer-button-primary block rounded-2xl px-4 py-4 text-center font-medium">
                        {{ $overview['subscription'] ? 'Продлить подписку' : 'Оформить подписку' }}
                    </a>
                @endif
                <a href="{{ route('customer.keys') }}" class="customer-button-secondary block rounded-2xl px-4 py-4">Получить или перевыпустить VPN-ключ</a>
                <a href="{{ route('customer.instructions') }}" class="customer-button-secondary block rounded-2xl px-4 py-4">Открыть инструкции по подключению</a>
                <a href="{{ route('customer.support') }}" class="customer-button-secondary block rounded-2xl px-4 py-4">Написать в поддержку</a>
                <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="customer-button-primary block rounded-2xl px-4 py-4 text-center font-medium">Открыть Telegram-бота</a>
            </div>
        </section>
    </div>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="customer-panel rounded-3xl p-6">
            <h2 class="customer-page-title text-xl font-semibold">Текущая подписка</h2>
            @if ($overview['subscription'])
                <div class="customer-page-text mt-4 space-y-2">
                    <p>Тариф: <span class="font-medium text-white">{{ $overview['subscription']->plan?->title ?? 'Без тарифа' }}</span></p>
                    <p>Начало: <span class="font-medium text-white">{{ $overview['subscription']->date_start?->format('d.m.Y H:i') }}</span></p>
                    <p>Окончание: <span class="font-medium text-white">{{ $overview['subscription']->date_end?->format('d.m.Y H:i') }}</span></p>
                </div>
            @else
                <div class="customer-empty-state mt-4 rounded-2xl p-5">
                    <p>Активная подписка пока не найдена.</p>
                    <p class="customer-soft-text mt-2 text-sm">При желании ее можно оформить на странице оплаты или через Telegram-бота.</p>
                </div>
            @endif
        </div>

        <div class="customer-panel rounded-3xl p-6">
            <h2 class="customer-page-title text-xl font-semibold">Текущий ключ</h2>
            @if ($overview['current_key'])
                <p class="customer-soft-text mt-4 text-sm">Сервер: {{ $overview['current_key']->server?->hostname ?? 'Неизвестный сервер' }}</p>
                <x-vpn-key-display :value="$overview['current_key']->access_key" />
            @else
                <p class="customer-page-text mt-4">Активного ключа пока нет. Создайте его на странице VPN-ключей.</p>
            @endif
        </div>
    </section>
@endsection
