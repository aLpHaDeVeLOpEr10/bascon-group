@extends('layouts.app')

@section('title', 'Project Management Fee · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('client/total_payment') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Project Management Fee</span>
@endsection

@section('content')
<x-page-header title="Project Management Fee"
               subtitle="Management fees and the instalments paid against them." />

{{-- Both figures come straight from the controller, as before — only the
     presentation changed. --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-stat-card label="Project done" :value="money($total_fee)" icon="wallet" />
    <x-stat-card label="Total instalments" :value="money($total_instalments)" icon="check" tone="success" />
    <x-stat-card label="Remaining instalments" :value="money($remaing_instalment)" icon="clock" tone="warning" />
</div>

<x-card flush>
    <table id="b_categoer_table" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Fee</th>
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
                $('#b_categoer_table').DataTable({
        "ajax": {
            "url": "{{ url('client/show_con_detail/' . $id) }}",
            "type": "GET",
            "dataType": "json",
            "dataSrc": "data"
        },
        "columns": [
            { "data": null, "render": function (data, type, row, meta) { return meta.row + 1; } }, // Custom serial number
            { "data": "payment" },
            { "data": "source" },
            { "data": "date" }
        ],
        dom: '<"ui-dt-bar"Bf>rt<"ui-dt-foot"ip>',
        buttons: [
            {
                extend: 'print',
                text: 'Print DataTable',
                className: 'dt-button',
                customize: function(win) {
                    // Add custom content to the print view
                    $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                    $(win.document.body).find('table').prepend('<tfoot><tr><td colspan="3">Total Fee: @money($total_fee)   &nbsp&nbsp&nbsp&nbspTotal Instalments: @money($total_instalments) </td></tr></tfoot>');
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
