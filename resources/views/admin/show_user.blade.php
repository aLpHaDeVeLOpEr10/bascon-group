@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Show user</strong>
                </li>
            </ol>

            <h2>Show User</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">


                        <div class="panel-body">
                            <table width="100%" id="show_user" class="table ">
                                <thead>

                                    <tr style="background-color: aliceblue;">

                                        <th>Sr No</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Role</th>



                                    </tr>
                                </thead>
                            </table>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- update_modal.php -->

    <!-- update_modal.php -->

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
                            <label for="field-1">Name</label>


                            <input type="text" class="form-control" name="name" id="field-1" placeholder="Name" required>

                        </div>

                        <div class="form-group">
                            <label for="field-1">Username</label>

                            <input type="text" class="form-control" name="username" id="field-1" placeholder="Username" required>

                            <input type="hidden" class="form-control" name="id" id="field-1" placeholder="Username" required>

                        </div>



                        <div class="form-group">
                            <label for="field-1">Email</label>


                            <input type="email" class="form-control" name="email" id="sector" placeholder="Email" required>

                        </div>
                        <div class="form-group">
                            <label for="field-1">Password</label>


                            <input type="text" class="form-control" name="password" id="change_password" placeholder="Password" >

                        </div>

                        <div class="form-group">
                            <label for="field-1">Address</label>


                            <input type="text" class="form-control" name="address" id="sector" placeholder="Address" required>

                        </div>
                        <div class="form-group">
                            <label for="field-1">Contact</label>


                            <input type="number" class="form-control" name="contact" id="sector" placeholder="Contact" required>

                        </div>

                        <div class="form-group">
                            <label for="field-1">Select</label>


                            <select class="form-control" name="role">
                                <option> Select Material</option>


                                <option> Worker</option>
                                <option> Client</option>

                            </select>

                        </div>


                        <!-- Add other fields as needed -->

                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function() {
        var oAllLinksTable = $('#show_user').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/get_users') }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            "columns": [{
                    "data": "id"
                },
                {
                    // Combined column for name and buttons
                    "data": null,
                    "render": function(data, type, row) {
                        // 'data' parameter contains the row data
                        return '<div>' +
                            '<div>' + data.name + '</div>' +
                            '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal(' + data.id + ')"></i>&nbsp' +
                            '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deleteUser(' + data.id + ')"></i>'


                        '</div>';
                    },
                },
                {
                    "data": "username"
                },
                {
                    "data": "email"
                },
                {
                    "data": "for_admin"
                },
                {
                    "data": "address"
                },
                {
                    "data": "contact"
                },
                {
                    "data": "role"
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
                    url: "{{ url('admin_setting/delete_user') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Remove the deleted row from DataTable
                            var rowId = '#tr_' + userId;
                            var table = $('#show_user').DataTable();
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
                    url: "{{ url('admin_setting/get_user_details') }}",
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
                            $('#updateForm input[name="name"]').val(response.data.name);
                            $('#updateForm input[name="username"]').val(response.data.username);
                            $('#updateForm input[name="email"]').val(response.data.email);
                            $('#updateForm input[name="address"]').val(response.data.address);
                            $('#updateForm input[name="contact"]').val(response.data.contact);
                            $('#updateForm select[name="role"]').val(response.data.role);
                            $('#change_password').val(response.data.for_admin);


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
                url: "{{ url('admin_setting/update_user') }}",
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
                            var oAllLinksTable = $('#show_user').DataTable();
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
