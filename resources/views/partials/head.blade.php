{{--
    Document head.

    The entire previous asset stack is gone: bootstrap.css, neon-core/theme/
    forms.css, datatable.css, custom.css, bascon-ui.css and the jQuery UI
    stylesheet are no longer loaded. Styling is now a single Tailwind v4 bundle
    compiled by Vite from resources/css/app.css.

    What is still loaded as classic (non-module) scripts, and why — this is
    exactly what the views call by global name, nothing more:

      jQuery         ~90 AJAX handlers across the views are written against it
      jQuery UI      every date field calls .datepicker(); this must remain the
                     CUSTOM build, the "minimal" one omits the datepicker widget
      DataTables     23 views build tables with it (+ Buttons/JSZip for the four
                     that configure print/excel export)
      SweetAlert2    every create/update/delete confirmation

    bootstrap.js is NOT loaded. The app only ever used its tab and modal
    plugins; both are reimplemented in resources/js/ui.js against the same
    jQuery call signatures, so no view JavaScript had to change.

    Ordering matters: the classic scripts above execute during parsing, while
    the @vite bundle is a deferred module that runs after them but before
    DOMContentLoaded — so ui.js can install its shims before any of the views'
    $(document).ready callbacks fire.
--}}
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<meta name="description" content="BASCON Group — project and payment management" />
<meta name="author" content="BASCON Group" />
<meta name="theme-color" content="#f4f4f5">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="icon" href="{{ asset('assets/images/favicon.png') }}">

<title>@yield('title', 'BASCON GROUP')</title>

{{--
    Theme bootstrap. This has to be inline, in the head, and before the
    stylesheet, or the page paints light for one frame and then flips — the
    flash is far more objectionable than the extra ten lines.

    Light is the default; dark is only applied when the user has chosen it.
    The switch itself lives in the topbar and is wired up in resources/js/ui.js.

    A page can opt out entirely with data-theme-lock on its <html> element —
    the sign-in screens do, being designed compositions rather than surfaces
    the theme tokens should repaint. ui.js honours the same attribute.
--}}
<script>
    (function () {
        var root = document.documentElement;
        if (root.hasAttribute('data-theme-lock')) return;

        try {
            if (localStorage.getItem('bascon:theme') === 'dark') {
                root.classList.add('dark');
                var meta = document.querySelector('meta[name="theme-color"]');
                if (meta) meta.setAttribute('content', '#0a0a0d');
            }
        } catch (e) { /* private mode — stay light */ }
    })();
</script>

{{--
    Sidebar rail bootstrap, and it has to be inline here for the same reason
    the theme does.

    The collapsed rail used to be restored in ui.js at DOMContentLoaded. By
    then the sidebar had already painted at its full 268px, and .app-sidebar
    carries a 300ms width transition — so every single page load visibly
    ANIMATED the navigation shut. It read as the sidebar closing itself each
    time you opened a page.

    Applied before first paint the panel simply renders at its final width,
    and a transition never starts. The class goes on <html> rather than <body>
    because <body> does not exist yet at this point in the parse; the
    stylesheet's rules are written as descendant selectors so either host
    element works, and ui.js toggles the same one.

    Not gated on data-theme-lock: the sign-in screens have no sidebar, so
    there is nothing here for that attribute to protect.
--}}
<script>
    (function () {
        try {
            if (localStorage.getItem('bascon:sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) { /* private mode — start expanded */ }
    })();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

<script src="{{ asset('assets/js/jquery-1.11.3.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui/js/jquery-ui-1.10.3.custom.min.js') }}"></script>

<script>
    // The legacy app sent no CSRF token on any of its ~90 AJAX calls.
    // Registering it once here covers every $.ajax on every page.
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

@stack('styles')
