{{--
    Metric tile.

    <x-stat-card label="Total fee" :value="money($total_fee)" icon="wallet"
                 tone="success" trend="up" change="12.4%" note="vs last month" />

    Only `label` and `value` are required; omit `trend` and the footer row
    disappears.
--}}
@props([
    'label',
    'value',
    'icon' => 'activity',
    'tone' => 'brand',
    'trend' => null,   // up | down | flat
    'change' => null,
    'note' => null,
])

@php
    $icons = [
        'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'wallet'   => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M17 12h.01"/>',
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
        'building' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'layers'   => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    ];

    $tones = [
        'brand'   => 'bg-brand-50 text-brand-600',
        'success' => 'bg-brand-50 text-brand-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'danger'  => 'bg-rose-50 text-rose-600',
        'info'    => 'bg-sky-50 text-sky-600',
        'neutral' => 'bg-neutral-100 text-neutral-500',
    ];

    $trendTone = match ($trend) {
        'up'   => 'bg-brand-50 text-brand-700',
        'down' => 'bg-rose-50 text-rose-700',
        default => 'bg-neutral-100 text-neutral-600',
    };

    $trendPath = match ($trend) {
        'up'   => '<path d="m5 15 7-7 7 7"/>',
        'down' => '<path d="m19 9-7 7-7-7"/>',
        default => '<path d="M5 12h14"/>',
    };
@endphp

<div {{ $attributes->merge([
    'class' => 'group rounded-2xl border border-line bg-surface p-5 transition-all duration-200
                hover:-translate-y-0.5 hover:border-neutral-300 hover:shadow-panel',
]) }}>
    <div class="mb-4 flex items-start justify-between gap-3">
        <span class="text-[12.5px] font-medium text-neutral-500">{{ $label }}</span>

        <span aria-hidden="true"
              class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                 stroke-linecap="round" stroke-linejoin="round">
                {!! $icons[$icon] ?? $icons['activity'] !!}
            </svg>
        </span>
    </div>

    <p class="text-[26px] font-semibold leading-tight tracking-[-0.03em] text-neutral-900 tabular-nums">
        {{ $value }}
    </p>

    @if ($trend || $note)
        <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs text-neutral-500">
            @if ($trend && $change)
                <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 font-semibold tabular-nums {{ $trendTone }}">
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $trendPath !!}
                    </svg>
                    {{ $change }}
                </span>
            @endif

            @if ($note)
                <span>{{ $note }}</span>
            @endif
        </div>
    @endif
</div>
