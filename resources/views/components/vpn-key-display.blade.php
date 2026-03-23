@props([
    'value',
    'label' => 'Скопировать ключ',
    'containerClass' => 'mt-4',
])

<div class="{{ $containerClass }}">
    <div class="mb-3 flex items-center justify-between gap-3">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ $label }}</p>
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900 text-slate-200 transition hover:border-indigo-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900"
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

    <div class="overflow-x-auto rounded-2xl bg-slate-950 p-4 font-mono text-sm text-emerald-300 break-all">
        {{ $value }}
    </div>
</div>
