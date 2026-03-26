@php
    $title = 'Статус подписки';
@endphp
@extends('customer.layouts.app')

@section('content')
    <section class="customer-panel customer-panel-hero rounded-3xl p-6">
        <h1 class="customer-page-title text-3xl font-semibold">Статус подписки</h1>

        @if (! $overview['has_active_subscription'])
            <div class="customer-alert customer-alert-warning mt-6 rounded-2xl p-5">
                <p class="font-medium text-white">
                    {{ $overview['subscription'] ? 'Подписка закончилась.' : 'Активной подписки сейчас нет.' }}
                </p>
                <p class="mt-2 max-w-2xl text-sm">
                    {{ $overview['subscription']
                        ? 'Можно продлить доступ онлайн или, если вам так удобнее, через Telegram-бота.'
                        : 'Оформить доступ можно онлайн или через Telegram-бота, когда вам будет удобно.' }}
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

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="customer-metric rounded-2xl p-5">
                <p class="customer-metric-label text-sm">Статус</p>
                <p class="customer-metric-value mt-2 text-xl font-semibold">{{ $overview['status_icon'] }} {{ $overview['status_text'] }}</p>
            </div>
            <div class="customer-metric rounded-2xl p-5">
                <p class="customer-metric-label text-sm">Осталось</p>
                <p class="customer-metric-value mt-2 text-xl font-semibold">
                    {{ $overview['has_active_subscription'] ? $overview['days_left'].' дн. '.$overview['hours_left'].' ч.' : 'Подписка не активна' }}
                </p>
            </div>
            <div class="customer-metric rounded-2xl p-5">
                <p class="customer-metric-label text-sm">Активные ключи</p>
                <p class="customer-metric-value mt-2 text-xl font-semibold">{{ $overview['active_keys_count'] }}</p>
            </div>
        </div>

        @if ($overview['subscription'])
            <div class="customer-metric mt-6 rounded-2xl p-6">
                <div class="grid gap-3 md:grid-cols-2">
                    <p>Тариф: <span class="font-medium text-white">{{ $overview['subscription']->plan?->title ?? 'Не указан' }}</span></p>
                    <p>Стоимость: <span class="font-medium text-white">{{ $overview['subscription']->plan?->price ?? 0 }}₽</span></p>
                    <p>Дата начала: <span class="font-medium text-white">{{ $overview['subscription']->date_start?->format('d.m.Y H:i') }}</span></p>
                    <p>Дата окончания: <span class="font-medium text-white">{{ $overview['subscription']->date_end?->format('d.m.Y H:i') }}</span></p>
                </div>
            </div>
        @else
            <div class="customer-empty-state mt-6 rounded-2xl p-6">
                После оформления подписки здесь появятся срок действия и данные по тарифу.
            </div>
        @endif
    </section>
@endsection
