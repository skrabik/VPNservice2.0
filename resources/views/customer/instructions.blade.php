@php
    $title = 'Инструкции';
@endphp
@extends('customer.layouts.app')

@section('content')
    <section class="customer-panel customer-panel-hero rounded-3xl p-6">
        <h1 class="customer-page-title text-3xl font-semibold">Инструкции по подключению</h1>

        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($platforms as $platform)
                <a href="{{ route('customer.instructions', ['type' => $platform['type']]) }}"
                   class="customer-pill rounded-full px-4 py-2 text-sm {{ $selectedType === $platform['type'] ? 'customer-pill-active' : '' }}">
                    {{ $platform['icon'] }} {{ $platform['label'] }}
                </a>
            @endforeach
        </div>

        <div class="customer-prose prose prose-invert mt-8 max-w-none rounded-3xl p-6">
            {!! $instructionHtml !!}
        </div>
    </section>
@endsection
