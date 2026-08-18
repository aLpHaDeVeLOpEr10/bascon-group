{{--
    Sticky console header.

    Replaces the topbar block that was copy-pasted verbatim into ~30
    CodeIgniter views. The public contract is unchanged: the layouts pass
    $logoutUrl (it differs between the two areas) and optionally $displayName.

    The page name is shown here rather than in the body, matching the reference
    console. It is resolved without touching a single view: every view already
    marks the last breadcrumb, so that label is reused. A view can override it
    with @section('page-title', '…').

    Breadcrumbs come from each page:

        @section('breadcrumbs')
            <a href="...">Section</a>
            <span data-crumb-sep>|</span>
            <span data-crumb-current>Current page</span>
        @endsection
--}}
@php
    /* Notifications. Both roles get the same bell, reading opposite ends of
       the same workflow:

         admin  — payments a worker has filed that are waiting on a decision
         worker — payments an admin has decided that the worker has not seen

       Queried here rather than passed in by every controller: the topbar is
       included by both layouts and there is no shared base controller to hang
       it on. It is one indexed COUNT plus at most five rows. */
    // Whichever guard is signed in, this is their picture (null = initials).
    $profileAvatar = auth('admin')->user()?->avatar_url
        ?? auth('web')->user()?->avatar_url;

    $notifications = collect();
    $notificationCount = 0;
    $notificationSites = collect();
    $avatarSeenUrl = null;
    $notificationEmpty = 'Requests and approvals will show up here.';
    $notificationSeenUrl = null;

    /* Profile pictures ride the same bell. Counted separately from payments
       because each links to its own screen, but folded into one badge — two
       bells would be worse than one. */
    $photoCount = 0;
    $photoDecision = null;

    if (auth('admin')->check()) {
        $photoCount = \App\Models\User::awaitingAvatar()->count();
    } elseif (auth('web')->check() && ! auth('web')->user()->isClient()) {
        $me = auth('web')->user();

        if ((int) $me->avatar_seen === 0) {
            $photoDecision = (int) $me->avatar_status === \App\Models\User::AVATAR_REJECTED
                ? 'rejected'
                : 'approved';
        }
    }

    if (auth('admin')->check()) {
        $notificationQuery = \App\Models\PaymentReceived::where('status', \App\Models\PaymentReceived::PENDING);
        $notificationHref = url('admin_setting/payment_requets');
    } elseif (auth('web')->check() && ! auth('web')->user()->isClient()) {
        $notificationQuery = \App\Models\PaymentReceived::whereIn('status', [
            \App\Models\PaymentReceived::LIVE,
            \App\Models\PaymentReceived::REJECTED,
        ])->where('decision_seen', 0);

        $notificationHref = url('construction/payments');

        // Opening the menu acknowledges them, so a decision is announced once.
        $notificationSeenUrl = url('construction/payments_seen');
        $avatarSeenUrl = url('construction/avatar_seen');
    }

    if (isset($notificationQuery)) {
        $notificationCount = (clone $notificationQuery)->count();
        $notifications = $notificationQuery->orderByDesc('id')->limit(5)->get();
        $notificationSites = \App\Models\Site::whereIn('id', $notifications->pluck('proj_id'))
            ->get()->keyBy('id');
    }

    $notificationCount += $photoCount + ($photoDecision ? 1 : 0);


    $logoutUrl = $logoutUrl ?? url('Login/logout');
    $displayName = $displayName ?? (auth('web')->user()->name ?? auth('admin')->user()->name ?? '');
    $displayName = trim((string) $displayName) !== '' ? trim((string) $displayName) : 'Account';
    $displayEmail = auth('web')->user()->email ?? auth('admin')->user()->email ?? null;

    // Page name: an explicit override first, otherwise the current breadcrumb,
    // otherwise the document title.
    $crumbs = trim($__env->yieldContent('breadcrumbs'));
    $pageTitle = trim($__env->yieldContent('page-title'));

    if ($pageTitle === '' && $crumbs !== '' && preg_match('/data-crumb-current[^>]*>(.*?)</s', $crumbs, $m)) {
        $pageTitle = trim(html_entity_decode(strip_tags($m[1])));
    }

    if ($pageTitle === '') {
        $pageTitle = trim($__env->yieldContent('title')) ?: 'Dashboard';
    }

    // Two-letter monogram. The theme referenced assets/images/thumb-1@2x.png,
    // which has never existed in this asset bundle — it 404s in the
    // CodeIgniter app too — so no <img> is emitted.
    $initials = collect(preg_split('/\s+/', $displayName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-line
               bg-surface/85 px-4 backdrop-blur-md sm:px-6">

    {{-- One control, two jobs: it opens the drawer on mobile and collapses the
         rail on desktop, so they are two buttons that never coexist. --}}
    <button type="button"
            data-sidebar-drawer
            aria-controls="app-sidebar"
            aria-expanded="false"
            aria-label="Open navigation menu"
            class="ui-icon-btn lg:hidden">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 6h18" /><path d="M3 12h18" /><path d="M3 18h18" />
        </svg>
    </button>

    <button type="button"
            data-sidebar-toggle
            aria-controls="app-sidebar"
            aria-expanded="true"
            aria-label="Collapse sidebar"
            title="Collapse sidebar"
            class="ui-icon-btn hidden lg:inline-flex">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 6h18" /><path d="M3 12h18" /><path d="M3 18h18" />
        </svg>
    </button>

    {{-- The visible page name is the last crumb, styled as the title — a
         separate <h1> beside the trail printed it twice. This keeps the
         heading in the document for screen readers and the outline without
         repeating it on screen.

         The trail reads "← Parent | This page". The arrow is drawn by CSS on
         the first link rather than written into each of the thirty-odd
         @section('breadcrumbs') blocks, so every page gets it and no view has
         to remember. --}}
    <h1 class="sr-only">{{ $pageTitle }}</h1>

    @if ($crumbs !== '')
        {{-- Always visible, unlike the old xl-only trail: it now carries the
             page name, which every width needs. --}}
        <nav aria-label="Breadcrumb" class="ui-crumbs flex min-w-0">
            {!! $crumbs !!}
        </nav>
    @else
        <span class="min-w-0 shrink truncate text-[15px] font-semibold tracking-[-0.02em] text-neutral-900">
            {{ $pageTitle }}
        </span>
    @endif

    <div class="flex-1"></div>

    {{-- Client-side navigation search: filters the links already rendered in
         the sidebar. No route or controller is involved. --}}
    <div class="relative hidden sm:block" data-search role="search">
        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
        </svg>

        <input type="search"
               placeholder="Search…"
               aria-label="Search pages"
               aria-autocomplete="list"
               aria-expanded="false"
               autocomplete="off"
               class="h-9 w-44 rounded-xl border border-line bg-elevated pl-9 pr-8 text-[13px]
                      text-neutral-900 transition-[width,background-color,border-color,box-shadow] duration-200
                      placeholder:text-neutral-400 focus:w-64 focus:border-brand-500 focus:bg-surface
                      focus:outline-none focus:ring-4 focus:ring-brand-600/10" />

        <button type="button"
                data-search-clear
                aria-label="Clear search"
                class="absolute right-1.5 top-1/2 hidden size-6 -translate-y-1/2 items-center justify-center
                       rounded-md text-neutral-400 transition-colors hover:bg-neutral-200 hover:text-neutral-700
                       [&:not(.hidden)]:flex">
            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6 6 18" /><path d="m6 6 12 12" />
            </svg>
        </button>

        <div data-search-results role="listbox" aria-label="Search results"
             class="ui-menu right-0 left-auto max-h-80 w-72 overflow-y-auto"></div>
    </div>

    {{-- Light / dark switch. Both glyphs live in the button and cross-fade, so
         it never reflows; the choice is stored by ui.js. --}}
    <button type="button"
            data-theme-toggle
            aria-label="Switch between light and dark theme"
            title="Switch theme"
            class="ui-icon-btn ui-theme-toggle">
        <svg data-icon="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2" /><path d="M12 20v2" /><path d="m4.93 4.93 1.41 1.41" />
            <path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" /><path d="M20 12h2" />
            <path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" />
        </svg>
        <svg data-icon="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
        </svg>
    </button>

    <div class="relative">
        <button type="button"
                data-menu-trigger
                aria-controls="topbar-notifications"
                aria-expanded="false"
                aria-haspopup="true"
                aria-label="{{ $notificationCount ? $notificationCount . ' notifications' : 'Notifications' }}"
                class="ui-icon-btn relative">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
            </svg>

            @if ($notificationCount)
                <span class="ui-badge-count">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
            @endif
        </button>

        <div class="ui-menu right-0 left-auto w-80" id="topbar-notifications" role="menu"
             @if ($notificationSeenUrl && $notificationCount) data-notifications-seen="{{ $notificationSeenUrl }}" @endif
             @if ($avatarSeenUrl && $photoDecision) data-avatar-seen="{{ $avatarSeenUrl }}" @endif>
            <div class="border-b border-line-soft px-2.5 pb-2.5 pt-2">
                <p class="text-[13px] font-semibold text-neutral-900">Notifications</p>
                <p class="text-xs text-neutral-500">
                    @if (! $notificationCount)
                        You're all caught up
                    @elseif (auth('admin')->check())
                        {{ $notificationCount }} payment{{ $notificationCount === 1 ? '' : 's' }} waiting for approval
                    @else
                        {{ $notificationCount }} payment{{ $notificationCount === 1 ? '' : 's' }} reviewed
                    @endif
                </p>
            </div>

            @if ($photoCount)
                <a href="{{ url('admin_setting/photo_requets') }}" class="ui-menu-item" role="menuitem">
                    <span class="ui-note-dot" aria-hidden="true"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-neutral-900">
                            {{ $photoCount }} profile picture{{ $photoCount === 1 ? '' : 's' }} to review
                        </span>
                        <span class="block truncate text-[11.5px] text-neutral-500">Waiting for approval</span>
                    </span>
                </a>
            @endif

            @if ($photoDecision)
                <a href="{{ url('construction/profile') }}" class="ui-menu-item" role="menuitem">
                    <span class="ui-note-dot {{ $photoDecision === 'rejected' ? 'is-rejected' : 'is-approved' }}"
                          aria-hidden="true"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-neutral-900">
                            Profile picture {{ $photoDecision }}
                        </span>
                        <span class="block truncate text-[11.5px] text-neutral-500">
                            {{ $photoDecision === 'approved' ? 'It is now shown on your profile.' : 'You can upload a different one.' }}
                        </span>
                    </span>
                </a>
            @endif

            @forelse ($notifications as $note)
                @php
                    // An admin sees a request; a worker sees the verdict on one.
                    $decided = ! auth('admin')->check();
                    $rejected = (int) $note->status === \App\Models\PaymentReceived::REJECTED;

                    /* A worker's notice is about one payment on one site, so it
                       opens that site's ledger rather than the site picker —
                       landing on the list left them to find the site again.
                       An admin's opens the queue, which is where the decision
                       is actually made. */
                    $noteHref = $decided
                        ? url('construction/payment_details/'.$note->proj_id)
                        : $notificationHref;
                @endphp

                <a href="{{ $noteHref }}" class="ui-menu-item" role="menuitem">
                    <span class="ui-note-dot {{ $decided && $rejected ? 'is-rejected' : ($decided ? 'is-approved' : '') }}"
                          aria-hidden="true"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-neutral-900">
                            @if ($decided)
                                Payment {{ $rejected ? 'rejected' : 'approved' }} · @money($note->payment)
                            @else
                                @money($note->payment) · {{ $note->source ?: 'Payment' }}
                            @endif
                        </span>
                        <span class="block truncate text-[11.5px] text-neutral-500">
                            {{ $notificationSites[$note->proj_id]->display_name ?? 'Site #'.$note->proj_id }}
                        </span>
                    </span>
                </a>
            @empty
                @if (! $photoCount && ! $photoDecision)
                <div class="ui-empty py-8">
                    <span class="ui-empty-art size-11">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                    </span>
                    <p class="ui-empty-title">Nothing new</p>
                    <p class="ui-empty-text">Requests and approvals will show up here.</p>
                </div>
                @endif
            @endforelse

            @if ($notificationCount > $notifications->count())
                <a href="{{ $notificationHref }}"
                   class="block border-t border-line-soft px-3 py-2.5 text-center text-[12.5px] font-medium text-brand-700">
                    View all {{ $notificationCount }}
                </a>
            @endif
        </div>
    </div>

    <div class="hidden h-6 w-px shrink-0 bg-neutral-200 sm:block" aria-hidden="true"></div>

    <div class="relative">
        <button type="button"
                data-menu-trigger
                aria-controls="topbar-user-menu"
                aria-expanded="false"
                aria-haspopup="true"
                class="flex h-9 items-center gap-2 rounded-xl pl-1 pr-2 transition-colors hover:bg-neutral-100">
            <span aria-hidden="true" class="ui-avatar size-7">@if ($profileAvatar)<img src="{{ $profileAvatar }}" alt="">@else{{ $initials }}@endif</span>
            <span class="hidden max-w-32 truncate text-[13px] font-semibold text-neutral-800 sm:block">
                {{ $displayName }}
            </span>
            <svg class="size-3.5 shrink-0 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div class="ui-menu right-0 left-auto" id="topbar-user-menu" role="menu">
            <div class="flex items-center gap-3 border-b border-line-soft px-2.5 pb-2.5 pt-2">
                <span aria-hidden="true" class="ui-avatar size-9 text-[13px]">@if ($profileAvatar)<img src="{{ $profileAvatar }}" alt="">@else{{ $initials }}@endif</span>
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-semibold text-neutral-900">{{ $displayName }}</p>
                    @if ($displayEmail)
                        <p class="truncate text-xs text-neutral-500">{{ $displayEmail }}</p>
                    @endif
                </div>
            </div>

            {{-- Profile lives per guard, so the link is chosen the same way the
                 avatar above it is. --}}
            @php
                $profileUrl = auth('admin')->check()
                    ? url('admin_setting/profile')
                    : (auth('web')->check()
                        ? (auth('web')->user()->isClient() ? url('client/profile') : url('construction/profile'))
                        : null);
            @endphp

            @if ($profileUrl)
                <a href="{{ $profileUrl }}" class="ui-menu-item mt-1.5" role="menuitem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                    </svg>
                    Profile
                </a>
            @endif

            <a href="{{ $logoutUrl }}" class="ui-menu-item is-danger mt-1.5" role="menuitem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="m16 17 5-5-5-5" /><path d="M21 12H9" />
                </svg>
                Log out
            </a>
        </div>
    </div>

    <a href="{{ $logoutUrl }}" class="ui-btn ui-btn-secondary hidden lg:inline-flex">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5" /><path d="M21 12H9" />
        </svg>
        Logout
    </a>
</header>

@push('scripts')
<script>
    /* Opening the bell acknowledges whatever is in it, so a decision is
       announced once rather than every page load. Fires at most once per page
       and only when there is something to clear — the attribute is absent
       otherwise. The badge is cleared straight away rather than waiting for
       the response, because the request cannot meaningfully fail from here and
       the count is rebuilt from the database on the next load anyway. */
    $(document).ready(function () {
        var menu = $('#topbar-notifications');
        if (!menu.length) return;

        // Either or both may be present — payments and the picture decision
        // are acknowledged by different endpoints.
        var url = menu.data('notifications-seen');
        var avatarUrl = menu.data('avatar-seen');
        if (!url && !avatarUrl) return;

        $('[aria-controls="topbar-notifications"]').one('click', function () {
            if (url) $.post(url);
            if (avatarUrl) $.post(avatarUrl);

            $('.ui-badge-count').remove();
        });
    });
</script>
@endpush
