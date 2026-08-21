{{--
    Back-office navigation. Replaces application/views/admin_sidebar.php.

    Same links, same order, same URLs as the legacy menu — including the
    original "Setiing" section, relabelled to "Settings" as display text only;
    the route it points at is unchanged.
--}}
@php
    $is = fn (string $path) => request()->is($path);
    $navName = trim((string) (auth('admin')->user()->Name ?? '')) ?: 'Administrator';
    $navInitials = collect(preg_split('/\s+/', $navName))
        ->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');

        
    /* A section is either a link (a `url`) or an expandable group (`items`).
       Dashboard is the landing page and has nothing under it, so a group with
       one child would have meant an extra click to reach the only thing in
       it. */
    $sections = [
        ['label' => 'Dashboard', 'icon' => 'activity', 'url' => 'admin_setting/dashboard'],
        ['label' => 'Registration', 'icon' => 'users', 'items' => [
            ['admin_setting/add_user',  'Add user'],
            ['admin_setting/show_user', 'Show User'],
        ]],
        /* Category is deliberately absent. The same three screens are on the
           worker side now (construction/add_civil and friends), which is where
           the catalogue is maintained. The admin routes and pages are still
           registered and reachable by URL — only the menu entry is hidden — so
           nothing breaks for a bookmarked link. */
        ['label' => 'Project Management', 'icon' => 'building', 'items' => [
            ['admin_setting/add_con_site',  'Add site'],
            ['admin_setting/show_con_site', 'Show site'],
        ]],
        ['label' => 'Architecture', 'icon' => 'compass', 'items' => [
            ['admin_setting/add_site',  'Add site'],
            ['admin_setting/show_site', 'Show site'],
        ]],
        ['label' => 'Request', 'icon' => 'inbox', 'items' => [
            ['admin_setting/civil_requets',  'Civil Request'],
            ['admin_setting/finish_requets', 'Finish Request'],
            ['admin_setting/payment_requets', 'Payment Request'],
            ['admin_setting/photo_requets',   'Photo Request'],
        ]],
        ['label' => 'Settings', 'icon' => 'settings', 'items' => [
            ['admin_setting/set_role', 'Manage'],
        ]],
        ['label' => 'Money Management', 'icon' => 'wallet', 'items' => [
            ['admin_setting/add_expense',  'Add Expense'],
            ['admin_setting/show_expense', 'Show Expense'],
        ]],
    ];

    $icons = [
        'users'    => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'activity' => '<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>',
        'layers'   => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
        'building' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'compass'  => '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>',
        'wallet'   => '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M17 12h.01"/>',
    ];
@endphp

<aside class="app-sidebar" id="app-sidebar" aria-label="Admin navigation">
    {{-- The light-on-dark colourway only; the panel is dark in both themes. --}}
    <div class="app-logo shrink-0 border-b border-line p-4">
        <a href="{{ url('admin_setting/dashboard') }}" class="block rounded-lg"
           aria-label="BASCON Group — admin home">
            <span data-logo-full class="block">
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="BASCON Group"
                     width="560" height="181" class="h-auto w-full object-contain" />
            </span>
            <span data-logo-mark class="hidden">
                <img src="{{ asset('assets/images/logo-mark-dark.png') }}" alt="BASCON Group"
                     width="240" height="68" class="h-auto w-full object-contain" />
            </span>
        </a>
    </div>

    <nav class="min-h-0 flex-1 space-y-0.5 overflow-y-auto px-3 pb-3">
        <p class="ui-nav-heading" data-sidebar-hide>Administration</p>

        @foreach ($sections as $section)
            @if (isset($section['url']))
                <a href="{{ url($section['url']) }}"
                   data-tip="{{ $section['label'] }}"
                   class="ui-nav-link {{ $is($section['url']) ? 'is-active' : '' }}"
                   @if ($is($section['url'])) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $icons[$section['icon']] !!}
                    </svg>
                    <span class="flex-1 truncate text-left" data-nav-label>{{ $section['label'] }}</span>
                </a>

                @continue
            @endif
            @php
                $groupActive = collect($section['items'])->contains(fn ($item) => $is($item[0]));
            @endphp

            <div class="ui-nav-group {{ $groupActive ? 'is-open' : '' }}">
                <button type="button"
                        data-nav-toggle
                        data-tip="{{ $section['label'] }}"
                        aria-expanded="{{ $groupActive ? 'true' : 'false' }}"
                        class="ui-nav-link ui-nav-parent w-full {{ $groupActive ? 'has-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $icons[$section['icon']] !!}
                    </svg>
                    <span class="flex-1 truncate text-left" data-nav-label>{{ $section['label'] }}</span>
                    <svg class="ui-nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>

                <div class="ui-nav-sub">
                    <div>
                        <div class="ml-[1.375rem] mt-1 mb-1 space-y-0.5 border-l border-neutral-200 pl-2">
                            @foreach ($section['items'] as [$path, $label])
                                <a href="{{ url($path) }}"
                                   class="ui-nav-sublink {{ $is($path) ? 'is-active' : '' }}"
                                   @if ($is($path)) aria-current="page" @endif>
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Account card. Collapsed to just the avatar on the icon rail. --}}
    <div class="shrink-0 border-t border-line p-3">
        <div class="ui-user-card">
            <span aria-hidden="true" class="ui-avatar size-9">@if (auth('admin')->user()?->avatar_url)<img src="{{ auth('admin')->user()?->avatar_url }}" alt="">@else{{ $navInitials }}@endif</span>

            <div class="min-w-0 flex-1" data-sidebar-hide>
                <p class="truncate text-[13px] font-semibold text-neutral-900">{{ $navName }}</p>
                <p class="truncate text-[11.5px] text-neutral-500">Administrator</p>
            </div>
            <a href="{{ url('admin/logout') }}"
               data-sidebar-hide
               aria-label="Log out"
               title="Log out"
               class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg text-neutral-400
                      transition-colors hover:bg-rose-50 hover:text-rose-600">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="m16 17 5-5-5-5" /><path d="M21 12H9" />
                </svg>
            </a>
        </div>
    </div>
</aside>
<div class="app-backdrop" data-sidebar-backdrop aria-hidden="true"></div>
