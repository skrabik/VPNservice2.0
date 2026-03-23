@extends('customer.layouts.guest')

@section('content')
    <h2 class="mb-2 text-2xl font-semibold">Вход в кабинет</h2>
    <p class="mb-6 text-sm text-slate-500">Войдите по email и паролю, чтобы открыть управление подпиской и ключами.</p>

    <div id="telegram-login-panel" class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-slate-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold">Вход через Telegram</h3>
                <p id="telegram-login-text" class="mt-1 text-sm text-slate-600">
                    Если кабинет открыт как Telegram Mini App, вход выполнится автоматически без email и пароля.
                </p>
            </div>
            <span id="telegram-login-badge" class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">Ожидание</span>
        </div>

        <form id="telegram-login-form" method="POST" action="{{ route('customer.telegram.store') }}" class="mt-4">
            @csrf
            <input type="hidden" name="init_data" id="telegram-init-data">
            <button type="submit" id="telegram-login-button" class="hidden w-full rounded-2xl bg-sky-600 px-4 py-3 font-medium text-white">
                Войти через Telegram
            </button>
        </form>

        <div class="mt-4 text-sm text-slate-600">
            <p>Не открывали кабинет из Telegram? Запустите его из бота:</p>
            <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="mt-2 inline-flex rounded-2xl bg-slate-900 px-4 py-2 font-medium text-white">
                Открыть бота
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('customer.login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-2xl border border-slate-300 px-4 py-3">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Пароль</label>
            <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
        </div>

        <button type="submit" class="w-full rounded-2xl bg-slate-950 px-4 py-3 font-medium text-white">Войти</button>
    </form>

    <div class="mt-6 space-y-2 text-sm text-slate-600">
        <p>Новый пользователь? <a href="{{ route('customer.register') }}" class="font-medium text-indigo-600">Создать аккаунт</a></p>
        <p>Есть аккаунт только в Telegram? Откройте ссылку на кабинет из бота и завершите регистрацию там.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const webApp = window.Telegram?.WebApp;
            const initDataInput = document.getElementById('telegram-init-data');
            const loginButton = document.getElementById('telegram-login-button');
            const loginForm = document.getElementById('telegram-login-form');
            const loginText = document.getElementById('telegram-login-text');
            const loginBadge = document.getElementById('telegram-login-badge');

            if (!webApp?.initData || !webApp?.initDataUnsafe?.user) {
                loginText.textContent = 'Обычный веб-вход доступен ниже. Для быстрого входа откройте кабинет кнопкой Mini App из Telegram-бота.';
                loginBadge.textContent = 'Вне Telegram';
                return;
            }

            initDataInput.value = webApp.initData;
            loginButton.classList.remove('hidden');
            loginText.textContent = 'Telegram Mini App обнаружен. Нажмите кнопку ниже или дождитесь автоматического входа.';
            loginBadge.textContent = 'Telegram';

            window.setTimeout(() => {
                if (initDataInput.value !== '') {
                    loginForm.submit();
                }
            }, 250);
        });
    </script>
@endsection
