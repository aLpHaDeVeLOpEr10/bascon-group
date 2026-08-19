{{--
    One row of a settings-style form: label on the left, control on the right,
    stacking on small screens.

    The label column narrows between sm and lg. At 190px it is sized for a long
    label on a wide screen, and on a tablet it left a short one like "Date"
    trailing a hundred pixels of nothing before its input. `.ui-row` in app.css
    is the hand-written twin of this and steps at the same widths, so rows of
    both kinds line up in the same form.

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
                sm:grid-cols-[minmax(0,140px)_minmax(0,1fr)] sm:items-start sm:gap-x-4
                lg:grid-cols-[minmax(0,190px)_minmax(0,1fr)] lg:gap-x-6',
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
