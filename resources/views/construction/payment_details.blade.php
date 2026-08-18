@extends('layouts.app')

@section('title', 'Payments · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('construction/payments') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>{{ $name }}</span>
@endsection

@section('content')
<x-page-header :title="$name" subtitle="Payments received against this site." />

{{-- is-strip: this card's tabs are the full-width underlined strip that meets
     the card edge, not the nested segmented pill group, so the total aligns to
     that strip rather than to a pane's padding box. --}}
<div class="ui-card overflow-hidden ui-tab-total-host is-strip">
    {{-- Payments received is marked active in the markup: opening the ledger
         should show the ledger. ui.js only auto-opens the first tab of a strip
         where nothing is selected, so this wins without any script. --}}
    <ul class="ui-tabs" role="tablist">
        <li role="presentation"><a href="#add_payment" aria-controls="add_payment" role="tab" data-toggle="tab">Add payment</a></li>
        <li class="active" role="presentation"><a href="#show_payment" aria-controls="show_payment" role="tab" data-toggle="tab" aria-selected="true">Payments received</a></li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane" id="add_payment">
            <div class="mt-4">
                <form role="form" id="payment_form" action="{{ url('construction/save_payment') }}">
                    <input type="hidden" name="proj_id1" value="{{ $const_id }}">

                    <x-field label="Amount">
                        <input type="number" step="any" class="ui-input" name="price1"
                               placeholder="Add amount" required>
                    </x-field>

                    <x-field label="Source">
                        <select class="ui-select" name="source1">
                            <option value="Cash">Cash</option>
                            <option value="Online">Online</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </x-field>

                    <x-field label="Date">
                        <input type="text" class="ui-input datepicker" name="selected_date1"
                               placeholder="Select a date" autocomplete="off">
                    </x-field>

                    @if ($needsApproval)
                        <div class="ui-alert ui-alert-info mt-4">
                            Payments need an administrator's approval before they count towards this
                            site's balance. Yours will be filed as pending.
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
                        <button type="submit" class="ui-btn ui-btn-primary">Add payment</button>
                    </div>
                </form>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane active" id="show_payment">
            <div class="mt-4">
                <div class="ui-total mb-4">
                    <h3>Total received: @money($total_payments)</h3>
                    @if ($pending_payments > 0)
                        <h3 class="is-pending">Awaiting approval: @money($pending_payments)</h3>
                    @endif
                </div>

                {{-- No delete column: a worker may correct a payment but never
                     remove one. The route does not exist either — see
                     ConstructionController::payments. --}}
                <table id="payment_table" width="100%" class="ui-table">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Date</th>
                            <th>Source</th>
                            <th class="text-right">Amount</th>
                            <th>Status</th>
                            <th class="w-px whitespace-nowrap text-right">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="paymentModalLabel">Update payment</h2>
                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
            </div>

            <div class="modal-body">
                <form id="paymentUpdateForm">
                    <input type="hidden" name="id">

                    <x-field label="Amount">
                        <input type="number" step="any" class="ui-input" name="price1" required>
                    </x-field>

                    <x-field label="Source">
                        <select class="ui-select" name="source1">
                            <option value="Cash">Cash</option>
                            <option value="Online">Online</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </x-field>

                    <x-field label="Date">
                        <input type="text" class="ui-input datepicker" name="selected_date1" autocomplete="off">
                    </x-field>

                    @if ($needsApproval)
                        <div class="ui-alert ui-alert-info mt-4">
                            An edited payment goes back to the administrator for approval.
                        </div>
                    @endif

                    <div class="ui-form-actions is-end">
                        <button type="button" class="ui-btn ui-btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        /* Both date fields — the add form and the edit modal.

           dd/mm/yy, matching the rest of the app. payments_recieved was
           written mm/dd up to id 301 and dd/mm from here on; the admin's
           payment form was switched to match in the same change, so both
           forms now write one convention. NormalizeDates carries the
           boundary, exactly as it already does for material,
           labour_instalment and misc, which each made this same switch. */
        $('.datepicker').datepicker({ dateFormat: 'dd/mm/yy' });

        var table = $('#payment_table').DataTable({
            "ajax": {
                "url": "{{ url('construction/get_payments/' . $const_id) }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            // The server already returns newest first; without this
            // DataTables would re-sort on column 0 and undo it.
            "order": [],
            "columns": [
                { "data": "id" },
                {
                    "data": "date",
                    /* date is a VARCHAR of dd/mm/yyyy, which sorts
                       alphabetically — every 01/xx lands above every 02/xx
                       regardless of month. date_n is the DATE copy beside it,
                       so it is handed to DataTables for sorting while the
                       readable string is what gets drawn. */
                    "render": function (data, type, row) {
                        return type === 'display' ? data : (row.date_n || '');
                    }
                },
                { "data": "source" },
                { "data": "payment", "className": "text-right" },
                {
                    "data": "status",
                    "render": function (data) {
                        return Number(data) === 0
                            ? '<span class="ui-pill is-pending">Pending</span>'
                            : '<span class="ui-pill is-live">Approved</span>';
                    }
                },
                {
                    "data": null,
                    "render": function (data) {
                        return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openPaymentModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>';
                    }
                }
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).attr("id", 'tr_' + data.id);
                $('td:eq(0)', row).html(dataIndex + 1);
            }
        });

        // ------------------------------------------------------------- add
        $('#payment_form').submit(function (event) {
            event.preventDefault();

            var $submit = $(this).find('button[type="submit"]');
            if ($submit.prop('disabled')) return;

            var form = this;
            $submit.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                complete: function () {
                    $submit.prop('disabled', false).removeClass('is-loading');
                },
                success: function (response) {
                    if (!response.success) {
                        Swal.fire('Error', 'Failed to add the payment. Please try again.', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Saved',
                        text: @json($needsApproval)
                            ? 'The payment has been filed and is awaiting approval.'
                            : 'The payment has been recorded.',
                        icon: 'success'
                    }).then(function () {
                        form.reset();
                        table.ajax.reload();
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Error in AJAX request. Please try again later.', 'error');
                }
            });
        });

        // ------------------------------------------------------------ edit
        $('#paymentUpdateForm').submit(function (event) {
            event.preventDefault();

            var $submit = $(this).find('button[type="submit"]');
            if ($submit.prop('disabled')) return;

            $submit.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: "{{ url('construction/update_payment') }}",
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                complete: function () {
                    $submit.prop('disabled', false).removeClass('is-loading');
                },
                success: function (response) {
                    if (!response.success) {
                        Swal.fire('Error', 'Failed to update the payment.', 'error');
                        return;
                    }

                    $('#paymentModal').modal('hide');
                    Swal.fire('Updated', 'The payment has been updated.', 'success')
                        .then(function () { table.ajax.reload(); });
                },
                error: function () {
                    Swal.fire('Error', 'Error in AJAX request. Please try again later.', 'error');
                }
            });
        });
    });

    function openPaymentModal(paymentId) {
        Swal.fire({
            title: 'Loading…',
            allowOutsideClick: false,
            onBeforeOpen: function () {
                Swal.showLoading();

                $.ajax({
                    url: "{{ url('construction/get_payment_details') }}",
                    type: 'GET',
                    data: { userId: paymentId },
                    dataType: 'json',
                    success: function (response) {
                        Swal.close();

                        if (!response.success) {
                            Swal.fire('Error', 'Failed to fetch the payment.', 'error');
                            return;
                        }

                        $('#paymentUpdateForm input[name="id"]').val(response.data.id);
                        $('#paymentUpdateForm input[name="price1"]').val(response.data.payment);
                        $('#paymentUpdateForm select[name="source1"]').val(response.data.source);
                        $('#paymentUpdateForm input[name="selected_date1"]').val(response.data.date);

                        $('#paymentModal').modal('show');
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
