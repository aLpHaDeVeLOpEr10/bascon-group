@extends('layouts.admin')

@section('title', 'Payment Requests · BASCON GROUP')

@section('breadcrumbs')
    <span>Request</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Payment Request</span>
@endsection

@section('content')
<x-page-header title="Payment Requests"
               subtitle="Payments a worker has recorded that are waiting on you. Approved payments count towards the site's balance; rejected ones never do." />

<x-card flush>
    <table id="payment_requests" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Site</th>
                <th>Date</th>
                <th>Source</th>
                <th class="text-right">Amount</th>
                <th class="w-px whitespace-nowrap text-right">Action</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        var table = $('#payment_requests').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/get_payment_requests') }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            "language": { "emptyTable": "No payments are waiting for approval" },
            "columns": [
                { "data": "id" },
                { "data": "site" },
                { "data": "date" },
                { "data": "source" },
                { "data": "payment", "className": "text-right" },
                {
                    "data": null,
                    "orderable": false,
                    // nowrap on the cell and a flex row inside it: the column is
                    // sized to its content, so without both the pair stacks.
                    "className": "whitespace-nowrap text-right",
                    "render": function (data) {
                        return '<div class="ui-table-actions">' +
                               '<button type="button" class="ui-btn ui-btn-sm ui-btn-primary" onclick="decidePayment(' + data.id + ', true)">Approve</button>' +
                               '<button type="button" class="ui-btn ui-btn-sm ui-btn-danger-soft" onclick="decidePayment(' + data.id + ', false)">Reject</button>' +
                               '</div>';
                    }
                }
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).attr("id", 'tr_' + data.id);
                $('td:eq(0)', row).html(dataIndex + 1);
            }
        });

        window.decidePayment = function (paymentId, approve) {
            Swal.fire({
                title: approve ? 'Approve this payment?' : 'Reject this payment?',
                text: approve
                    ? 'It will count towards the site’s balance.'
                    : 'It will not count towards the site’s balance.',
                icon: approve ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: approve ? 'Approve' : 'Reject'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Saving…',
                    allowOutsideClick: false,
                    onBeforeOpen: function () { Swal.showLoading(); }
                });

                $.ajax({
                    url: approve
                        ? "{{ url('admin_setting/accept_payment') }}"
                        : "{{ url('admin_setting/reject_payment') }}",
                    type: 'POST',
                    data: { userId: paymentId },
                    dataType: 'json',
                    success: function (response) {
                        if (!response.success) {
                            Swal.fire('Error', 'Could not update the payment.', 'error');
                            return;
                        }

                        Swal.fire(approve ? 'Approved' : 'Rejected', '', 'success')
                            .then(function () { table.ajax.reload(); });
                    },
                    error: function () {
                        Swal.fire('Error', 'Error in AJAX request. Please try again later.', 'error');
                    }
                });
            });
        };
    });
</script>
@endpush
