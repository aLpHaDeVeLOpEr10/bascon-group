{{--
    One row of a settings-style form: label on the left, control on the right,
    stacking on small screens.

    <x-field label="Phase" for="field-phase" hint="Optional." required>
        <input id="field-phase" name="phase" class="ui-input" />
    </x-field>

    This replaces the Bootstrap `.form-group / .col-sm-3 .control-label /
    .col-sm-5` triple that appeared 154 times across the views.
--}}
@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'required' => false,
    'wide' => false,
])

<div {{ $attributes->merge([
    'class' => 'grid gap-1.5 border-b border-neutral-100 py-4 last:border-b-0
                sm:grid-cols-[minmax(0,190px)_minmax(0,1fr)] sm:items-start sm:gap-x-6',
]) }}>
    @if ($label)
        <div class="sm:pt-2.5">
            <label @if ($for) for="{{ $for }}" @endif class="ui-label mb-0">
                {{ $label }}
                @if ($required)
                    <span class="text-rose-500" aria-hidden="true">*</span>
                @endif
            </label>
        </div>
    @else
        <div class="hidden sm:block"></div>
    @endif

    <div class="min-w-0 {{ $wide ? '' : 'max-w-md' }}">
        {{ $slot }}

        @if ($hint)
            <p class="ui-hint">{{ $hint }}</p>
        @endif
    </div>
</div>
