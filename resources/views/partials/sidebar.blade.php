{{--
    Worker / client navigation.

    Replaces application/views/side_bar.php.

    The original ran its own query inside the view (side_bar.php:37) to fetch
    the signed-in user's role. That is taken from the auth guard instead.

    BEHAVIOUR CHANGE (deliberate, carried over from the CodeIgniter port): the
    original had two independent tests, `if ($role == 'Worker')` and
    `if ($role == 'Client')`, so a user whose role is neither — user 28,
    "Ahsan Bukhari", the busiest data-entry account — rendered an EMPTY sidebar
    with no navigation at all, while Construction.php:24 let that same user into
    the whole construction module because it only excluded the exact string
    'Client'. The menu follows the access rules: anyone who is not a Client
    gets the worker menu.

    This pass is presentation only — the same links render under exactly the
    same conditions.
--}}
@php
    $user = auth('web')->user();
    $navName = trim((string) ($user->name ?? '')) ?: 'Account';
    $navRole = trim((string) ($user->role ?? '')) ?: 'Worker';
    $navInitials = collect(preg_split('/\s+/', $navName))
        ->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    // request()->is() takes a path pattern; links are built with url(), so the
    // pattern is just the path portion of each one.
    $is = fn (string $path) => request()->is($path);

    $constructionActive = $is('construction/*');
    $clientActive = $is('client/*');

    $clientLinks = [
        ['client/show_payments',        'Civil Material Payments'],
        ['client/finish_payments',      'Finishing Material Payments'],
        ['client/labour_payment',       'Labour Payments'],
        ['client/misc_payment',         'Miscellaneous Payments'],
        ['client/project_mangement',    'Project Management Fee'],
        ['client/architect_management', 'Architect'],
        ['client/payment_recieved',     'Construction Payments'],
        ['client/total_payment',        'Grand Total'],
    ];
@endphp

<aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">

    {{--
        The logo is the same artwork in both themes, but it ships as two
        colourways: assets/images/bg.png is the WHITE one — which is why it
        vanished against a light sidebar — and logo_trans.png is the black one.
        Both originals are 18080x5840 and ~850KB, so the sidebar uses
        downscaled 560px copies (logo-dark / logo-light, ~15KB each) generated
        from them; the originals are untouched.

        Swapping them in CSS rather than JavaScript keeps the correct one
        painted on the very first frame.
    --}}
    <div class="app-logo shrink-0 border-b border-line p-4">
        <a href="{{ url('/') }}" class="block rounded-lg" aria-label="BASCON Group — home">
            <span data-logo-full class="block">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="BASCON Group"
                     width="560" height="181" class="h-auto w-full object-contain dark:hidden" />
                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" aria-hidden="true"
                     width="560" height="181" class="hidden h-auto w-full object-contain dark:block" />
            </span>

            <span data-logo-mark class="hidden">
                <img src="{{ asset('assets/images/logo-mark-light.png') }}" alt="BASCON Group"
                     width="240" height="68" class="h-auto w-full object-contain dark:hidden" />
                <img src="{{ asset('assets/images/logo-mark-dark.png') }}" alt="" aria-hidden="true"
                     width="240" height="68" class="hidden h-auto w-full object-contain dark:block" />
            </span>
        </a>
    </div>

    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 pb-3">

        @if ($user && ! $user->isClient())
            <p class="ui-nav-heading" data-sidebar-hide>Workspace</p>

            <div class="ui-nav-group {{ $constructionActive ? 'is-open' : '' }}">
                <button type="button"
                        data-nav-toggle
                        data-tip="Construction"
                        aria-expanded="{{ $constructionActive ? 'true' : 'false' }}"
                        class="ui-nav-link ui-nav-parent w-full {{ $constructionActive ? 'has-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 21v-6h6v6" />
                    </svg>
                    <span class="flex-1 truncate text-left" data-nav-label>Construction</span>
                    <svg class="ui-nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>

                <div class="ui-nav-sub">
                    <div>
                        <div class="ml-[1.375rem] mt-1 space-y-0.5 border-l border-neutral-200 pl-2">
                            <a href="{{ url('construction/show_site') }}"
                               class="ui-nav-sublink {{ $is('construction/show_site') || $is('construction/show_details/*') ? 'is-active' : '' }}"
                               @if ($is('construction/show_site')) aria-current="page" @endif>
                                Show Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($user && $user->isClient())
            <p class="ui-nav-heading" data-sidebar-hide>Payments</p>

            <div class="ui-nav-group {{ $clientActive ? 'is-open' : '' }}">
                <button type="button"
                        data-nav-toggle
                        data-tip="Payment Detail"
                        aria-expanded="{{ $clientActive ? 'true' : 'false' }}"
                        class="ui-nav-link ui-nav-parent w-full {{ $clientActive ? 'has-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" />
                    </svg>
                    <span class="flex-1 truncate text-left" data-nav-label>Payment Detail</span>
                    <svg class="ui-nav-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>

                <div class="ui-nav-sub">
                    <div>
                        <div class="ml-[1.375rem] mt-1 space-y-0.5 border-l border-neutral-200 pl-2">
                            @foreach ($clientLinks as [$path, $label])
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
        @endif
    </nav>
    {{-- Account card. Collapsed to just the avatar on the icon rail. --}}
    <div class="shrink-0 border-t border-line p-3">
        <div class="ui-user-card">
            <span aria-hidden="true" class="ui-avatar size-9">{{ $navInitials }}</span>

            <div class="min-w-0 flex-1" data-sidebar-hide>
                <p class="truncate text-[13px] font-semibold text-neutral-900">{{ $navName }}</p>
                <p class="truncate text-[11.5px] text-neutral-500">{{ $navRole }}</p>
            </div>

            <a href="{{ url('Login/logout') }}"
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
