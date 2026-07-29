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

                            <table id="grand_account" width="100%" class="table table-bordered">

                                <thead class="thead-dark">
                                    <tr style="background-color: aliceblue;">
                                        <!-- Add your table headers here -->
                                        <th>Sr No</th>
                                        <th>Type</th>
                                        <th>Total</th>

                                    </tr>
                                </thead>
                            </table>
                            <div style="margin-top:30px;" id="total_price_managments">
                                <h3 style="display: inline; margin-left:60px;">Payments Recieved: {{ $payment_recieved }}&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRemaining Balace: {{ $Remainung_Balace }} </h3>
                            </div>

                        </div>

                    </div>

                </div>
            </div>




        </div>
@endsection

@push('scripts')
<script>
            $(document).ready(function() {
                var dataTable = $('#grand_account').DataTable({
        dom: 'lBfrtip',
        buttons: [{
                extend: 'print',
                text: 'Print Record',
                className: 'btn btn-secondary',
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
                className: 'btn btn-primary',
                filename: 'data_export'
            }
        ]
    });

    // Add a new row with values
    dataTable.row.add([
        '1',
        'Civil Total',
        '{{ $civil_price }}',
    ]).draw();

    dataTable.row.add([
        '2',
        'Finish Total',
        '{{ $finish_price }}',
    ]).draw();

    dataTable.row.add([
        '3',
        'Labour total',
        '{{ $labour_price }}',
    ]).draw();

    dataTable.row.add([
        '4',
        'Miscellaneous Total',
        '{{ $misc_price }}',
    ]).draw();

    dataTable.row.add([
        '<h3>5</h3>',
        '<h3>Grand Total</h3>',
        '<h3>{{ $Grand_total }}</h3>',
    ]).draw();

            });
        </script>
@endpush
