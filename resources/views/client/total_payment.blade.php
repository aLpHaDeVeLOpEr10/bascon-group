@extends('layouts.app')

@section('title', 'Grand Total · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Grand Total</span>
@endsection

@section('content')
<x-page-header title="Grand Total"
               subtitle="Everything charged to your project, and what is still outstanding." />

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-stat-card label="Payments received" :value="money($payment_recieved)" wash icon="check" tone="success" />
    {{-- Danger below zero: the same balance, overdrawn. --}}
    <x-stat-card label="Remaining balance" :value="money($Remainung_Balace)" icon="clock"
                 :tone="$Remainung_Balace < 0 ? 'danger' : 'success'" wash />
</div>

<x-card flush>
    <table id="grand_account" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Type</th>
                <th>Total</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
            $(document).ready(function() {
                var dataTable = $('#grand_account').DataTable({
        dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
        buttons: [{
                extend: 'print',
                text: 'Print Record',
                className: 'dt-button',
                exportOptions: {
                    columns: ':visible' // Export only visible columns
                },
                customize: function(win) {
                        // Add custom content to the print view
                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                    }
            },
            {
                extend: 'excel',
                text: 'Download Excel',
                className: 'dt-button',
                filename: 'data_export'
            }
        ]
    });

    // Add a new row with values
    dataTable.row.add([
        '1',
        'Civil Total',
        '@money($civil_price)',
    ]).draw();

    dataTable.row.add([
        '2',
        'Finish Total',
        '@money($finish_price)',
    ]).draw();

    dataTable.row.add([
        '3',
        'Labour total',
        '@money($labour_price)',
    ]).draw();

    dataTable.row.add([
        '4',
        'Miscellaneous Total',
        '@money($misc_price)',
    ]).draw();

    dataTable.row.add([
        '<h3>5</h3>',
        '<h3>Grand Total</h3>',
        '<h3>@money($Grand_total)</h3>',
    ]).draw();

            });
        </script>
@endpush
