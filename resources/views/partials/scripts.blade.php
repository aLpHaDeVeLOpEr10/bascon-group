
{{-- SweetAlert2 is gone. It was a render-blocking CDN script on every
     page; ui.js now provides a local `Swal` with the same API, so the
     views' 177 Swal.fire() calls are unchanged and open instantly. --}}
<script src="{{ asset('assets/js/datatable.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>

@stack('scripts')
