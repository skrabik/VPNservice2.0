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
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('customer.dashboard') }}" class="text-lg font-semibold text-white">Quantum Shield</a>
                    <p class="text-sm text-slate-400">Пользовательский веб-кабинет</p>
                </div>
                <div class="min-w-0 sm:text-right">
                    <p class="font-medium text-white">{{ auth('customer')->user()->first_name }}</p>
                    <p class="truncate text-sm text-slate-400">{{ auth('customer')->user()->email ?: 'Telegram-only аккаунт' }}</p>
                </div>
            </div>
            <nav class="mx-auto grid max-w-6xl grid-cols-2 gap-2 px-4 pb-4 sm:grid-cols-3 lg:flex lg:flex-wrap">
                <a href="{{ route('customer.dashboard') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.dashboard') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">Главная</a>
                <a href="{{ route('customer.status') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.status') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">Подписка</a>
                <a href="{{ route('customer.keys') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.keys') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">VPN-ключи</a>
                <a href="{{ route('customer.instructions') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.instructions') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">Инструкции</a>
                <a href="{{ route('customer.support') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.support') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">Поддержка</a>
                <a href="{{ route('customer.pay') }}" class="flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm transition {{ request()->routeIs('customer.pay') ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-slate-800 text-slate-200 hover:bg-slate-700' }}">Оплата</a>
                <form method="POST" action="{{ route('customer.logout') }}" class="col-span-2 sm:col-span-3 lg:ml-auto">
                    @csrf
                    <button type="submit" class="flex min-h-11 w-full items-center justify-center rounded-2xl bg-slate-800 px-4 py-2.5 text-center text-sm text-slate-200 transition hover:bg-slate-700 lg:w-auto">Выйти</button>
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
                    bindCopyButtons();
                    return;
                }

                webApp.ready();
                webApp.expand();
                bindCopyButtons();
            });

            function bindCopyButtons() {
                const fallbackCopy = (text) => {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.setAttribute('readonly', '');
                    textArea.style.position = 'absolute';
                    textArea.style.left = '-9999px';
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                };

                const copyText = async (text) => {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(text);
                        return;
                    }

                    fallbackCopy(text);
                };

                document.querySelectorAll('[data-copy-vpn-key]').forEach((button) => {
                    if (button.dataset.copyBound === 'true') {
                        return;
                    }

                    button.dataset.copyBound = 'true';

                    button.addEventListener('click', async () => {
                        const text = button.dataset.copyText ?? '';
                        const defaultTitle = button.dataset.copyDefaultTitle ?? 'Скопировать';
                        const successTitle = button.dataset.copySuccessTitle ?? 'Скопировано';

                        try {
                            await copyText(text);
                            button.title = successTitle;
                            button.setAttribute('aria-label', successTitle);
                            button.classList.add('border-emerald-400', 'text-emerald-300');

                            window.setTimeout(() => {
                                button.title = defaultTitle;
                                button.setAttribute('aria-label', defaultTitle);
                                button.classList.remove('border-emerald-400', 'text-emerald-300');
                            }, 1600);
                        } catch (error) {
                            button.title = 'Не удалось скопировать';
                            button.setAttribute('aria-label', 'Не удалось скопировать');
                        }
                    });
                });
            }
        </script>
    </body>
</html>
