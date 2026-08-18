@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/add_user') }}">Registration</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Show user</span>
@endsection

@section('content')
<x-page-header title="Users"
               subtitle="Every account with access to the system." />


            <div class="ui-card" data-collapsed="0">


                        <div class="ui-card-body">
                            <table width="100%" id="show_user" class="ui-table">
                                <thead>

                                    <tr>

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

        

    <!-- update_modal.php -->

    <!-- update_modal.php -->

    <div class="modal" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="updateModalLabel">Update User</h2>
                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                </div>
                <div class="modal-body">
                    <!-- Update form goes here -->
                    <form id="updateForm">

                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Name</label>


                            <input type="text" class="ui-input" name="name" id="field-1" placeholder="Name" required>
</div>

                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Username</label>

                            <input type="text" class="ui-input" name="username" id="field-1" placeholder="Username" required>

                            <input type="hidden" class="ui-input" name="id" id="field-1" placeholder="Username" required>
</div>

                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Email</label>


                            <input type="email" class="ui-input" name="email" id="sector" placeholder="Email" required>
</div>
                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Password</label>


                            <input type="text" class="ui-input" name="password" id="change_password" placeholder="Password" >
</div>

                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Address</label>


                            <input type="text" class="ui-input" name="address" id="sector" placeholder="Address" required>
</div>
                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Contact</label>


                            <input type="number" class="ui-input" name="contact" id="sector" placeholder="Contact" required>
</div>

                        <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Select</label>


                            <select class="ui-select" name="role">
                                <option> Select Material</option>


                                <option> Worker</option>
                                <option> Client</option>

                            </select>
</div>


                        <!-- Add other fields as needed -->

                        <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
{{-- stray </body> from the CodeIgniter view removed --}}
@endsection

@push('scripts')
{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
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
                            '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                            '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteUser(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>'


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
