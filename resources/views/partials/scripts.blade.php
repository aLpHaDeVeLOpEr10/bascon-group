{{--
    Replaces application/views/footer_link.php.

    Fixes carried over from the original:
      - Two <link> tags had the base_url echoed into the rel attribute instead
        of href (rel="...stylesheet" rather than href), so the jvectormap and
        rickshaw stylesheets never loaded at all. Corrected below.
      - jQuery UI and jQuery are loaded once, in partials/head.
--}}
<link rel="stylesheet" href="{{ asset('assets/js/jvectormap/jquery-jvectormap-1.2.2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/js/rickshaw/rickshaw.min.css') }}">

<script src="{{ asset('assets/js/gsap/TweenMax.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/joinable.js') }}"></script>
<script src="{{ asset('assets/js/resizeable.js') }}"></script>
<script src="{{ asset('assets/js/neon-api.js') }}"></script>
<script src="{{ asset('assets/js/jvectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
<script src="{{ asset('assets/js/jvectormap/jquery-jvectormap-europe-merc-en.js') }}"></script>
<script src="{{ asset('assets/js/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('assets/js/rickshaw/vendor/d3.v3.js') }}"></script>
<script src="{{ asset('assets/js/rickshaw/rickshaw.min.js') }}"></script>
<script src="{{ asset('assets/js/raphael-min.js') }}"></script>
<script src="{{ asset('assets/js/morris.min.js') }}"></script>
<script src="{{ asset('assets/js/toastr.js') }}"></script>
<script src="{{ asset('assets/js/fullcalendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset('assets/js/neon-chat.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script src="{{ asset('assets/js/datatable.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>

<script src="{{ asset('assets/js/neon-custom.js') }}"></script>

@stack('scripts')
