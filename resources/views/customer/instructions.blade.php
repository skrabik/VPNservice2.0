@php
    $title = 'Инструкции';
@endphp
@extends('customer.layouts.app')

@section('content')
    <section class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
        <h1 class="text-3xl font-semibold text-white">Инструкции по подключению</h1>
        <p class="mt-3 text-slate-300">Контент синхронизирован с Telegram-командой `/instructions`.</p>

        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($platforms as $platform)
                <a href="{{ route('customer.instructions', ['type' => $platform['type']]) }}"
                   class="rounded-full px-4 py-2 text-sm {{ $selectedType === $platform['type'] ? 'bg-indigo-500 text-white' : 'bg-slate-800 text-slate-200' }}">
                    {{ $platform['icon'] }} {{ $platform['label'] }}
                </a>
            @endforeach
        </div>

        <div class="prose prose-invert mt-8 max-w-none rounded-3xl bg-slate-800 p-6">
            {!! $instructionHtml !!}
        </div>
    </section>
@endsection
