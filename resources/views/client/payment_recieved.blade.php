@extends('layouts.app')

@section('title', 'Construction Payments · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('client/total_payment') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Construction Payments</span>
@endsection

@section('content')
<x-page-header title="Construction Payments"
               subtitle="Payments received against your construction contract." />

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-stat-card label="Total payments" :value="money($total_payments)" icon="wallet" tone="success" wash />
    {{-- The same balance the Grand Total tab shows, and read the same way:
         below zero is money still owed. --}}
    <x-stat-card label="Remaining balance" :value="money($Remainung_Balace)" icon="clock"
                 :tone="$Remainung_Balace < 0 ? 'danger' : 'success'" wash />
</div>

<x-card flush>
    <table id="b_categoer_table2342" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Price</th>
                <th>Source</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
            $(document).ready(function() {
                $('#b_categoer_table2342').DataTable({
                    "ajax": {
                        "url": "{{ url('client/show_con_detail12/' . $id) }}",
                        "type": "GET",
                        "dataType": "json",
                        "dataSrc": "data"
                    },
                    "columns": [{
                    "data": null,
                    "render": function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                }, // Custom serial number
                {
                    "data": "payment"
                },
                {
                    "data": "source"
                },
                {
                    "data": "date"
                }
            ],
            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
            buttons: [{
                    extend: 'print',
                    text: 'Print DataTable',
                    className: 'dt-button',
                    customize: function(win) {
                        // Add custom content to the print view
                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                        $(win.document.body).find('table').prepend('<tfoot><tr><td ></td><td >Total Payments: @money($total_payments)  </td><td ></td><td ></td></tr></tfoot>');
                    }
                },
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'dt-button',
                    filename: 'data_export'
                }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Set the ID for each row
                $(row).attr("id", 'tr_' + data.id);
            }
        });
    });
        </script>
@endpush
