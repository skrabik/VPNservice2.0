<div class="p-4">
    <h3 class="text-lg font-bold">Stats</h3>
    <div>
        @foreach ($count_data as $item)
            <p>{{ $item->action }} - {{ $item->count }}</p>
        @endforeach
    </div>
</div>