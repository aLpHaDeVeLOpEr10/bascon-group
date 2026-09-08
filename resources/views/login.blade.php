{{--
    Worker / client sign-in. Port of application/views/login.php.

    The form contract is unchanged: POST to login/login_user with the fields
    `username` (matched against the email column — see LoginController) and
    `password`, plus the CSRF token.

    Two notes on the controls, unchanged from the previous passes:

      - "Keep me signed in" is client-side. LoginController::authenticate calls
        Auth::attempt() without a remember flag and backend changes are out of
        scope, so the checkbox does what it honestly can: it stores the entered
        email in localStorage and prefills it next visit. It does NOT extend
        the session and it never stores the password.

      - There is no password-reset route in this application, so "Forgot
        password?" reveals an inline note rather than linking to a 404.

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
    @section('title', 'Sign in · BASCON GROUP')
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
                    Sign in to your dashboard to continue.
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

                <form action="{{ url('login/login_user') }}" method="POST" id="login-form"
                      class="mt-8 space-y-4" data-loading-on-submit>
                    @csrf

                    {{-- Email --}}
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500"
                              aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2" />
                                <path d="m2 7 10 6 10-6" />
                            </svg>
                        </span>

                        <input type="email" name="username" id="username"
                               value="{{ old('username') }}"
                               placeholder="Email address"
                               autocomplete="username" autofocus required
                               class="h-[68px] w-full rounded-xl border border-transparent bg-[#e8eefb] pl-13 pr-4
                                      text-[15px] text-neutral-900 transition
                                      placeholder:text-neutral-500
                                      focus:border-neutral-900/15 focus:bg-white focus:outline-none
                                      focus:ring-4 focus:ring-neutral-900/5" />
                        <label for="username" class="sr-only">Email address</label>
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
                            Password resets are handled by your administrator — please contact them to
                            have your password changed.
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
            <div class="hidden rounded-[0px_28px_28px_0px] bg-[#0b1120] px-11 py-12 text-white lg:flex lg:flex-col lg:justify-center">

                {{-- The existing site logo, unchanged. It sits on a white chip
                     so it stays legible against the dark panel whatever
                     colours the artwork uses. --}}
                <div class="mb-7 inline-flex w-fit px-5 py-4">
                    <img src="{{ asset('assets/images/bg.png') }}" alt="BASCON Group"
                         class="w-auto object-contain" />
                </div>

                <div class="mb-8 h-px w-28 bg-white/25 w-full"></div>

                <h2 class="text-[30px] font-bold leading-tight tracking-[-0.02em] text-white ">Project Dashboard</h2>

                <p class="mt-3 max-w-sm text-[15px] leading-relaxed text-white/60">
                    Record site materials and labour, and follow every payment on your projects
                    from one place.
                </p>

                <ul class="mt-10 space-y-0">
                    @php
                        // Copy reflects the modules this dashboard actually has.
                        $features = [
                            ['Civil &amp; finishing material records',
                             '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>'],
                            ['Labour instalments &amp; misc costs',
                             '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>'],
                            ['Payment history &amp; running totals',
                             '<path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5"/><path d="M17 12h.01"/>'],
                            ['Print &amp; Excel export on every table',
                             '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/>'],
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
            </div>
        </div>
    </div>

    @include('partials.scripts')

    <script>
        // "Keep me signed in" prefills the email on the next visit. See the
        // note at the top of this file: the password is never stored and the
        (function () {
            var KEY = 'bascon:remembered-email';
            var form = document.getElementById('login-form');
            var email = document.getElementById('username');
            var remember = document.getElementById('remember-me');
            if (!form || !email || !remember) return;

            var saved = null;
            try { saved = window.localStorage.getItem(KEY); } catch (e) { /* private mode */ }

            if (saved && !email.value) {
                email.value = saved;
                remember.checked = true;
                document.getElementById('password').focus();
            }

            form.addEventListener('submit', function () {
                try {
                    if (remember.checked) window.localStorage.setItem(KEY, email.value);
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
