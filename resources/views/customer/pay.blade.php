@php
    $title = 'Оплата';
@endphp
@extends('customer.layouts.app')

@section('content')
    <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
        <h1 class="text-3xl font-semibold text-white">Оплата подписки</h1>

        @if (! $yooKassaEnabled)
            <p class="mt-3 max-w-2xl text-slate-300">
                Онлайн-оплата через YooKassa сейчас отключена. Вы можете продлить подписку через Telegram-бота.
            </p>

            <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="mt-6 inline-flex rounded-2xl bg-indigo-500 px-5 py-3 font-medium text-white">
                Перейти в Telegram-бот
            </a>
        @else
            <p class="mt-3 max-w-2xl text-slate-300">
                Оплата проходит на защищенной странице YooKassa. После подтверждения платежа подписка продлится автоматически.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-slate-800 p-5">
                    <h2 class="text-lg font-semibold text-white">{{ $plan->title }}</h2>
                    <ul class="mt-4 space-y-2 text-slate-300">
                        <li>Период: {{ $plan->period }} дней</li>
                        <li>Стоимость: {{ number_format((float) $plan->price, 2, '.', ' ') }} RUB</li>
                        <li>Способ оплаты: YooKassa</li>
                    </ul>

                    @if ($activeSubscription)
                        <p class="mt-4 rounded-xl border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
                            У вас уже есть активная подписка до {{ $activeSubscription->date_end?->format('d.m.Y H:i') }}.
                            После оплаты она будет продлена еще на {{ $plan->period }} дней.
                        </p>
                    @endif

                    <form method="POST" action="{{ route('customer.pay.yookassa') }}" class="mt-5">
                        @csrf
                        <button type="submit" class="inline-flex rounded-2xl bg-indigo-500 px-5 py-3 font-medium text-white hover:bg-indigo-400">
                            Оплатить через YooKassa
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl bg-slate-800 p-5">
                    <h2 class="text-lg font-semibold text-white">Последний платеж</h2>

                    @if ($latestYooKassaPayment)
                        @php
                            $statusLabel = match ($latestYooKassaPayment->status) {
                                \App\Models\Payment::STATUS_SUCCEEDED => 'Успешно',
                                \App\Models\Payment::STATUS_CANCELED => 'Отменен',
                                default => 'Ожидает оплаты',
                            };

                            $statusClasses = match ($latestYooKassaPayment->status) {
                                \App\Models\Payment::STATUS_SUCCEEDED => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-100',
                                \App\Models\Payment::STATUS_CANCELED => 'border-rose-500/30 bg-rose-500/10 text-rose-100',
                                default => 'border-amber-500/30 bg-amber-500/10 text-amber-100',
                            };
                        @endphp

                        <div class="mt-4 rounded-xl border px-3 py-2 text-sm {{ $statusClasses }}">
                            Статус: {{ $statusLabel }}
                        </div>
                        <p class="mt-3 text-sm text-slate-300">ID платежа: {{ $latestYooKassaPayment->external_payment_id }}</p>
                        <p class="mt-1 text-sm text-slate-300">Создан: {{ $latestYooKassaPayment->created_at?->format('d.m.Y H:i') }}</p>

                        <a href="{{ route('customer.pay', ['refresh' => 1]) }}" class="mt-5 inline-flex rounded-2xl border border-slate-600 px-4 py-2 text-sm text-slate-100 hover:bg-slate-700">
                            Обновить статус
                        </a>
                    @else
                        <p class="mt-4 text-slate-300">Платежей через YooKassa пока не было.</p>
                    @endif
                </div>
            </div>

            <p class="mt-6 text-sm text-slate-400">
                Если платеж прошел, но статус еще не обновился, нажмите «Обновить статус» через несколько секунд.
            </p>
        @endif

        <div class="mt-8 rounded-2xl bg-slate-800 p-5">
            <h2 class="text-lg font-semibold text-white">Альтернативный способ</h2>
            <p class="mt-3 text-slate-300">Оплатить подписку также можно через Telegram-бота.</p>
            <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="mt-5 inline-flex rounded-2xl border border-slate-600 px-5 py-3 font-medium text-slate-100 hover:bg-slate-700">
                Открыть Telegram-бота
            </a>
        </div>
    </section>
@endsection
