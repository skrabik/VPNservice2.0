@php($title = 'Оплата')
@extends('customer.layouts.app')

@section('content')
    <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
        <h1 class="text-3xl font-semibold text-white">Оплата подписки</h1>
        <p class="mt-3 max-w-2xl text-slate-300">
            Веб-оплата еще не подключена. Пока продление и покупка подписки доступны через Telegram-бота,
            чтобы не дублировать текущую платежную логику.
        </p>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl bg-slate-800 p-5">
                <h2 class="text-lg font-semibold text-white">Что уже доступно в кабинете</h2>
                <ul class="mt-4 space-y-2 text-slate-300">
                    <li>Проверка статуса подписки</li>
                    <li>Создание и просмотр ключей</li>
                    <li>Инструкции по подключению</li>
                    <li>Поддержка</li>
                </ul>
            </div>
            <div class="rounded-2xl bg-slate-800 p-5">
                <h2 class="text-lg font-semibold text-white">Как оплатить сейчас</h2>
                <p class="mt-4 text-slate-300">Откройте бота и используйте команду оплаты. После успешного платежа кабинет сразу увидит обновленную подписку.</p>
                <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="mt-6 inline-flex rounded-2xl bg-indigo-500 px-5 py-3 font-medium text-white">
                    Перейти в Telegram-бот
                </a>
            </div>
        </div>
    </section>
@endsection
