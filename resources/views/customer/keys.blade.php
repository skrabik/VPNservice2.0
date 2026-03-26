@php
    $title = 'VPN-ключи';
@endphp
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="customer-panel customer-panel-hero rounded-3xl p-6">
            <h1 class="customer-page-title text-3xl font-semibold">VPN-ключи</h1>
            <p class="customer-page-text mt-3">Выберите сервер и выпустите новый ключ. Старые ключи будут заменены автоматически.</p>

            @if ($currentKey)
                <div class="customer-metric mt-6 rounded-2xl p-5">
                    <p class="customer-metric-label text-sm">Текущий ключ</p>
                    <p class="customer-page-text mt-2 text-sm">Сервер: {{ $currentKey->server?->hostname ?? 'Неизвестный сервер' }}</p>
                    <x-vpn-key-display :value="$currentKey->access_key" />
                </div>
            @else
                <div class="customer-empty-state mt-6 rounded-2xl p-5">
                    Активного ключа пока нет.
                </div>
            @endif
        </section>

        <section class="customer-panel rounded-3xl p-6">
            <h2 class="customer-page-title text-xl font-semibold">Создать новый ключ</h2>

            @if (! $hasActiveSubscription)
                <div class="customer-alert customer-alert-warning mt-5 rounded-2xl p-5">
                    Ключи доступны только при активной подписке.
                </div>
            @elseif ($availableServers->isEmpty())
                <div class="customer-alert customer-alert-warning mt-5 rounded-2xl p-5">
                    Сейчас нет активных серверов. Попробуйте позже.
                </div>
            @else
                <form method="POST" action="{{ route('customer.keys.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="server_id" class="customer-field-label mb-1 block text-sm font-medium">Сервер</label>
                        <select id="server_id" name="server_id" class="customer-select rounded-2xl px-4 py-3">
                            @foreach ($availableServers as $server)
                                <option value="{{ $server->id }}">{{ $server->hostname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="customer-button-primary w-full rounded-2xl px-4 py-3 font-medium">Создать новый ключ</button>
                </form>
            @endif
        </section>
    </div>
@endsection
