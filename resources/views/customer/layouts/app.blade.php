<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Личный кабинет' }}</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <header class="border-b border-slate-800 bg-slate-900/95">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <div>
                    <a href="{{ route('customer.dashboard') }}" class="text-lg font-semibold text-white">Quantum Shield</a>
                    <p class="text-sm text-slate-400">Пользовательский веб-кабинет</p>
                </div>
                <div class="text-right">
                    <p class="font-medium text-white">{{ auth('customer')->user()->first_name }}</p>
                    <p class="text-sm text-slate-400">{{ auth('customer')->user()->email ?: 'Telegram-only аккаунт' }}</p>
                </div>
            </div>
            <nav class="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 pb-4">
                <a href="{{ route('customer.dashboard') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.dashboard') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">Главная</a>
                <a href="{{ route('customer.status') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.status') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">Подписка</a>
                <a href="{{ route('customer.keys') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.keys') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">VPN-ключи</a>
                <a href="{{ route('customer.instructions') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.instructions') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">Инструкции</a>
                <a href="{{ route('customer.support') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.support') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">Поддержка</a>
                <a href="{{ route('customer.pay') }}" class="rounded-full px-4 py-2 text-sm {{ request()->routeIs('customer.pay') ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">Оплата</a>
                <form method="POST" action="{{ route('customer.logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="rounded-full bg-slate-800 px-4 py-2 text-sm text-slate-200">Выйти</button>
                </form>
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                    <ul class="space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const webApp = window.Telegram?.WebApp;

                if (!webApp) {
                    return;
                }

                webApp.ready();
                webApp.expand();
            });
        </script>
    </body>
</html>
