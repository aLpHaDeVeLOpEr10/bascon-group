@extends('layouts.app')

@push('styles')
<style>
    #show_payment td {
        border: 1px solid #A9A9A9;
        /* Change 'black' to your desired border color */
        font-family: 'Your Modern Font', sans-serif;
        /* Replace 'Your Modern Font' with your desired modern font */
        font-size: 16px;
        /* Adjust the font size as needed */
    }

    #show_payment th {
        border: 1px solid #A9A9A9;
        /* Change 'black' to your desired border color */
        font-family: 'Your Modern Font', sans-serif;
        /* Replace 'Your Modern Font' with your desired modern font */
        font-size: 16px;
        /* Adjust the font size as needed */
    }




    .action {
        width: 10% !important;
    }
</style>
@endpush

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong> Payment</strong>
                </li>
            </ol>

            <h2>Show Payment</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">


                        <div class="panel-body">
                            <div id="misclanious_total"></div>

                            <table id="show_payment" width="100%" class="table table-bordered" class="table">
                                <thead>

                                    <tr style="background-color: aliceblue;">

                                        <th>Sr No</th>
                                        <th>Material</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>

                    </div>

                </div>
            </div>




        </div>
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

                        $('#misclanious_total').html('<h3 style="display: inline; margin-left:0px;">Total Price: ' + total_price + '</h3>');

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
                            dom: 'lBfrtip',
                            buttons: [{
                                    extend: 'print',
                                    text: 'Print Record',
                                    className: 'btn btn-secondary',
                                    customize: function(win) {
                                        // Add custom content to the print view
                                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}"  style="width:500px;" /></div>');

                                        // Add total price to the print view
                                        $(win.document.body).find('table').prepend('<tfoot><tr><td></td><td></td><td></td><td>Total Price: ' + total_price + '</td></tr></tfoot>');
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: 'Download Excel',
                                    className: 'btn btn-primary',
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
