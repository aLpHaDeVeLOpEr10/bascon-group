@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Show site</strong>
                </li>
            </ol>

            <h2>Show site</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#add_personal_architect" aria-controls="home" role="tab" data-toggle="tab">Personal Project</a></li>
                            <li role="presentation"><a href="#add_company_architect" aria-controls="profile" role="tab" data-toggle="tab">Company Project</a></li>

                        </ul>

                        <div class="tab-content">

                            <div role="tabpanel" class="tab-pane active" id="add_personal_architect">

                                <div class="panel-body">
                                    <table id="show_site" width="100%" style="white-space: nowrap;" class="table ">
                                        <thead>

                                            <tr style="background-color: aliceblue;">
                                                </th>
                                                <th>Sr No</th>
                                                <th>Project Name</th>
                                                <th>Phase</th>
                                                <th>Sector</th>
                                                <th>Price</th>
                                                <th>Action</th>


                                            </tr>
                                        </thead>
                                    </table>

                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane " id="add_company_architect">
                                <div class="panel-body">
                                    <table id="show_site_company" width="100%" style="white-space: nowrap;" class="table ">
                                        <thead>

                                            <tr style="background-color: aliceblue;">
                                                </th>
                                                <th>Sr No</th>
                                                <th>Project Name</th>
                                                <th>Phase</th>
                                                <th>Sector</th>
                                                <th>Price</th>
                                                <th>Action</th>


                                            </tr>
                                        </thead>
                                    </table>

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
                        <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Update form goes here -->
                        <form id="updateForm">

                            <div class="form-group">
                                <label for="field-1">Phase</label>
                                <input type="text" class="form-control" name="phase" id="field-1" placeholder="Phase" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Plot No</label>
                                <input type="text" class="form-control" name="name" id="field-1" placeholder="Plot No" required>
                                <input type="hidden" class="form-control" name="id" id="field-1" placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Price</label>

                                <input type="text" class="form-control" name="total_price" id="total_price" placeholder="Price" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Sector</label>
                                <input type="text" class="form-control" name="sector" id="sector" placeholder="Sector" required>
                            </div>



                            <!-- Add other fields as needed -->

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        
        <div class="modal fade" id="updateModal1" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Update form goes here -->
                        <form id="updateForm1">

                            <div class="form-group">
                                <label for="field-1">Phase</label>
                                <input type="text" class="form-control" name="phase" id="field-1" placeholder="Phase" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Plot No</label>
                                <input type="text" class="form-control" name="project_name" id="field-1" placeholder="Plot No" required>
                                <input type="hidden" class="form-control" name="id" id="field-1" placeholder="Username" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Price</label>

                                <input type="text" class="form-control" name="architect_fees" id="total_price" placeholder="Price" required>
                            </div>
                            <div class="form-group">
                                <label for="field-1">Sector</label>
                                <input type="text" class="form-control" name="sector" id="sector" placeholder="Sector" required>
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
            $(document).ready(function() {
                var oAllLinksTable = $('#show_site').DataTable({
                    "ajax": {
                        "url": "{{ url('admin_setting/get_site') }}",
                        "type": "GET",
                        "dataType": "json",
                        "dataSrc": "data"
                    },
                    "columns": [{
                            "data": "id"
                        },
                        {
                            "data": "name",
                            "render": function(data, type, row) {
                                // Assuming 'row.id' contains the unique identifier for the row
                                return '<a href="{{ url('admin_setting/show_details') }}/' + row.id + '">' + data + '</a>';
                            }
                        },
                        {
                            "data": "phase"
                        },
                        {
                            "data": "sector"
                        },
                        {
                            "data": "total_price"
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
                            url: "{{ url('Admin_setting/get_site_details') }}",
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
                                    $('#updateForm input[name="phase"]').val(response.data.phase);
                                    $('#updateForm input[name="name"]').val(response.data.name);
                                    $('#updateForm input[name="total_price"]').val(response.data.total_price);
                                    $('#updateForm input[name="sector"]').val(response.data.sector);



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
                        url: "{{ url('admin_setting/update_site') }}",
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
                                    var oAllLinksTable = $('#show_site').DataTable();
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
                            url: "{{ url('admin_setting/delete_user23') }}",
                            type: "POST",
                            data: {
                                userId: userId
                            },
                            dataType: "json",
                            success: function(response) {
                                if (response.success) {
                                    // Remove the deleted row from DataTable
                                    var rowId = '#tr_' + userId;
                                    var table = $('#show_site').DataTable();
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
<script>
            $(document).ready(function() {
                var oAllLinksTable = $('#show_site_company').DataTable({
                    "ajax": {
                        "url": "{{ url('admin_setting/get_site_comapny') }}",
                        "type": "GET",
                        "dataType": "json",
                        "dataSrc": "data"
                    },
                    "columns": [{
                            "data": "id"
                        },
                        {
                            "data": "project_name",
                            "render": function(data, type, row) {
                                // Assuming 'row.id' contains the unique identifier for the row
                                return '<a href="{{ url('admin_setting/show_details_company') }}/' + row.id + '">' + data + '</a>';
                            }
                        },
                        {
                            "data": "phase"
                        },
                        {
                            "data": "sector"
                        },
                        {
                            "data": "architect_fees"
                        },
                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal1(' + data.id + ')"></i>';

                            }
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
            function openUpdateModal1(userId) {
                // Assuming you have included the SweetAlert library for a loading indicator
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                        // Fetch user details via AJAX
                        $.ajax({
                            url: "{{ url('Admin_setting/get_site_details_company') }}",
                            type: 'GET',
                            data: {
                                userId: userId
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    // Populate form fields with retrieved data
                                    $('#updateForm1 input[name="id"]').val(response.data.id);
                                    $('#updateForm1 input[name="phase"]').val(response.data.phase);
                                    $('#updateForm1 input[name="project_name"]').val(response.data.project_name);
                                    $('#updateForm1 input[name="architect_fees"]').val(response.data.architect_fees);
                                    $('#updateForm1 input[name="sector"]').val(response.data.sector);



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
                        url: "{{ url('admin_setting/update_site_company') }}",
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
                                    var oAllLinksTable = $('#show_site_company').DataTable();
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

           
        </script>
@endpush
