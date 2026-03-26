@extends('customer.layouts.guest')

@section('content')
    <h2 class="customer-page-title mb-2 text-2xl font-semibold">Завершение регистрации</h2>
    <p class="customer-page-text mb-6 text-sm">
        Вы открыли кабинет по ссылке из Telegram. Задайте email и пароль, чтобы входить в веб без дублирования аккаунта.
    </p>

    <form method="POST" action="{{ request()->fullUrl() }}" class="space-y-4">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="first_name" class="customer-field-label mb-1 block text-sm font-medium">Имя</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $customer->first_name) }}" required class="customer-input rounded-2xl px-4 py-3">
            </div>

            <div>
                <label for="last_name" class="customer-field-label mb-1 block text-sm font-medium">Фамилия</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $customer->last_name) }}" class="customer-input rounded-2xl px-4 py-3">
            </div>
        </div>

        <div>
            <label for="email" class="customer-field-label mb-1 block text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}" required class="customer-input rounded-2xl px-4 py-3">
        </div>

        <div>
            <label for="password" class="customer-field-label mb-1 block text-sm font-medium">Пароль</label>
            <input id="password" name="password" type="password" required class="customer-input rounded-2xl px-4 py-3">
        </div>

        <div>
            <label for="password_confirmation" class="customer-field-label mb-1 block text-sm font-medium">Подтвердите пароль</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="customer-input rounded-2xl px-4 py-3">
        </div>

        <button type="submit" class="customer-button-primary w-full rounded-2xl px-4 py-3 font-medium">Активировать веб-доступ</button>
    </form>
@endsection
