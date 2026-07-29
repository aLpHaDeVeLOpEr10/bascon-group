{{--
    Page intro block.

    <x-page-header title="Show User" subtitle="Everyone with an account.">
        <x-slot:actions>
            <a href="..." class="ui-btn ui-btn-primary">Add user</a>
        </x-slot:actions>
    </x-page-header>

    The `title` no longer renders here. In the console layout the page name
    lives in the sticky header (partials/topbar), so printing it again at the
    top of the body would say the same thing twice. The prop is kept — every
    view passes it, and it is still the accessible name of the region — so no
    call site had to change.

    Breadcrumbs are separate — they render in the sticky header too; see
    partials/topbar.blade.php.
--}}
@props([
    'title',
    'subtitle' => null,
    'actions' => null,
])

@if ($subtitle || $actions)
    <div {{ $attributes->merge([
        'class' => 'mb-6 flex flex-wrap items-center gap-x-6 gap-y-3',
    ]) }} aria-label="{{ $title }}">
        @if ($subtitle)
            <p class="min-w-0 flex-1 basis-72 text-[13.5px] text-neutral-500">{{ $subtitle }}</p>
        @else
            <div class="flex-1"></div>
        @endif

        @if ($actions)
            <div class="flex flex-wrap items-center gap-2.5 max-sm:w-full [&>*]:max-sm:flex-1">
                {{ $actions }}
            </div>
        @endif
    </div>
@endif
