{{--
    One row of a settings-style form: label on the left, control on the right,
    stacking on small screens.

    The two columns only appear at lg. A fixed 190px label column is what makes
    a stack of rows line up, but it is dead space to the right of a short label
    like "Date", and on anything narrower than a desktop that space is the width
    the control needed. Below lg the label sits on its own line above its
    control, hard against the left edge. `.ui-row` in app.css is the
    hand-written twin of this and breaks at the same width.

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
                lg:grid-cols-[minmax(0,190px)_minmax(0,1fr)] lg:items-start lg:gap-x-6',
]) }}>
    @if ($label)
        <div class="lg:pt-2.5">
            <label @if ($for) for="{{ $for }}" @endif class="ui-label mb-0">
                {{ $label }}
                @if ($required)
                    <span class="text-rose-500" aria-hidden="true">*</span>
                @endif
            </label>
        </div>
    @else
        <div class="hidden lg:block"></div>
    @endif

    <div class="min-w-0 {{ $wide ? '' : 'max-w-md' }}">
        {{ $slot }}

        @if ($hint)
            <p class="ui-hint">{{ $hint }}</p>
        @endif
    </div>
</div>
