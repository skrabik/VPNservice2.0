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
    <body class="customer-theme">
        <div class="customer-shell">
        <header class="customer-header" data-customer-header>
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('customer.dashboard') }}" class="customer-brand text-lg font-semibold">Quantum Shield</a>
                </div>
                <div class="customer-panel-soft min-w-0 rounded-3xl px-4 py-3 sm:text-right">
                    <p class="font-medium text-white">{{ auth('customer')->user()->first_name }}</p>
                    <p class="customer-subtitle truncate text-sm">{{ auth('customer')->user()->email ?: 'Telegram-only аккаунт' }}</p>
                </div>
            </div>
            <nav class="mx-auto grid max-w-6xl grid-cols-2 gap-2 px-4 pb-4 sm:grid-cols-3 lg:flex lg:flex-wrap">
                <a href="{{ route('customer.dashboard') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.dashboard') ? 'customer-nav-link-active' : '' }}">Главная</a>
                <a href="{{ route('customer.status') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.status') ? 'customer-nav-link-active' : '' }}">Подписка</a>
                <a href="{{ route('customer.keys') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.keys') ? 'customer-nav-link-active' : '' }}">VPN-ключи</a>
                <a href="{{ route('customer.instructions') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.instructions') ? 'customer-nav-link-active' : '' }}">Инструкции</a>
                <a href="{{ route('customer.support') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.support') ? 'customer-nav-link-active' : '' }}">Поддержка</a>
                <a href="{{ route('customer.pay') }}" class="customer-nav-link flex min-h-11 items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm {{ request()->routeIs('customer.pay') ? 'customer-nav-link-active' : '' }}">Оплата</a>
                @unless (session('customer.is_telegram_mini_app'))
                    <form method="POST" action="{{ route('customer.logout') }}" class="col-span-2 sm:col-span-3 lg:ml-auto">
                        @csrf
                        <button type="submit" class="customer-button-secondary flex min-h-11 w-full items-center justify-center rounded-2xl px-4 py-2.5 text-center text-sm lg:w-auto">Выйти</button>
                    </form>
                @endunless
            </nav>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="customer-alert customer-alert-success mb-6 rounded-2xl px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="customer-alert customer-alert-danger mb-6 rounded-2xl px-4 py-3">
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
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const webApp = window.Telegram?.WebApp;
                bindAutoHideHeader();

                if (!webApp) {
                    bindCopyButtons();
                    return;
                }

                webApp.ready();
                webApp.expand();
                bindCopyButtons();
            });

            function bindAutoHideHeader() {
                const header = document.querySelector('[data-customer-header]');

                if (!header) {
                    return;
                }

                let lastScrollY = window.scrollY;
                let ticking = false;
                const minDelta = 12;

                const updateHeaderVisibility = () => {
                    const currentScrollY = window.scrollY;
                    const scrollDelta = currentScrollY - lastScrollY;

                    if (currentScrollY <= 16 || scrollDelta < -minDelta) {
                        header.classList.remove('customer-header-hidden');
                    } else if (scrollDelta > minDelta) {
                        header.classList.add('customer-header-hidden');
                    }

                    lastScrollY = currentScrollY;
                    ticking = false;
                };

                window.addEventListener('scroll', () => {
                    if (ticking) {
                        return;
                    }

                    ticking = true;
                    window.requestAnimationFrame(updateHeaderVisibility);
                }, { passive: true });
            }

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
                            button.classList.add('customer-copy-button-success');

                            window.setTimeout(() => {
                                button.title = defaultTitle;
                                button.setAttribute('aria-label', defaultTitle);
                                button.classList.remove('customer-copy-button-success');
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
