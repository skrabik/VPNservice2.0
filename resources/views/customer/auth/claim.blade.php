@extends('customer.layouts.guest')

@section('content')
    <h2 class="mb-2 text-2xl font-semibold">Завершение регистрации</h2>
    <p class="mb-6 text-sm text-slate-500">
        Вы открыли кабинет по ссылке из Telegram. Задайте email и пароль, чтобы входить в веб без дублирования аккаунта.
    </p>

    <form method="POST" action="{{ request()->fullUrl() }}" class="space-y-4">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1 block text-sm font-medium text-slate-700">Имя</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $customer->first_name) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
            </div>

            <div>
                <label for="last_name" class="mb-1 block text-sm font-medium text-slate-700">Фамилия</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $customer->last_name) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3">
            </div>
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Пароль</label>
            <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Подтвердите пароль</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
        </div>

        <button type="submit" class="w-full rounded-2xl bg-slate-950 px-4 py-3 font-medium text-white">Активировать веб-доступ</button>
    </form>
@endsection
