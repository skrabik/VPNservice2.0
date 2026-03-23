@php($title = 'VPN-ключи')
@extends('customer.layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h1 class="text-3xl font-semibold text-white">VPN-ключи</h1>
            <p class="mt-3 text-slate-300">Выберите сервер и выпустите новый ключ. Старые ключи будут заменены автоматически.</p>

            @if ($currentKey)
                <div class="mt-6 rounded-2xl bg-slate-800 p-5">
                    <p class="text-sm text-slate-400">Текущий ключ</p>
                    <p class="mt-2 text-sm text-slate-300">Сервер: {{ $currentKey->server?->hostname ?? 'Неизвестный сервер' }}</p>
                    <div class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 font-mono text-sm text-emerald-300">
                        {{ $currentKey->access_key }}
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-slate-700 bg-slate-800 p-5 text-slate-300">
                    Активного ключа пока нет.
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold text-white">Создать новый ключ</h2>

            @if (! $hasActiveSubscription)
                <div class="mt-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-100">
                    Ключи доступны только при активной подписке.
                </div>
            @elseif ($availableServers->isEmpty())
                <div class="mt-5 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-100">
                    Сейчас нет активных серверов. Попробуйте позже.
                </div>
            @else
                <form method="POST" action="{{ route('customer.keys.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="server_id" class="mb-1 block text-sm font-medium text-slate-300">Сервер</label>
                        <select id="server_id" name="server_id" class="w-full rounded-2xl border border-slate-700 bg-slate-800 px-4 py-3 text-white">
                            @foreach ($availableServers as $server)
                                <option value="{{ $server->id }}">{{ $server->hostname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-indigo-500 px-4 py-3 font-medium text-white">Создать новый ключ</button>
                </form>
            @endif
        </section>
    </div>
@endsection
