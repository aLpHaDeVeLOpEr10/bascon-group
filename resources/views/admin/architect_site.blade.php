@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Site Details</strong>
                </li>
            </ol>

            <h2>
                {{ $name }}
            </h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary " style="padding-bottom: 30px;">


                        <div>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Add Installment</a></li>
                                <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Payment Recieved</a></li>

                            </ul>
                        </div>

                        <!-- Tab panes -->
                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <form role="form" class="form-horizontal" id="brick_addition_form" action="{{ url('admin_setting/Add_brick') }}">



                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">Fee</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="price" id="field-1" placeholder="Add Fee" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">source</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="source" id="field-1" placeholder="Add Source" required>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label for="field-2" class="col-sm-3 control-label">Date</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="selected_date" id="datepicker" placeholder="Select a date" required>
                                        </div>
                                    </div>
                                    <div class="form-group">

                                        
                                        <div class="col-sm-5">
                                            <input type="hidden" class="form-control" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-3 col-sm-5">
                                            <button type="submit" class="btn btn-default submit-form">Add</button>
                                        </div>
                                    </div>
                                </form>







                            </div>
                            <!-- End A_category -->



                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_category">


                                <div style="margin-top:30px;" id="total_price_b">

                                </div>
                                <div class="container" style="width: 90%;margin-top: 15px;">


                                    <table id="b_categoer_table" width="100%" class="table table-bordered">

                                        <thead>
                                            <tr style="background-color: aliceblue;">
                                                <!-- Add your table headers here -->
                                                <th>Sr No</th>
                                                <th>Price</th>
                                                <th>Source</th>
                                                <th>Date</th>
                                                <th>Action</th>

                                            </tr>
                                        </thead>
                                    </table>
                                    <div style="margin-top:30px;" id="total_price_managments">
                                        <h4 style="display: inline; margin-left:30px;">Total Fee:{{ $total_fee }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspTotal Instalments:{{ $total_instalments }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspRemaing Ammount: {{ $remaing_instalment }}</h4>
                                    </div>
                                </div>

                            </div>
                        </div>



                        <!-- End B_category -->





                    </div>

                </div>
            </div>
        </div>
        <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Payment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Update form goes here -->
                        <form id="updateForm">

                            <div class="form-group">
                                <label for="field-1">Price</label>


                                <input type="text" class="form-control" name="payment" id="field-1" placeholder="Price" required>

                            </div>

                            <div class="form-group">
                                <label for="field-1">Source</label>

                                <input type="text" class="form-control" name="source" id="field-1" placeholder="Source" required>

                                <input type="hidden" class="form-control" name="id" id="field-1" placeholder="Username" required>

                            </div>



                            <div class="form-group">
                                <label for="field-1">Date</label>


                                <input type="text" class="form-control" name="date" id="datepicker2" placeholder="Date" required>

                            </div>




                            <!-- Add other fields as needed -->

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    $(function() {
        $("#datepicker").datepicker();
    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#brick_addition_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="price"]').val('');
                        $('input[name="selected_date"]').val('');
                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Handle form submission
        $('#Bcategory_form').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Your form data
            var formData = $(this).serialize();

            // Ajax request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        swal.fire({
                            title: 'Success',
                            text: 'Data added successfully',
                            icon: 'success',
                            button: 'Ok',
                        });
                        $('input[name="brick_quantity"]').val('');
                        $('input[name="brick_price"]').val('');
                        // Additional success handling if needed
                    } else {
                        swal.fire({
                            title: 'Error',
                            text: 'Failed to add data',
                            icon: 'error',
                            button: 'Ok',
                        });
                        // Additional error handling if needed
                    }
                },
                error: function() {
                    swal.fire({
                        title: 'Error',
                        text: 'Error in Ajax request',
                        icon: 'error',
                        button: 'Ok',
                    });
                    // Additional error handling if needed
                }
            });
        });
    });
</script>
<script>
    $('#payment_recieved').click(function() {
        if ($.fn.DataTable.isDataTable('#b_categoer_table')) {
            $('#b_categoer_table').DataTable().destroy();
        }

        var table = $('#b_categoer_table').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/show_b_category/' . $const_id) }}",
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
                },
                {
                    // New column for delete and update buttons
                    "data": null,
                    "render": function(data, type, row) {
                        // 'data' parameter contains the row data
                        return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal(' + data.id + ')"></i>&nbsp' +
                            '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deleteUser(' + data.id + ')"></i>';

                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'print',
                    text: 'Print DataTable',
                    className: 'btn btn-secondary',
                    customize: function(win) {
                        // Add custom content to the print view
                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');

                        // Add a custom footer with total fee information
                        var footerContent = '<tfoot><tr><td colspan="3">Total Fee: {{ $total_fee }} &nbsp;&nbsp;&nbsp; Total Instalments: {{ $total_instalments }} &nbsp;&nbsp;&nbsp; Remaing Amount: {{ $remaing_instalment }}</td></tr></tfoot>';
                        $(win.document.body).find('table').append(footerContent);
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
    $(document).ready(function() {
        $('#updateForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('admin_setting/update_payment_archi') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#updateModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            // Reload the DataTable upon success
                            var oAllLinksTable = $('#b_categoer_table').DataTable();
                            oAllLinksTable.ajax.reload();
                        });
                    } else {
                        // Show error message with SweetAlert
                        $('#updateModal').modal('hide');
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to update user. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    // Show error message with SweetAlert
                    $('#updateModal').modal('hide');
                    Swal.fire({
                        title: 'Error',
                        text: 'Error in AJAX request. Please try again later.',
                        icon: 'error',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });

    function openUpdateModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('Admin_setting/get_payment_arcchi') }}",
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#updateForm input[name="id"]').val(response.data.id);
                            $('#updateForm input[name="payment"]').val(response.data.payment);
                            $('#updateForm input[name="source"]').val(response.data.source);
                            $('#updateForm input[name="date"]').val(response.data.date);



                            // Show the modal
                            $('#updateModal').modal('show');
                        } else {
                            Swal.fire('Error', 'Failed to fetch user details', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error in AJAX request', 'error');
                    }
                });
            }
        });
    }

    function deleteUser(userId) {
        // Use SweetAlert for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this record!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            width: '600px',

        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request
                $.ajax({
                    url: "{{ url('admin_setting/delete_payment_archi') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Remove the deleted row from DataTable
                            var rowId = '#tr_' + userId;
                            var table = $('#b_categoer_table').DataTable();
                            table.row(rowId).remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'User has been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete user. Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error in AJAX request. Please try again later.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>
@endpush
