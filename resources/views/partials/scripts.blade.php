{{--
    End-of-body vendor scripts.

    These are classic scripts on purpose: the views call them by global name
    ($, Swal, $.fn.DataTable), so they cannot be bundled into the ES module
    without rewriting every view's JavaScript.

    bootstrap.js is deliberately absent — see partials/head for the reasoning.
    jQuery and jQuery UI are loaded once, in partials/head.

    Everything removed in the earlier pass stays removed: gsap, joinable,
    resizeable, neon-api/custom/chat, jvectormap, sparkline, rickshaw + d3,
    raphael + morris, toastr and fullcalendar were all loaded on every page and
    referenced by none of them.
--}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="{{ asset('assets/js/datatable.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>

@stack('scripts')
