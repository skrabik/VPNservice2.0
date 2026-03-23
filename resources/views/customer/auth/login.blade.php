@extends('customer.layouts.guest')

@section('content')
    <h2 class="mb-2 text-2xl font-semibold">Вход в кабинет</h2>
    <p class="mb-6 text-sm text-slate-500">Войдите по email и паролю, чтобы открыть управление подпиской и ключами.</p>

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
@endsection
