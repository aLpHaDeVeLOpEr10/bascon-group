{{--
    Replaces application/views/header_links.php.

    Fixes carried over from the original:
      - Assets used a hardcoded PHP constant `base_url` (constants.php:86)
        pointing at https://accounts.bascongroup.pk/, so a local copy loaded its
        CSS/JS from the live site. They are now asset() paths.
      - The Google Fonts link was malformed ("<base_url>//fonts.googleapis...")
        and never resolved; it is now a plain protocol-relative URL.
      - jQuery UI was loaded twice at two versions (1.12.1 from CDN plus a
        vendored 1.10.3 "minimal" build). Only the vendored copy is loaded now,
        but it must be the CUSTOM build: the minimal one omits the datepicker
        widget, and every date field on the site relies on it.
      - DataTables CSS came from CDN 1.11.5 while the JS was vendored 1.10.4.
        Both now come from the vendored copy.
--}}
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="description" content="Neon Admin Panel" />
<meta name="author" content="" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="icon" href="{{ asset('assets/images/favicon.png') }}">

<title>BASCON GROUP</title>

<link rel="stylesheet" href="{{ asset('assets/js/jquery-ui/css/no-theme/jquery-ui-1.10.3.custom.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/font-icons/entypo/css/entypo.css') }}">
<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatable.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/neon-core.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/neon-theme.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/neon-forms.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.1.1/css/buttons.dataTables.min.css">

<script src="{{ asset('assets/js/jquery-1.11.3.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui/js/jquery-ui-1.10.3.custom.min.js') }}"></script>

<script>
    // The legacy app sent no CSRF token on any of its ~90 AJAX calls.
    // Registering it once here covers every $.ajax on every page.
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
</script>

<style>
    .dt-button {
        background-color: aliceblue !important;
    }

    .dt-button:hover {
        background-color: aliceblue !important;
        color: black !important;
    }
</style>

@stack('styles')
