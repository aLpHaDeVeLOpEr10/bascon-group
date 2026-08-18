@extends('layouts.app')

@section('title', 'Civil Material Payments · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('client/total_payment') }}">Payments</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Civil Materials</span>
@endsection

@section('content')
<x-page-header title="Civil Material Payments"
               subtitle="Civil materials recorded against your project.">
    <x-slot:actions>
        {{-- Filled in by the AJAX callback below. --}}
        <div class="ui-total" id="misclanious_total"></div>
    </x-slot:actions>
</x-page-header>

<x-card flush>
    <table id="show_payment" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Material</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
            $(document).ready(function() {
                $.ajax({
                    url: "{{ url('client/get_payment/' . $id) }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        var total_price = response.total_price;
                        var sites_data = response.data;
                        console.log(sites_data);


                        // Add a new column with serial numbers starting from 1
                        sites_data.forEach(function(record, index) {
                            record.serial_number = index + 1;
                        });

                        $('#misclanious_total').html('<h3>Total Price: ' + money(total_price) + '</h3>');

                        // Initialize DataTable and store the instance in the variable
                        miscTable = $('#show_payment').DataTable({
                            "data": sites_data,
                            "columns": [{
                                    "data": "id"
                                },
                                {
                                    "data": "name"
                                },
                                {
                                    "data": "quantity"
                                },
                                {
                                    "data": "price"
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
                                        $(win.document.body).find('table').prepend('<tfoot><tr><td></td><td></td><td></td><td>Total Price: ' + money(total_price) + '</td></tr></tfoot>');
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

                                // Set the content for the first cell (Sr No)
                                $('td:eq(0)', row).html(dataIndex + 1);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: " + status + " - " + error);
                    }
                });
            });
        </script>
@endpush
