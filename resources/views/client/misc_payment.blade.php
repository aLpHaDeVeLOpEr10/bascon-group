@extends('layouts.app')

@section('title', 'Miscellaneous Payments · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('client/total_payment') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Miscellaneous</span>
@endsection

@section('content')
<x-page-header title="Miscellaneous Payments"
               subtitle="One-off costs recorded against your project.">
    <x-slot:actions>
        {{-- Filled in by the AJAX callback below. --}}
        <div class="ui-total" id="misclanious_total"></div>
    </x-slot:actions>
</x-page-header>

<x-card flush>
    <table id="misc_table" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Detail</th>
                <th>Price</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
            $(document).ready(function() {
                $.ajax({
                    url: "{{ url('client/misc_payments/' . $id) }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        var total_price = response.total_price;
                        var sites_data = response.data;

                        // Add a new column with serial numbers starting from 1
                        sites_data.forEach(function(record, index) {
                            record.serial_number = index + 1;
                        });

                        $('#misclanious_total').html('<h3>Total Price: ' + money(total_price) + '</h3>');

                        // Initialize DataTable and store the instance in the variable
                        miscTable = $('#misc_table').DataTable({
                            "data": sites_data,
                            "columns": [{
                                    "data": "serial_number"
                                },
                                {
                                    "data": "detail"
                                },
                                {
                                    "data": "price"
                                },
                                {
                                    "data": "date"
                                },

                            ],
                            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                            buttons: [{
                                    extend: 'print',
                                    text: 'Print Record',
                                    className: 'dt-button',
                                    customize: function(win) {
                                        // Add custom content to the print view
                                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}"  style="width:500px;" /></div>');

                                        // Add total price to the print view
                                        $(win.document.body).find('table').prepend('<tfoot><tr><td></td><td></td><td>Total Price: ' + money(total_price) + '</td><td></td></tr></tfoot>');
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
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + status + " - " + error);
                    }
                });
            });
        </script>
@endpush
