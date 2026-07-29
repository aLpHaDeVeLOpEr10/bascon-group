{{--
    Worker / client shell.

    The layout is a "console": a full-height navigation panel pinned to the
    left on its own surface, and the working area beside it sitting directly on
    the canvas so that the cards inside it are the only elevated things on the
    page.

    Nothing structural is nested around @yield('content') beyond one <main>,
    because a few legacy views still emit unbalanced closing tags of their own.
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-canvas">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-100
              focus:rounded-xl focus:bg-surface focus:px-4 focus:py-2.5 focus:text-[13px]
              focus:font-semibold focus:shadow-pop">
        Skip to main content
    </a>

    @include('partials.sidebar')

    <div class="app-shell flex min-h-screen flex-col">

        @include('partials.topbar', ['logoutUrl' => url('Login/logout')])

        <main id="main-content" tabindex="-1" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>

    @include('partials.scripts')

</body>

</html>
