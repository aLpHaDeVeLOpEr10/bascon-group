@extends('layouts.admin')

@section('breadcrumbs')
    <span>Category</span>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Civil Materials</span>
@endsection

@section('content')
<x-page-header title="Civil Materials"
               subtitle="Manage the civil material catalogue and its categories." />


            <div class="ui-card" data-collapsed="0">


                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab"
                                        data-toggle="tab">Add Category</a></li>
                                <li role="presentation"><a href="#B_category" aria-controls="profile" role="tab"
                                        data-toggle="tab">Show Category</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <div class="ui-card-body">

                                    <form role="form" id="material_add"
                                        action="{{ url('admin_setting/save_Amaterial') }}">

                                        <x-field label="Category Name">
    <input type="text" class="ui-input" name="name" id="field-1"
                                                    placeholder="Name" required>
</x-field>

                                        <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-secondary">Add</button>
</div>
                                    </form>

                                </div>
                            </div>

                            <div role="tabpanel" style="padding:30px;"  class="tab-pane" id="B_category">
                            <table id="show_user" width="100%" class="ui-table">
                                <thead>

                                <tr>
                                        <th>Sr No</th>
                                        <th>Name</th>
                                        <th class="w-24">Action</th>
    
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>

                    </div>

        

{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
@endsection

@push('scripts')
<script>

            // Include jQuery library if not already included
            $(document).ready(function () {
                $('#material_add').submit(function (event) {
                    event.preventDefault();

                    // Your form data
                    var formData = $(this).serialize();

                    // Ajax request
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                swal({
                                    title: 'Success',
                                    text: 'Data added successfully',
                                    icon: 'success',
                                    button: 'Ok',
                                });
                                $('input[name="name"]').val('');

                                // Additional success handling if needed
                            } else {
                                swal({
                                    title: 'Error',
                                    text: 'Failed to add data',
                                    icon: 'error',
                                    button: 'Ok',
                                });
                                // Additional error handling if needed
                            }
                        },
                        error: function () {
                            swal({
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


            $(document).ready(function () {
                var oAllLinksTable = $('#show_user').DataTable({
                    "ajax": {
                        "url": "{{ url('admin_setting/get_Bgategory') }}",
                        "type": "GET",
                        "dataType": "json",
                        "dataSrc": "data"
                    },
                    "columns": [
                        { "data": "id" },
                        { "data": "material_name" },
                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function (data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button class="ui-btn ui-btn-danger" onclick="deleteUser(' + data.id + ')">Delete</button>';
                            }
                        }


                    ],
                    "createdRow": function (row, data, dataIndex) {
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
                            url: "{{ url('admin_setting/delete_Bcategory') }}",
                            type: "POST",
                            data: { userId: userId },
                            dataType: "json",
                            success: function (response) {
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
                            error: function () {
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
