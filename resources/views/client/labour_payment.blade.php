@extends('layouts.app')

@section('title', 'Labour Payments · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('client/total_payment') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Labour</span>
@endsection

@section('content')
<x-page-header title="Labour Payments"
               subtitle="Labour instalments recorded against your project." />

<x-card flush>
    <table id="show_payment" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Labour</th>
                <th>Installment</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        var oAllLinksTable = $('#show_payment').DataTable({
            "ajax": {
                "url": "{{ url('client/get_labour_payment/'.$id) }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            "columns": [
                { "data": "id" },
                { "data": "labour" },
                { "data": "instalment" },

            ],

            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                buttons: [
                    {
                        extend: 'print',
                        text: 'Print DataTable',
                        className: 'dt-button',
                        customize: function (win) {
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
                ],
            "createdRow": function (row, data, dataIndex) {
                // Set the ID for each row
                $(row).attr("id", 'tr_' + data.id);

                // Set the content for the first cell (Sr No)
                $('td:eq(0)', row).html(dataIndex + 1);
            }
        });
    });
</script>
@endpush
