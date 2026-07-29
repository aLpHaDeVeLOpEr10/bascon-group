@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Show Expense</strong>
                </li>
            </ol>

            <h2>Show Expense</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0" style="padding: 0px 20px;">
                        <div>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Show Expense</a></li>
                                <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Show Miscellaneous Credit</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="A_category">
                                <table id="show_expense" width="100%" style="white-space: nowrap;" class="table ">
                                    <thead>

                                        <tr style="background-color: aliceblue;">
                                            </th>
                                            <th>Sr No</th>
                                            <th>Date</th>
                                            <th>Ammount</th>
                                            <th>Detail</th>
                                            <th>Type</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                </table>
                                <div style="margin-top:25px; padding-bottom:15px;"><span style="font-size: 14px; font-weight: bold;">Total Amount: </span> <span style="font-size: 14px;">{{ $total_price }}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 14px; font-weight: bold;">Total Expense: </span> <span style="font-size: 14px;"> {{ $expense_total }}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 14px; font-weight: bold;">Remainig Total: </span> <span style="font-size: 14px;"> {{ $total_remaining }}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br><br><span style="margin-top:10px ;font-size: 14px; font-weight: bold;">Amount recieved by Hussnian: </span> <span style="font-size: 14px;"> {{ $hussnain_total }}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 14px; font-weight: bold;">Amount recieved by Basharat: </span> <span style="font-size: 14px;"> {{ $basharat_total }}</span></div>

                            </div>
                            <div role="tabpanel" class="tab-pane" id="B_category">
                                <table id="show_expense1" width="100%" style="white-space: nowrap;" class="table ">
                                    <thead>

                                        <tr style="background-color: aliceblue;">
                                            </th>
                                            <th>Sr No</th>
                                            <th>Date</th>
                                            <th>Detail</th>
                                            <th>Amount</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                </table>
                                <div style="margin-top:25px; padding-bottom:15px;"><span style="font-size: 14px; font-weight: bold;">Total Credit: </span> <span style="font-size: 14px;">{{ $misc_total }}</span></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>




    </div>

    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Update form goes here -->
                    <form id="updateForm">

                        <div class="form-group">
                            <label for="field-1" class="col-sm-12 control-label">Select </label>

                            <div class="col-sm-12">
                                <select class="form-control" id="type" class="col-sm-12" name="type">
                                    <option>
                                        Select
                                    </option>
                                    <option value="expense">
                                        Expense
                                    </option>

                                    <option value="hussnain">
                                        Hussnain
                                    </option>

                                    <option value="basharat">
                                        Basharat
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-1" class="col-sm-12  control-label">Amount</label>

                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="ammount" id="field-1" placeholder="Add Amount" required>
                                <input type="hidden" class="form-control" name="id" id="field-1" required>
                            </div>
                        </div>



                        <div class="form-group">
                            <label for="field-2" class="col-sm-12 control-label">Date</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="date" id="datepicker" placeholder="Select a date" required>
                            </div>
                        </div>


                        <!-- Add other fields as needed -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="margin-left:15px;margin-top:10px;">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>






    <div class="modal fade" id="updateModal1" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Miscellaneous</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Update form goes here -->
                    <form id="updateForm1">


                        <div class="form-group">
                            <label for="field-1" class="col-sm-3 control-label">Detail </label>

                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="detail" id="detail" placeholder="Add Detail" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-1" class="col-sm-12  control-label">Amount</label>

                            <div class="col-sm-12">
                            <input type="text" class="form-control" name="Amount" id="Amount" placeholder="Add Amount" required>
                                <input type="hidden" class="form-control" name="id1" id="id1" required>
                            </div>
                        </div>



                        <div class="form-group">
                            <label for="field-2" class="col-sm-12 control-label">Date</label>
                            <div class="col-sm-12">
                            <input type="text" class="form-control" name="Selected_date" id="datepicker1" placeholder="Select a date" required>
                            </div>
                        </div>


                        <!-- Add other fields as needed -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="margin-left:15px;margin-top:10px;">Update</button>
                        </div>
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

        $(function() {
            $("#datepicker1").datepicker();
        });
        $(document).ready(function() {

            var oAllLinksTable = $('#show_expense').DataTable({
                "ajax": {
                    "url": "{{ url('admin_setting/get_expense') }}",
                    "type": "GET",
                    "dataType": "json",
                    "dataSrc": "data"
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "ammount"
                    },
                    {
                        "data": "detail"
                    },
                    {
                        "data": "type"
                    },
                    {
                        // New column for delete and update buttons
                        "data": null,
                        "render": function(data, type, row) {
                            // 'data' parameter contains the row data
                            return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal(' + data.id + ')"></i>&nbsp' +
                                '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="expensedelete(' + data.id + ')"></i>';

                        }
                    }
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



            var oAllLinksTable = $('#show_expense1').DataTable({
                "ajax": {
                    "url": "{{ url('admin_setting/get_misc_admin') }}",
                    "type": "GET",
                    "dataType": "json",
                    "dataSrc": "data"
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "detail"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        // New column for delete and update buttons
                        "data": null,
                        "render": function(data, type, row) {
                            // 'data' parameter contains the row data
                            return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal1(' + data.id + ')"></i>&nbsp' +
                                '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deleteUser(' + data.id + ')"></i>';

                        }
                    }
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

        });
    </script>
<script>
        // Assuming jQuery is included
        function openUpdateModal(userId) {
            // Assuming you have included the SweetAlert library for a loading indicator
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                    // Fetch user details via AJAX
                    $.ajax({
                        url: "{{ url('Admin_setting/get_expense_data') }}",
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
                                $('#updateForm input[name="date"]').val(response.data.date);
                                $('#updateForm input[name="ammount"]').val(response.data.ammount);
                                $('#type').val(response.data.type);
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



        $(document).ready(function() {
            $('#updateForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('admin_setting/update_expense') }}",
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
                                var oAllLinksTable = $('#show_expense').DataTable();
                                oAllLinksTable.ajax.reload();
                            });
                        } else {
                            // Show error message with SweetAlert
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

        function expensedelete(userId) {
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
                        url: "{{ url('admin_setting/delete_expense') }}",
                        type: "POST",
                        data: {
                            userId: userId
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                // Remove the deleted row from DataTable
                                var rowId = '#tr_' + userId;
                                var table = $('#show_expense').DataTable();
                                table.row(rowId).remove().draw();

                                Swal.fire(
                                    'Deleted!',
                                    'Record has been deleted.',
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
<script>
        // Assuming jQuery is included
        function openUpdateModal1(userId) {
            // Assuming you have included the SweetAlert library for a loading indicator
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                onBeforeOpen: () => {
                    Swal.showLoading();
                    // Fetch user details via AJAX
                    $.ajax({
                        url: "{{ url('Admin_setting/get_misc_data') }}",
                        type: 'GET',
                        data: {
                            userId: userId
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                // Populate form fields with retrieved data
                                $('#id1').val(response.data.id);
                                $('#datepicker1').val(response.data.date);
                                $('#Amount').val(response.data.amount);
                                $('#detail').val(response.data.detail);
                                // Show the modal
                                $('#updateModal1').modal('show');
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



        $(document).ready(function() {
            $('#updateForm1').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ url('admin_setting/update_misc') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Close the update modal
                            $('#updateModal1').modal('hide');

                            // Show success message with SweetAlert
                            Swal.fire({
                                title: 'Success',
                                text: 'User updated successfully!',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                // Reload the DataTable upon success
                                var oAllLinksTable = $('#show_expense1').DataTable();
                                oAllLinksTable.ajax.reload();
                            });
                        } else {
                            // Show error message with SweetAlert
                            $('#updateModal1').modal('hide');
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
                        $('#updateModal1').modal('hide');
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
                        url: "{{ url('admin_setting/delete_misc') }}",
                        type: "POST",
                        data: {
                            userId: userId
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                // Remove the deleted row from DataTable
                                var rowId = '#tr_' + userId;
                                var table = $('#show_expense1').DataTable();
                                table.row(rowId).remove().draw();

                                Swal.fire(
                                    'Deleted!',
                                    'Record has been deleted.',
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
