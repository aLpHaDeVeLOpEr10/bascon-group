@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/show_con_site') }}">Project Management</a>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Site details</span>
@endsection

@section('content')
<x-page-header :title="$name"
               subtitle="Construction project detail, instalments and payments." />


            <div class="ui-card" style="padding-bottom: 30px;">
                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation"><a href="#installments" aria-controls="home" role="tab" data-toggle="tab">Installment</a></li>
                                <li role="presentation"><a href="#payments" id="payments_recieved" aria-controls="profile" role="tab" data-toggle="tab">Payment</a></li>

                            </ul>
                        </div>
                        <div class="tab-content">

                            <div role="tabpanel" class="tab-pane" id="installments">

                                <!-- Nav tabs -->
                                <ul class="ui-tabs" role="tablist">
                                    <li role="presentation"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Add Installment</a></li>
                                    <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Instalment Recieved</a></li>

                                </ul>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="payments">

                                <!-- Nav tabs -->
                                <ul class="ui-tabs" role="tablist">
                                    <li role="presentation"><a href="#paymentssdf_category" aria-controls="home" role="tab" data-toggle="tab">Add payment</a></li>
                                    <li role="presentation"><a href="#B_payment_category" id="payments_category" aria-controls="profile" role="tab" data-toggle="tab">Payment Recieved</a></li>

                                </ul>
                            </div>
                        </div>
                        <!-- Tab panes -->
                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <form role="form" id="brick_addition_form" action="{{ url('admin_setting/Add_cons_instal') }}">

                                    <x-field label="Instalment">
    <input type="number" class="ui-input" name="price" id="field-1" placeholder="Add Instalment" required>
</x-field>

                                    <x-field label="Source">
    <input type="text" class="ui-input" name="source" id="field-1" placeholder="Add Source" required>
</x-field>

                                    <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date" id="datepicker" placeholder="Select a date" required>
</x-field>
                                    <div class="ui-row">

                                        
                                        <div class="min-w-0 flex-1">
                                            <input type="hidden" class="ui-input" value="{{ $const_id }}" name="proj_id" id="sector" required>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-primary submit-form">Add</button>
</div>
                                </form>

                            </div>
                            <!-- End A_category -->

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_category">

                                <div class="mt-4">


                                    <table id="b_categoer_table" width="100%" class="ui-table">

                                        <thead>
                                            <tr>
                                                <!-- Add your table headers here -->
                                                <th>Sr No</th>
                                                <th>Price</th>
                                                <th>source</th>
                                                <th>Date</th>

                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="ui-total my-5" id="total_price_managments">
                                        <h4>Total Fee:@money($total_fee)</h4><h4>Total Instalments:@money($total_instalments)</h4>
                                    </div>
                                </div>

                            </div>


                            <div role="tabpanel" class="tab-pane" id="paymentssdf_category">
                                <form role="form" id="brick_addition_form1" action="{{ url('admin_setting/Add_cons_payment') }}">

                                    <x-field label="Payment">
    <input type="number" class="ui-input" name="price1" id="field-1" placeholder="Add Payment" required>
</x-field>

                                    <x-field label="Source">
    <input type="text" class="ui-input" name="source1" id="field-1" placeholder="Add Source" required>
</x-field>

                                    <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date1" id="datepicker1" placeholder="Select a date" required>
</x-field>
                                    <div class="ui-row">

                                        
                                        <div class="min-w-0 flex-1">
                                            <input type="hidden" class="ui-input" value="{{ $const_id }}" name="proj_id1" id="sector1" required>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-primary submit-form">Add</button>
</div>
                                </form>

                            </div>
                            <!-- End A_category -->

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_payment_category">

                                <div class="mt-4">


                                    <table id="b_categoer_table1" width="100%" class="ui-table">

                                        <thead>
                                            <tr>
                                                <!-- Add your table headers here -->
                                                <th>Sr No</th>
                                                <th>Price</th>
                                                <th>source</th>
                                                <th>Date</th>
                                                <th>Action</th>


                                            </tr>
                                        </thead>
                                    </table>
                                    <div class="ui-total my-5" id="total_price_managments">
                                        <h4>Total Payments:@money($total_payments)</h4>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <!-- End B_category -->

                    </div>

        

        <div class="modal" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="updateModalLabel">Update Payment</h2>
                        <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
                    </div>
                    <div class="modal-body">
                        <!-- Update form goes here -->
                        <form id="updateForm">

                            <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Price</label>


                                <input type="text" class="ui-input" name="payment" id="field-1" placeholder="Price" required>
</div>

                            <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Source</label>

                                <input type="text" class="ui-input" name="source" id="field-1" placeholder="Source" required>

                                <input type="hidden" class="ui-input" name="id" id="field-1" placeholder="Username" required>
</div>

                            <div class="mb-4 space-y-1.5">
    <label for="field-1" class="ui-label">Date</label>


                                <input type="text" class="ui-input" name="date" id="datepicker2" placeholder="Date" required>
</div>

                            <!-- Add other fields as needed -->

                            <button type="submit" class="ui-btn ui-btn-primary">Update</button>
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
    $(function() {
        $("#datepicker2").datepicker();
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
                        $('input[name="source"]').val('');
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
        $('#brick_addition_form1').submit(function(event) {
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
                        $('input[name="price1"]').val('');
                        $('input[name="selected_date1"]').val('');
                        $('input[name="source1"]').val('');
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
        $('#b_categoer_table').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/show_con_detail/' . $const_id) }}",
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
                }
            ],
            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
            buttons: [{
                    extend: 'print',
                    text: 'Print DataTable',
                    className: 'dt-button',
                    customize: function(win) {
                        // Add custom content to the print view
                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                        $(win.document.body).find('table').prepend('<tfoot><tr><td colspan="3">Total Fee: @money($total_fee)   &nbsp&nbsp&nbsp&nbspTotal Instalments: @money($total_instalments)  </td></tr></tfoot>');
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
            }
        });
    });
</script>
<script>
    $('#payments_category').click(function() {
        if ($.fn.DataTable.isDataTable('#b_categoer_table1')) {
            $('#b_categoer_table1').DataTable().destroy();
        }
        $('#b_categoer_table1').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/show_con_detail1/' . $const_id) }}",
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
                        return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                            '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteUser(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                    }
                }
            ],
            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
            buttons: [{
                    extend: 'print',
                    text: 'Print DataTable',
                    className: 'dt-button',
                    customize: function(win) {
                        // Add custom content to the print view
                        $(win.document.body).prepend('<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;"><img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>');
                        $(win.document.body).find('table').prepend('<tfoot><tr><td colspan="3">Total Payments: @money($total_payments)  </td></tr></tfoot>');
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
            }
        });
    });


    $(document).ready(function() {
        $('#updateForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('admin_setting/update_payment') }}",
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
                            var oAllLinksTable = $('#b_categoer_table1').DataTable();
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

    function openUpdateModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('Admin_setting/get_payment_edit') }}",
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
                    url: "{{ url('admin_setting/delete_payment') }}",
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Remove the deleted row from DataTable
                            var rowId = '#tr_' + userId;
                            var table = $('#b_categoer_table1').DataTable();
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
