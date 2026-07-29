@extends('layouts.admin')

@push('styles')
<style>
    #show_user td {
        border: 1px solid #A9A9A9;
        /* Change 'black' to your desired border color */
        font-family: 'Your Modern Font', sans-serif;
        /* Replace 'Your Modern Font' with your desired modern font */
        font-size: 16px;
        /* Adjust the font size as needed */
    }

    #show_user th {
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

                    <strong>Add Finishing</strong>
                </li>
            </ol>

            <h2>Add Finishing</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">

                        <div>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab"
                                        data-toggle="tab">Add Category</a></li>
                                <li role="presentation"><a href="#B_category" aria-controls="profile" role="tab"
                                        data-toggle="tab">Show Category</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <div class="panel-body">

                                    <form role="form" class="form-horizontal" id="material_add"
                                        action="{{ url('admin_setting/save_Bmaterial') }}">

                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-3 control-label">Category Name</label>

                                            <div class="col-sm-5">
                                                <input type="text" class="form-control" name="name" id="field-1"
                                                    placeholder="Name" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-5">
                                                <button type="submit" class="btn btn-default">Add</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>

                            </div>

                            <div role="tabpanel" style="padding:30px;" class="tab-pane" id="B_category">

                                <table id="show_user" width="100%" class="table table-bordered">
                                    <thead>

                                    <tr style="background-color: aliceblue;">
                                            </th>
                                            <th>Sr No</th>
                                            <th>Name</th>
                                            <th class="action">Action</th>

                                        </tr>
                                    </thead>
                                </table>


                            </div>
                        </div>
                    </div>

                </div>
            </div>




        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
                                swal.fire({
                                    title: 'Success',
                                    text: 'Data added successfully',
                                    icon: 'success',
                                    button: 'Ok',
                                });
                                $('input[name="name"]').val('');

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
                        error: function () {
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

            $(document).ready(function () {
                var oAllLinksTable = $('#show_user').DataTable({
                    "ajax": {
                        "url": "{{ url('admin_setting/get_Agategory') }}",
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
                                return '<button class="btn btn-danger" onclick="deleteUser(' + data.id + ')">Delete</button>';
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
                            url: "{{ url('admin_setting/delete_category') }}",
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
