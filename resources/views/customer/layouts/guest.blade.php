<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Вход в кабинет' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10">
            <div class="grid w-full gap-8 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-800 bg-slate-900 p-8">
                    <p class="mb-3 text-sm uppercase tracking-[0.3em] text-indigo-300">Quantum Shield</p>
                    <h1 class="mb-4 text-4xl font-semibold text-white">Веб-кабинет клиента</h1>
                    <p class="text-base leading-7 text-slate-300">
                        Управляйте ключами, проверяйте подписку, открывайте инструкции и пишите в поддержку без Telegram.
                    </p>
                    <div class="mt-8 space-y-3 text-sm text-slate-300">
                        <div class="rounded-2xl bg-slate-800 px-4 py-3">Поддержка существующих Telegram-пользователей через signed link</div>
                        <div class="rounded-2xl bg-slate-800 px-4 py-3">Создание и перевыпуск VPN-ключей из кабинета</div>
                        <div class="rounded-2xl bg-slate-800 px-4 py-3">Просмотр статуса подписки и инструкции по подключению</div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-800 bg-white p-8 text-slate-900">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
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
    </body>
</html>
