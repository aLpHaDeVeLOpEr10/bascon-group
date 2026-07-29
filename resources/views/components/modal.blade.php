{{--
    Dialog.

    <x-modal id="updateModal" title="Update user" size="lg">
        …body…
        <x-slot:footer>…</x-slot:footer>
    </x-modal>

    The id, the `.modal` class and data-dismiss="modal" are kept from the
    Bootstrap markup on purpose: ~15 views open and close these with
    $('#id').modal('show') / .modal('hide'), and those calls still work because
    resources/js/ui.js reimplements that jQuery API. No view JS was changed.
--}}
@props([
    'id',
    'title' => null,
    'size' => null,   // sm | lg
    'footer' => null,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'modal-dialog-sm',
        'lg' => 'modal-dialog-lg',
        default => '',
    };
@endphp

<div class="modal" id="{{ $id }}" tabindex="-1" aria-hidden="true"
     @if ($title) aria-labelledby="{{ $id }}-title" @endif>
    <div class="modal-dialog {{ $sizeClass }}">
        <div class="modal-content">

            @if ($title)
                <div class="modal-header">
                    <h2 class="modal-title" id="{{ $id }}-title">{{ $title }}</h2>

                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @if ($footer)
                <div class="modal-footer">{{ $footer }}</div>
            @endif
        </div>
    </div>
</div>
