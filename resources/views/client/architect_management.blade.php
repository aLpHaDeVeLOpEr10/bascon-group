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

            <h2>Show Installments</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">


                        <div class="panel-body">

                            <table id="b_categoer_table" width="100%" class="table table-bordered">

                                <thead>
                                    <tr style="background-color: aliceblue;">
                                        <!-- Add your table headers here -->
                                        <th>Sr No</th>
                                        <th>Fee</th>
                                        <th>Source</th>
                                        <th>Date</th>

                                    </tr>
                                </thead>
                            </table>
                            <div style="margin-top:30px;" id="total_price_managments">
                                <h4 style="display: inline; margin-left:60px;">Total Fee:{{ $total_fee }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspTotal Instalments:{{ $total_instalments }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRemaining Instalments:{{ $remaing_instalment }}</h4>
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
                $('#b_categoer_table').DataTable({
        "ajax": {
            "url": "{{ url('client/show_arch_detail/' . $id) }}",
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
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'print',
                text: 'Print DataTable',
                className: 'btn btn-secondary',
                customize: function(win) {
                    // Add custom content to the print view
                    $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                    $(win.document.body).find('table').prepend('<tfoot><tr><td colspan="4">Total Fee: {{ $total_fee }}   &nbsp&nbsp&nbsp&nbspTotal Instalments: {{ $total_instalments }}&nbsp&nbsp&nbsp&nbspRemaining Instalments: {{ $remaing_instalment }} </td></tr></tfoot>');
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
        }
    });
            });
        </script>
@endpush
