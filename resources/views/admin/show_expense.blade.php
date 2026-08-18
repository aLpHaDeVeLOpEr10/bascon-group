@extends('layouts.admin')

@section('breadcrumbs')
    <span>Money Management</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Show Expense</span>
@endsection

@section('content')
<x-page-header title="Expenses"
               subtitle="Recorded expenses and miscellaneous costs." />


            <div class="ui-card" data-collapsed="0" style="padding: 0px 20px;">
                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Show Expense</a></li>
                                <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Show Miscellaneous Credit</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="A_category">
                                <table id="show_expense" width="100%" style="white-space: nowrap;" class="ui-table">
                                    <thead>

                                        <tr>
                                            <th>Sr No</th>
                                            <th>Date</th>
                                            <th>Ammount</th>
                                            <th>Detail</th>
                                            <th>Type</th>
                                            <th>Action</th>

                                        </tr>
                                    </thead>
                                </table>
                                {{-- Five figures, so five pills. They used to be one line separated by runs of &nbsp;. --}}
                                <div class="ui-total mt-6 mb-4">
                                    <h4>Total Amount: @money($total_price)</h4>
                                    <h4>Total Expense: @money($expense_total)</h4>
                                    <h4>Remainig Total: @money($total_remaining)</h4>
                                    <h4>Amount recieved by Hussnian: @money($hussnain_total)</h4>
                                    <h4>Amount recieved by Basharat: @money($basharat_total)</h4>
                                </div>

                            </div>
                            <div role="tabpanel" class="tab-pane" id="B_category">
                                <table id="show_expense1" width="100%" style="white-space: nowrap;" class="ui-table">
                                    <thead>

                                        <tr>
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

                

            
        




    

    <div class="modal" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="updateModalLabel">Update Expense</h2>
                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                </div>
                <div class="modal-body">
                    <!-- Update form goes here -->
                    <form id="updateForm">

                        <div class="ui-row">
                            <label for="field-1" class="ui-label" >Select </label>

                            <div class="min-w-0 flex-1">
                                <select class="ui-select" id="type" name="type">
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

                        <x-field label="Amount">
    <input type="text" class="ui-input" name="ammount" id="field-1" placeholder="Add Amount" required>
                                <input type="hidden" class="ui-input" name="id" id="field-1" required>
</x-field>



                        <x-field label="Date">
    <input type="text" class="ui-input" name="date" id="datepicker" placeholder="Select a date" required>
</x-field>


                        <!-- Add other fields as needed -->
                        <div class="ui-row">
                            <button type="submit" class="ui-btn ui-btn-primary" style="margin-left:15px;margin-top:10px;">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>






    <div class="modal" id="updateModal1" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="updateModalLabel">Update Miscellaneous</h2>
                    <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                </div>
                <div class="modal-body">
                    <!-- Update form goes here -->
                    <form id="updateForm1">


                        <x-field label="Detail">
    <input type="text" class="ui-input" name="detail" id="detail" placeholder="Add Detail" required>
</x-field>

                        <x-field label="Amount">
    <input type="text" class="ui-input" name="Amount" id="Amount" placeholder="Add Amount" required>
                                <input type="hidden" class="ui-input" name="id1" id="id1" required>
</x-field>



                        <x-field label="Date">
    <input type="text" class="ui-input" name="Selected_date" id="datepicker1" placeholder="Select a date" required>
</x-field>


                        <!-- Add other fields as needed -->
                        <div class="mb-4 space-y-1.5">
    <button type="submit" class="ui-btn ui-btn-primary" style="margin-left:15px;margin-top:10px;">Update</button>
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
            $("#datepicker").datepicker({ dateFormat: 'dd/mm/yy' });
        });

        $(function() {
            $("#datepicker1").datepicker({ dateFormat: 'dd/mm/yy' });
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
                            return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="expensedelete(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                        }
                    }
                ],
                dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                buttons: [{
                        extend: 'print',
                        text: 'Print Record',
                        className: 'dt-button',
                        customize: function(win) {
                            // Add custom content to the print view
                            $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}"  style="width:500px;" /></div>');

                            // Add total price to the print view

                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'dt-button',
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
                            return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal1(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteUser(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                        }
                    }
                ],
                dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                buttons: [{
                        extend: 'print',
                        text: 'Print Record',
                        className: 'dt-button',
                        customize: function(win) {
                            // Add custom content to the print view
                            $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}"  style="width:500px;" /></div>');

                            // Add total price to the print view

                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'dt-button',
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
