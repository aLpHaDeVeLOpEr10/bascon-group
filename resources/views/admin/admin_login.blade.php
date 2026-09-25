{{--
    Back-office sign-in. Port of application/views/admin/admin_login.php.

    The form contract is unchanged: POST to admin/login_admin with `username`
    and `password` plus the CSRF token. The username field is type="text",
    matching the original — the admin guard does not require an email.

    See resources/views/login.blade.php for the notes on "Keep me signed in"
    and "Forgot password?"; both behave identically here.

    The logo sits in the brand panel only — deliberately not above the
    "Welcome Back!" heading.
--}}
<!DOCTYPE html>
{{-- Locked to the light theme: this page is a designed split panel with its own
     navy brand side, not a surface the theme tokens are meant to repaint. The
     lock is read by the inline bootstrap in partials/head and by ui.js. --}}
<html lang="en" data-theme-lock="light">

<head>
    @include('partials.head')
    @section('title', 'Admin sign in · BASCON GROUP')
</head>

<body class="min-h-screen bg-[#eef1fb]">

    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">

        <div class="grid w-full max-w-6xl rounded-[28px] bg-white shadow-[0_24px_70px_-20px_rgb(15_23_42/0.28)]
                    lg:grid-cols-2">

            {{-- ------------------------------------------------- form side --}}
            <div class="flex flex-col justify-center px-7 py-10 sm:px-12 sm:py-14">

                <h1 class="text-center text-[34px] font-extrabold leading-tight tracking-[-0.02em] text-neutral-900
                           sm:text-[38px]">
                    Welcome Back!
                </h1>
                <p class="mt-2 text-center text-[14px] text-neutral-500">
                    Sign in to your admin dashboard to continue.
                </p>

                @if ($errors->any() || session('error'))
                    <div class="ui-alert ui-alert-danger mt-7" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" /><path d="M12 8v4" /><path d="M12 16h.01" />
                        </svg>
                        <span>{{ session('error') ?: $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ url('admin/login_admin') }}" method="POST" id="login-form"
                      class="mt-8 space-y-4" data-loading-on-submit>
                    @csrf

                    {{-- Username / email --}}
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500"
                              aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m2 7 10 6 10-6" />
                            </svg>
                        </span>

                        <input type="text" name="username" id="username"
                               value="{{ old('username') }}"
                               placeholder="Username or email"
                               autocomplete="username" autofocus required
                               class="h-[68px] w-full rounded-xl border border-transparent bg-[#e8eefb] pl-13 pr-4
                                      text-[15px] text-neutral-900 transition
                                      placeholder:text-neutral-500
                                      focus:border-neutral-900/15 focus:bg-white focus:outline-none
                                      focus:ring-4 focus:ring-neutral-900/5" />
                        <label for="username" class="sr-only">Username or email</label>
                    </div>

                    {{-- Password --}}
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500"
                              aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>

                        <input type="password" name="password" id="password"
                               placeholder="Password"
                               autocomplete="current-password" required
                               class="h-[68px] w-full rounded-xl border border-transparent bg-[#e8eefb] pl-13 pr-14
                                      text-[15px] text-neutral-900 transition
                                      placeholder:text-neutral-500
                                      focus:border-neutral-900/15 focus:bg-white focus:outline-none
                                      focus:ring-4 focus:ring-neutral-900/5" />
                        <label for="password" class="sr-only">Password</label>

                        <button type="button" data-toggle-password="#password"
                                aria-label="Show password" aria-pressed="false"
                                class="absolute right-3 top-1/2 inline-flex size-9 -translate-y-1/2 items-center
                                       justify-center rounded-lg text-neutral-500 transition
                                       hover:bg-neutral-900/5 hover:text-neutral-800">
                            <svg data-icon-show class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg data-icon-hide class="hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a18.4 18.4 0 0 1-2.7 3.7" />
                                <path d="M6.6 6.6A18.5 18.5 0 0 0 2 12s3.5 7 10 7a10.7 10.7 0 0 0 5.4-1.4" />
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" /><path d="m2 2 20 20" />
                            </svg>
                        </button>
                    </div>

                    {{-- Options --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                        <label class="flex cursor-pointer select-none items-center gap-3 text-[15px] text-neutral-600">
                            {{-- .ui-check already carries the white tick as a
                                 background image; only the colours are
                                 overridden here to match the dark button. --}}
                            <input type="checkbox" id="remember-me"
                                   class="ui-check size-5 rounded-[4px] border-2 border-neutral-400
                                          hover:border-neutral-500
                                          checked:border-neutral-900 checked:bg-neutral-900
                                          focus-visible:ring-neutral-900/15" />
                            Keep me signed in
                        </label>

                        <a href="#" id="forgot-password-link"
                           class="text-[15px] font-medium text-emerald-700 underline underline-offset-4
                                  hover:text-emerald-800">
                            Forgot password?
                        </a>
                    </div>

                    <div id="forgot-password-note" class="ui-alert ui-alert-info hidden" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" />
                        </svg>
                        <span>
                            Administrator passwords are reset directly in the database — there is no
                            self-service reset for this account type.
                        </span>
                    </div>

                    <button type="submit"
                            class="ui-btn mt-3 h-[62px] w-full rounded-xl bg-[#0b1120] text-[17px] font-bold
                                   text-white shadow-sm transition hover:bg-[#161f34]
                                   focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-neutral-900/20">
                        <span>Sign In to Dashboard</span>
                    </button>
                </form>

                <p class="mt-9 border-t border-neutral-200 pt-6 text-center text-[13.5px] text-neutral-500">
                    © {{ date('Y') }} BASCON Group. All rights reserved.
                </p>
            </div>

            {{-- ------------------------------------------------ brand side --}}
            {{-- Hidden below lg so the form gets the full width on mobile. --}}
            <div class="hidden rounded-[0px_28px_28px_0px] bg-[#0b1120] px-11 py-12 text-white lg:flex lg:flex-col lg:justify-center">

                {{-- The existing site logo, unchanged. It sits on a white chip
                     so it stays legible against the dark panel whatever
                     colours the artwork uses. --}}
                <div class="mb-7 inline-flex w-fit px-5 py-4">
                    <img src="{{ asset('assets/images/bg.png') }}" alt="BASCON Group"
                         class="w-auto object-contain" />
                </div>

                <div class="mb-8 h-px w-28 bg-white/25"></div>

                <h2 class="text-[30px] font-bold leading-tight tracking-[-0.02em] text-white">Admin Panel</h2>

                <p class="mt-3 max-w-sm text-[15px] leading-relaxed text-white/60">
                    Manage users, projects, material catalogues and expenses securely from
                    one place.
                </p>

                <ul class="mt-10 space-y-0">
                    @php
                        // Copy reflects the modules this dashboard actually has:
                        // Registration, Category, Project Management, Architecture,
                        // Request and Money Management.
                        $features = [
                            ['User accounts, roles &amp; project access',
                             '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                            ['Construction &amp; architecture sites',
                             '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>'],
                            ['Material requests &amp; approvals',
                             '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/>'],
                            ['Expenses, fees &amp; payment tracking',
                             '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M17 12h.01"/>'],
                        ];
                    @endphp

                    @foreach ($features as $i => [$label, $icon])
                        <li class="flex items-center gap-4 py-5 {{ $i > 0 ? 'border-t border-white/10' : '' }}">
                            <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl
                                         bg-white/8 ring-1 ring-white/12" aria-hidden="true">
                                <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    {!! $icon !!}
                                </svg>
                            </span>
                            <span class="text-[15px] leading-snug text-white/90">{!! $label !!}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-10 text-[13px] text-white/35">
                    Not an administrator? <a href="{{ url('login') }}" class="text-white/70 underline underline-offset-4
                        hover:text-white">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    @include('partials.scripts')

    <script>
        // "Keep me signed in" prefills the username on the next visit. The
        // password is never stored and the session lifetime is unchanged.
        (function () {
            var KEY = 'bascon:remembered-admin';
            var form = document.getElementById('login-form');
            var username = document.getElementById('username');
            var remember = document.getElementById('remember-me');
            if (!form || !username || !remember) return;

            var saved = null;
            try { saved = window.localStorage.getItem(KEY); } catch (e) { /* private mode */ }

            if (saved && !username.value) {
                username.value = saved;
                remember.checked = true;
                document.getElementById('password').focus();
            }

            form.addEventListener('submit', function () {
                try {
                    if (remember.checked) window.localStorage.setItem(KEY, username.value);
                    else window.localStorage.removeItem(KEY);
                } catch (e) { /* private mode */ }
            });
        })();

        (function () {
            var link = document.getElementById('forgot-password-link');
            var note = document.getElementById('forgot-password-note');
            if (!link || !note) return;

            link.addEventListener('click', function (e) {
                e.preventDefault();
                var open = !note.classList.contains('hidden');
                note.classList.toggle('hidden', open);
                link.setAttribute('aria-expanded', open ? 'false' : 'true');
            });
        })();
    </script>

</body>

</html>
