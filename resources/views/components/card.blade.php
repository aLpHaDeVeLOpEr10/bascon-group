{{--
    Surface container.

    <x-card title="Users" subtitle="All accounts" flush>
        <x-slot:actions>…</x-slot:actions>
        …body…
        <x-slot:footer>…</x-slot:footer>
    </x-card>
    `flush` removes the body padding and clips the corners — use it when the
    body is a table or a DataTable, so rows meet the card edge cleanly.
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'flush' => false,
])

<div {{ $attributes->merge(['class' => 'ui-card' . ($flush ? ' overflow-hidden' : '')]) }}>
    @if ($title || $actions)
        <div class="ui-card-header">
            @if ($title)
                <h2 class="ui-card-title">{{ $title }}</h2>
            @endif

            @if ($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endif

            @if ($subtitle)
                <p class="ui-card-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <div class="{{ $flush ? '' : 'ui-card-body' }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="ui-card-footer">{{ $footer }}</div>
    @endif
</div>
