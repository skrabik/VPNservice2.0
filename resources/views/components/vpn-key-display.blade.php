@props([
    'value',
    'label' => 'Скопировать ключ',
    'containerClass' => 'mt-4',
])

<div class="{{ $containerClass }}">
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="customer-key-label text-xs uppercase tracking-[0.2em]">{{ $label }}</p>
        <button
            type="button"
            class="customer-copy-button inline-flex h-10 w-10 items-center justify-center rounded-full focus:outline-none"
            data-copy-vpn-key
            data-copy-text="{{ $value }}"
            data-copy-default-title="Скопировать ключ"
            data-copy-success-title="Ключ скопирован"
            title="Скопировать ключ"
            aria-label="Скопировать VPN-ключ"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="9" y="9" width="10" height="10" rx="2"></rect>
                <path d="M5 15V7a2 2 0 0 1 2-2h8"></path>
            </svg>
        </button>
    </div>

    <div class="customer-key-box overflow-x-auto rounded-2xl p-4 font-mono text-sm break-all">
        {{ $value }}
    </div>
</div>
