<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Вход в кабинет' }}</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="customer-theme">
        <div class="customer-shell mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10">
            <div class="grid w-full gap-8 lg:grid-cols-2">
                <div class="customer-panel customer-panel-hero rounded-3xl p-8">
                    <p class="customer-kicker mb-3 text-sm">NerpaVPN</p>
                    <h1 class="customer-page-title mb-4 text-4xl font-semibold">Веб-кабинет клиента</h1>
                    <p class="customer-page-text text-base leading-7">
                        Управляйте ключами, проверяйте подписку, открывайте инструкции и пишите в поддержку без Telegram.
                    </p>
                    <div class="mt-8 space-y-3 text-sm">
                        <div class="customer-metric rounded-2xl px-4 py-3">Поддержка существующих Telegram-пользователей через signed link</div>
                        <div class="customer-metric rounded-2xl px-4 py-3">Создание и перевыпуск VPN-ключей из кабинета</div>
                        <div class="customer-metric rounded-2xl px-4 py-3">Просмотр статуса подписки и инструкции по подключению</div>
                    </div>
                </div>

                <div class="customer-panel-soft rounded-3xl p-8">
                    @if (session('status'))
                        <div class="customer-alert customer-alert-success mb-6 rounded-2xl px-4 py-3 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="customer-alert customer-alert-danger mb-6 rounded-2xl px-4 py-3 text-sm">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
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
