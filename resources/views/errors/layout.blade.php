{{--
    Shared chrome for the error pages. Laravel resolves errors/{status}.blade.php
    automatically, so 404/403/419/500/503 all extend this.

    Deliberately standalone — an error can happen before or outside a session,
    so it must not depend on the authenticated shell.
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
    @section('title', $code . ' · BASCON GROUP')
</head>

<body class="min-h-screen bg-canvas">

    <div class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">

        {{-- Decorative backdrop, matching the sign-in pages. --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute -top-40 left-1/2 size-[38rem] -translate-x-1/2 rounded-full
                        bg-brand-300/20 blur-[110px]"></div>
            <div class="absolute inset-0
                        [background-image:radial-gradient(circle,rgb(9_9_11/0.07)_1px,transparent_1px)]
                        [background-size:22px_22px]
                        [mask-image:radial-gradient(ellipse_at_center,black_10%,transparent_70%)]"></div>
        </div>

        <div class="w-full max-w-lg text-center">

            {{-- Logo is unchanged, as required. --}}
            <img src="{{ asset('assets/images/bg.png') }}" alt="BASCON Group"
                 class="mx-auto mb-10 h-8 w-auto object-contain" />

            <div class="rounded-3xl bg-white p-9 shadow-pop ring-1 ring-neutral-900/5">

                <span class="mx-auto mb-6 inline-flex size-14 items-center justify-center rounded-2xl
                             {{ $tone ?? 'bg-neutral-100 text-neutral-400' }} ring-1 ring-neutral-200/70">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        {!! $icon !!}
                    </svg>
                </span>

                <p class="text-[13px] font-semibold uppercase tracking-[0.12em] text-neutral-400">
                    Error {{ $code }}
                </p>

                <h1 class="mt-2 text-[26px] font-semibold tracking-[-0.025em] text-neutral-900">
                    @yield('heading')
                </h1>

                <p class="mx-auto mt-3 max-w-sm text-[13.5px] leading-relaxed text-neutral-500">
                    @yield('message')
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-2.5">
                    <a href="{{ url('/') }}" class="ui-btn ui-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
                            <path d="M9 22V12h6v10" />
                        </svg>
                        Go home
                    </a>

                    <button type="button" onclick="history.back()" class="ui-btn ui-btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                        Go back
                    </button>
                </div>
            </div>

            <p class="mt-6 text-xs text-neutral-400">
                © {{ date('Y') }} BASCON Group. All rights reserved.
            </p>
        </div>
    </div>

</body>

</html>
