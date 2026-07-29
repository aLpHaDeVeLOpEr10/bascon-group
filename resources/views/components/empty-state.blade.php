{{--
    Placeholder for a view with nothing to show yet.

    <x-empty-state title="No sites yet" text="Add your first site to get going.">
        <a href="…" class="ui-btn ui-btn-primary">Add site</a>
    </x-empty-state>
--}}
@props([
    'title' => 'Nothing here yet',
    'text' => null,
    'icon' => 'inbox',
])

@php
    $icons = [
        'inbox'  => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'file'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
        'users'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ui-empty']) }}>
    <span class="ui-empty-art" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
             stroke-linecap="round" stroke-linejoin="round">
            {!! $icons[$icon] ?? $icons['inbox'] !!}
        </svg>
    </span>

    <p class="ui-empty-title">{{ $title }}</p>

    @if ($text)
        <p class="ui-empty-text">{{ $text }}</p>
    @endif

    @if (trim($slot) !== '')
        <div class="mt-5 flex flex-wrap justify-center gap-2.5">{{ $slot }}</div>
    @endif
</div>
