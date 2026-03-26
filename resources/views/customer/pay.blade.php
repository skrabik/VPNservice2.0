@php
    $title = 'Оплата';
@endphp
@extends('customer.layouts.app')

@section('content')
    <section class="customer-panel customer-panel-hero rounded-3xl p-6">
        <h1 class="customer-page-title text-3xl font-semibold">Оплата подписки</h1>

        @if (! $yooKassaEnabled)
            <p class="customer-page-text mt-3 max-w-2xl">
                Онлайн-оплата через YooKassa сейчас отключена. Вы можете продлить подписку через Telegram-бота.
            </p>

            <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="customer-button-primary mt-6 inline-flex rounded-2xl px-5 py-3 font-medium">
                Перейти в Telegram-бот
            </a>
        @else
            <p class="customer-page-text mt-3 max-w-2xl">
                Оплата проходит на защищенной странице YooKassa. После подтверждения платежа подписка продлится автоматически.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="customer-metric rounded-2xl p-5">
                    <h2 class="customer-page-title text-lg font-semibold">{{ $plan->title }}</h2>
                    <ul class="customer-page-text mt-4 space-y-2">
                        <li>Период: {{ $plan->period }} дней</li>
                        <li>Стоимость: {{ number_format((float) $plan->price, 2, '.', ' ') }} RUB</li>
                        <li>Способ оплаты: YooKassa</li>
                    </ul>

                    @if ($activeSubscription)
                        <p class="customer-alert customer-alert-warning mt-4 rounded-xl px-3 py-2 text-sm">
                            У вас уже есть активная подписка до {{ $activeSubscription->date_end?->format('d.m.Y H:i') }}.
                            После оплаты она будет продлена еще на {{ $plan->period }} дней.
                        </p>
                    @endif

                    <form method="POST" action="{{ route('customer.pay.yookassa') }}" class="mt-5">
                        @csrf
                        <button type="submit" class="customer-button-primary inline-flex rounded-2xl px-5 py-3 font-medium">
                            Оплатить через YooKassa
                        </button>
                    </form>
                </div>

                <div class="customer-metric rounded-2xl p-5">
                    <h2 class="customer-page-title text-lg font-semibold">Последний платеж</h2>

                    @if ($latestYooKassaPayment)
                        @php
                            $statusLabel = match ($latestYooKassaPayment->status) {
                                \App\Models\Payment::STATUS_SUCCEEDED => 'Успешно',
                                \App\Models\Payment::STATUS_CANCELED => 'Отменен',
                                default => 'Ожидает оплаты',
                            };

                            $statusClasses = match ($latestYooKassaPayment->status) {
                                \App\Models\Payment::STATUS_SUCCEEDED => 'customer-alert-success',
                                \App\Models\Payment::STATUS_CANCELED => 'customer-alert-danger',
                                default => 'customer-alert-warning',
                            };
                        @endphp

                        <div class="customer-alert mt-4 rounded-xl px-3 py-2 text-sm {{ $statusClasses }}">
                            Статус: {{ $statusLabel }}
                        </div>
                        <p class="customer-page-text mt-3 text-sm">ID платежа: {{ $latestYooKassaPayment->external_payment_id }}</p>
                        <p class="customer-page-text mt-1 text-sm">Создан: {{ $latestYooKassaPayment->created_at?->format('d.m.Y H:i') }}</p>

                        <a href="{{ route('customer.pay', ['refresh' => 1]) }}" class="customer-button-secondary mt-5 inline-flex rounded-2xl px-4 py-2 text-sm">
                            Обновить статус
                        </a>
                    @else
                        <p class="customer-page-text mt-4">Платежей через YooKassa пока не было.</p>
                    @endif
                </div>
            </div>

            <p class="customer-soft-text mt-6 text-sm">
                Если платеж прошел, но статус еще не обновился, нажмите «Обновить статус» через несколько секунд.
            </p>
        @endif

        <div class="customer-metric mt-8 rounded-2xl p-5">
            <h2 class="customer-page-title text-lg font-semibold">Альтернативный способ</h2>
            <p class="customer-page-text mt-3">Оплатить подписку также можно через Telegram-бота.</p>
            <a href="{{ $botUrl }}" target="_blank" rel="noreferrer" class="customer-button-secondary mt-5 inline-flex rounded-2xl px-5 py-3 font-medium">
                Открыть Telegram-бота
            </a>
        </div>
    </section>
@endsection
