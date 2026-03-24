<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Информация о тикете</h5>
                <dl class="mb-0">
                    <dt>ID</dt>
                    <dd>#{{ $ticket->id }}</dd>

                    <dt>Клиент</dt>
                    <dd>{{ $ticket->customer?->first_name }} (ID: {{ $ticket->customer?->id }})</dd>

                    <dt>Telegram ID</dt>
                    <dd>{{ $ticket->customer?->telegram_id ?: 'Нет' }}</dd>

                    <dt>Канал</dt>
                    <dd>{{ $ticket->source_channel }}</dd>

                    <dt>Статус</dt>
                    <dd>{{ $ticket->status }}</dd>

                    <dt>Ответственный</dt>
                    <dd>{{ $ticket->assignedUser?->name ?: 'Не назначен' }}</dd>

                    <dt>Создан</dt>
                    <dd>{{ $ticket->created_at?->format('d.m.Y H:i') }}</dd>

                    <dt>Последний ответ</dt>
                    <dd>{{ $ticket->last_reply_at?->format('d.m.Y H:i') ?: 'Нет' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Сообщение клиента</h5>
                <div class="border rounded p-3 bg-light">
                    {!! nl2br(e($ticket->message)) !!}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Ответы поддержки</h5>

                @forelse ($ticket->replies as $reply)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div>
                                <strong>{{ $reply->user?->name ?: 'Поддержка' }}</strong>
                                <div class="text-muted small">{{ $reply->created_at?->format('d.m.Y H:i') }}</div>
                            </div>
                            <div class="text-muted small">
                                {{ $reply->sent_to_telegram_at ? 'Отправлено в Telegram' : 'Только в кабинете' }}
                            </div>
                        </div>

                        <div>{!! nl2br(e($reply->message)) !!}</div>
                    </div>
                @empty
                    <div class="text-muted">Ответов пока нет.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
