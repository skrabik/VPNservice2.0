<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nerpa VPN — быстрый и приватный доступ в интернет</title>
    <meta name="description" content="NerpaVPN — VPN с веб-кабинетом: подписка, ключи и поддержка в браузере. Оплату и быстрый старт удобно пройти через Telegram-бота.">
    <meta name="keywords" content="NerpaVPN, Nerpa VPN, VPN, Telegram, приватность, безопасный интернет">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/branding/nerpa-logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/branding/nerpa-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>
<body class="nerpa-landing antialiased">
    <div class="nerpa-shell">
        <header class="nerpa-header">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <div class="nerpa-logo-mark">
                        <img src="{{ asset('images/branding/nerpa-logo.jpg') }}" alt="NerpaVPN" class="nerpa-logo-image">
                    </div>
                    <span class="nerpa-brand">NerpaVPN</span>
                </a>
                <nav class="hidden items-center gap-8 md:flex" aria-label="Основная навигация">
                    <a href="#how" class="nerpa-nav-link">Как это работает</a>
                    <a href="#features" class="nerpa-nav-link">Возможности</a>
                    <a href="#pricing" class="nerpa-nav-link">Тариф</a>
                    <a href="#support" class="nerpa-nav-link">Поддержка</a>
                </nav>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ route('customer.login') }}" class="nerpa-btn-primary hidden text-sm sm:inline-flex">
                        <i class="fa-solid fa-globe text-lg" aria-hidden="true"></i>
                        Войти в кабинет
                    </a>
                    <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-btn-ghost hidden text-sm md:inline-flex">
                        <i class="fa-brands fa-telegram text-lg" aria-hidden="true"></i>
                        Telegram-бот
                    </a>
                    <button type="button" class="nerpa-nav-link p-2 md:hidden mobile-menu-toggle" aria-expanded="false" aria-controls="nerpa-mobile-nav">
                        <i class="fa-solid fa-bars text-xl" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div id="nerpa-mobile-nav" class="mobile-menu nerpa-mobile-menu hidden md:hidden">
                <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-4">
                    <a href="#how" class="nerpa-nav-link rounded-xl px-3 py-3 hover:bg-white/5">Как это работает</a>
                    <a href="#features" class="nerpa-nav-link rounded-xl px-3 py-3 hover:bg-white/5">Возможности</a>
                    <a href="#pricing" class="nerpa-nav-link rounded-xl px-3 py-3 hover:bg-white/5">Тариф</a>
                    <a href="#support" class="nerpa-nav-link rounded-xl px-3 py-3 hover:bg-white/5">Поддержка</a>
                    <a href="{{ route('customer.login') }}" class="nerpa-btn-primary mt-2 justify-center text-sm">
                        <i class="fa-solid fa-globe text-lg" aria-hidden="true"></i>
                        Войти в кабинет
                    </a>
                    <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-btn-ghost mt-2 justify-center text-sm">
                        <i class="fa-brands fa-telegram text-lg" aria-hidden="true"></i>
                        Telegram-бот
                    </a>
                </div>
            </div>
        </header>

        <section class="nerpa-hero relative z-10">
            <div class="relative z-10 mx-auto grid max-w-6xl items-center gap-12 px-4 lg:grid-cols-2">
                <div>
                    <h1 class="mb-8 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Управление VPN в
                        <span class="nerpa-text-gradient">веб-кабинете</span>
                    </h1>
                    <p class="mb-8 max-w-xl text-lg leading-relaxed text-[color:var(--nerpa-text-muted)]">
                        В браузере удобно смотреть подписку, копировать ключи, открывать инструкции и писать в поддержку.
                        Первую оплату и быстрый доступ к сервису по-прежнему можно оформить через Telegram-бота.
                    </p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <a href="{{ route('customer.login') }}" class="nerpa-btn-primary px-8 py-3.5 text-base">
                            <i class="fa-solid fa-globe text-xl" aria-hidden="true"></i>
                            Войти в кабинет
                        </a>
                        <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-btn-ghost px-8 py-3.5 text-base">
                            <i class="fa-brands fa-telegram text-xl" aria-hidden="true"></i>
                            Оплата и бот в Telegram
                        </a>
                    </div>
                    <div class="nerpa-highlight-list mt-6 flex flex-wrap gap-3">
                        <span class="nerpa-highlight-pill">
                            <i class="fa-solid fa-globe" aria-hidden="true"></i>
                            Веб-кабинет в браузере
                        </span>
                        <span class="nerpa-highlight-pill">
                            <i class="fa-solid fa-key" aria-hidden="true"></i>
                            Ключи и инструкции в одном месте
                        </span>
                        <span class="nerpa-highlight-pill">
                            <i class="fa-solid fa-headset" aria-hidden="true"></i>
                            Поддержка прямо из кабинета
                        </span>
                    </div>
                    <dl class="mt-12 grid grid-cols-3 gap-6 border-t border-white/10 pt-10 sm:max-w-lg">
                        <div>
                            <dt class="nerpa-stat-label">Аптайм</dt>
                            <dd class="nerpa-stat-value mt-1"><span data-nerpa-counter="99" data-nerpa-suffix="%">0%</span></dd>
                        </div>
                        <div>
                            <dt class="nerpa-stat-label">Локации</dt>
                            <dd class="nerpa-stat-value mt-1"><span data-nerpa-counter="14" data-nerpa-suffix="+">0+</span></dd>
                        </div>
                        <div>
                            <dt class="nerpa-stat-label">Поддержка</dt>
                            <dd class="nerpa-stat-value mt-1 text-lg sm:text-2xl">24/7</dd>
                        </div>
                    </dl>
                </div>
                <div class="relative lg:pl-4">
                    <div class="nerpa-panel-strong p-6 sm:p-8">
                        <div class="mb-6 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                </span>
                                <span class="font-semibold text-white">NerpaVPN</span>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-[color:var(--nerpa-text-muted)]">Веб-кабинет + VPN</span>
                        </div>
                        <ul class="space-y-4 text-sm">
                            <li class="flex items-center justify-between gap-4 border-b border-white/10 pb-3">
                                <span class="text-[color:var(--nerpa-text-soft)]">Веб-кабинет</span>
                                <span class="font-medium text-white">подписка, ключи, статус</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 border-b border-white/10 pb-3">
                                <span class="text-[color:var(--nerpa-text-soft)]">Поддержка</span>
                                <span class="font-medium text-white">быстрый контакт из кабинета</span>
                            </li>
                            <li class="flex items-center justify-between gap-4 border-b border-white/10 pb-3">
                                <span class="text-[color:var(--nerpa-text-soft)]">Ключи</span>
                                <span class="font-medium text-white">быстрое получение и копирование</span>
                            </li>
                            <li class="flex items-center justify-between gap-4">
                                <span class="text-[color:var(--nerpa-text-soft)]">Шифрование</span>
                                <span class="font-medium text-white">современные протоколы</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="how" class="relative z-10 py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-12 max-w-2xl">
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">Три шага до VPN</h2>
                    <p class="mt-3 text-lg text-[color:var(--nerpa-text-muted)]">
                        Главная точка входа для ежедневной работы — веб-кабинет. Telegram удобен для первой оплаты и быстрого получения доступа.
                    </p>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <article class="nerpa-card nerpa-reveal p-6 sm:p-8">
                        <p class="nerpa-step-num mb-3">Шаг 1</p>
                        <div class="nerpa-icon-wrap mb-4 w-12 h-12">
                            <i class="fa-solid fa-globe text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Веб-кабинет</h3>
                        <p class="mt-2 text-[color:var(--nerpa-text-muted)]">
                            Войдите в личный кабинет в браузере: там подписка, ключи, инструкции и поддержка — всё в одном месте.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 sm:p-8">
                        <p class="nerpa-step-num mb-3">Шаг 2</p>
                        <div class="nerpa-icon-wrap mb-4 w-12 h-12">
                            <i class="fa-brands fa-telegram text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Telegram при первом подключении</h3>
                        <p class="mt-2 text-[color:var(--nerpa-text-muted)]">
                            Если нужно оформить подписку или получить доступ в пару кликов, воспользуйтесь ботом NerpaVPN — затем снова возвращайтесь в кабинет.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 sm:p-8">
                        <p class="nerpa-step-num mb-3">Шаг 3</p>
                        <div class="nerpa-icon-wrap mb-4 w-12 h-12">
                            <i class="fa-solid fa-key text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Подключите устройства</h3>
                        <p class="mt-2 text-[color:var(--nerpa-text-muted)]">
                            Копируйте ключи, открывайте пошаговые инструкции и возвращайтесь в кабинет в любой момент с телефона или компьютера.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section id="features" class="relative z-10 py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-12 text-center">
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">Почему NerpaVPN</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-lg text-[color:var(--nerpa-text-muted)]">
                        Сервис сделан так, чтобы пользоваться им было удобно каждый день именно через веб-кабинет, а не только через бота.
                    </p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-globe text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Удобный веб-кабинет</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Управляйте подпиской, переходите к нужным разделам и находите все основные действия в одном понятном интерфейсе.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-key text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Ключи и инструкции под рукой</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Не нужно искать сообщения в чате: конфигурации и шаги подключения собраны в кабинете и доступны в пару кликов.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-headset text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Поддержка внутри сервиса</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Когда возникает вопрос по оплате, доступу или настройке, из кабинета проще сразу перейти к нужному каналу связи.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-lock text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Надёжное шифрование</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Трафик защищён проверенными протоколами, чтобы вы могли пользоваться интернетом спокойнее.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-mobile-screen text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Телефон, планшет, ПК</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Веб-кабинет одинаково удобен на разных устройствах, поэтому управлять сервисом можно откуда угодно.
                        </p>
                    </article>
                    <article class="nerpa-card nerpa-reveal p-6 text-center sm:text-left">
                        <div class="nerpa-icon-wrap mx-auto mb-4 sm:mx-0">
                            <i class="fa-solid fa-earth-europe text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-white">Серверы и доступ без лишнего шума</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Подберите регион под задачи и возвращайтесь в кабинет, когда нужно сменить сценарий использования.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section id="pricing" class="relative z-10 py-20">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-12 text-center">
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">Тариф</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-lg text-[color:var(--nerpa-text-muted)]">
                        Одна помесячная подписка: управляйте ею в веб-кабинете. Оплату и актуальную сумму удобнее смотреть в Telegram-боте.
                    </p>
                </div>
                <div class="mx-auto flex max-w-md justify-center">
                    <div class="nerpa-price-card nerpa-price-featured nerpa-reveal relative flex w-full flex-col p-8">
                        <span class="nerpa-badge absolute -top-3 left-1/2 -translate-x-1/2">На месяц</span>
                        <h3 class="text-xl font-bold text-white">NerpaVPN</h3>
                        <p class="mt-2 text-sm text-[color:var(--nerpa-text-soft)]">Доступ к серверам, веб-кабинет, ключи и поддержка</p>
                        <p class="mt-6 text-4xl font-extrabold text-white">499&nbsp;₽</p>
                        <p class="text-sm text-[color:var(--nerpa-text-muted)]">в месяц</p>
                        <ul class="mt-8 flex flex-1 flex-col gap-3 text-sm text-[color:var(--nerpa-text-muted)]">
                            <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-400" aria-hidden="true"></i> Доступ к серверам и локациям</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-400" aria-hidden="true"></i> Веб-кабинет: подписка, ключи, инструкции</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-400" aria-hidden="true"></i> Поддержка в Telegram</li>
                        </ul>
                        <a href="{{ route('customer.login') }}" class="nerpa-btn-primary mt-8 w-full justify-center gap-2 py-3">
                            <i class="fa-solid fa-globe text-lg" aria-hidden="true"></i>
                            Войти в кабинет
                        </a>
                        <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-btn-ghost mt-3 w-full justify-center gap-2 py-3">
                            <i class="fa-brands fa-telegram text-lg" aria-hidden="true"></i>
                            Оплатить в боте
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative z-10 py-16">
            <div class="mx-auto max-w-4xl px-4">
                <div class="nerpa-cta-band px-8 py-12 text-center sm:px-12">
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Откройте NerpaVPN и управляйте через веб-кабинет</h2>
                    <p class="mx-auto mt-3 max-w-xl text-[color:var(--nerpa-text-muted)]">
                        Заходите в веб-кабинет для подписки, ключей и поддержки. Для оплаты и мгновенного старта рядом всегда есть Telegram-бот.
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="{{ route('customer.login') }}" class="nerpa-btn-primary inline-flex px-10 py-3.5 text-base">
                            <i class="fa-solid fa-globe text-xl" aria-hidden="true"></i>
                            Открыть веб-кабинет
                        </a>
                        <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-btn-ghost inline-flex px-10 py-3.5 text-base">
                            <i class="fa-brands fa-telegram text-xl" aria-hidden="true"></i>
                            Перейти в бота
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <footer id="support" class="nerpa-footer relative z-10 py-16">
            <div class="mx-auto max-w-6xl px-4">
                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-3">
                            <div class="nerpa-logo-mark">
                                <img src="{{ asset('images/branding/nerpa-logo.jpg') }}" alt="NerpaVPN" class="nerpa-logo-image">
                            </div>
                            <span class="nerpa-brand">NerpaVPN</span>
                        </div>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-[color:var(--nerpa-text-muted)]">
                            Nerpa VPN — сервис с веб-кабинетом для ежедневного управления подпиской и ключами; Telegram-бот остаётся удобным способом оплаты и быстрого старта.
                        </p>
                        <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/15 bg-gradient-to-br from-[#6e64ff] to-[#2f8cff] text-white transition hover:opacity-90" aria-label="Telegram NerpaVPN">
                            <i class="fa-brands fa-telegram text-xl" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Сервис</h3>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li><a href="{{ route('customer.login') }}" class="nerpa-link-muted">Веб-кабинет</a></li>
                            <li><a href="#features" class="nerpa-link-muted">Возможности</a></li>
                            <li><a href="#pricing" class="nerpa-link-muted">Тариф</a></li>
                            <li><a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-link-muted">Telegram-бот</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Поддержка</h3>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li>
                                <a href="https://t.me/skrabik0" target="_blank" rel="noopener noreferrer" class="nerpa-link-muted inline-flex items-center gap-2">
                                    <i class="fa-brands fa-telegram" aria-hidden="true"></i>
                                    Написать в поддержку
                                </a>
                            </li>
                            <li>
                                <a href="{{ $telegramBotUrl }}" target="_blank" rel="noopener noreferrer" class="nerpa-link-muted inline-flex items-center gap-2">
                                    <i class="fa-brands fa-telegram" aria-hidden="true"></i>
                                    Бот NerpaVPN
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <p class="mt-12 border-t border-white/10 pt-8 text-center text-sm text-[color:var(--nerpa-text-soft)]">
                    © {{ date('Y') }} NerpaVPN. Все права защищены.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
