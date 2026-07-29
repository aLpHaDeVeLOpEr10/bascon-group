@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Add Expensive</strong>
                </li>
            </ol>

            <h2>Add Expensive</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">
                        <div>

                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Add Expense</a></li>
                                <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Add Miscellaneous Credit</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <form role="form" class="form-horizontal" id="add_expense" action="{{ url('admin_setting/save_expense') }}">

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">Select </label>

                                        <div class="col-sm-5">
                                            <select class="form-control" name="type">
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
                                        <label for="field-1" class="col-sm-3 control-label">Detail</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="detail" id="field-1" placeholder="Add Detail" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">Amount</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="amount" id="field-1" placeholder="Add Amount" required>
                                        </div>
                                    </div>
                                   

                                    <div class="form-group">
                                        <label for="field-2" class="col-sm-3 control-label">Date</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="selected_date" id="datepicker" placeholder="Select a date" required>
                                        </div>
                                    </div>






                                    <div class="form-group">
                                        <div class="col-sm-offset-3 col-sm-5">
                                            <button type="submit" class="btn btn-default">Add</button>
                                        </div>
                                    </div>
                                </form>

                            </div>

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_category">
                                <form role="form" class="form-horizontal" id="add_expense1" action="{{ url('admin_setting/save_misc_Admin') }}">

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">Detail </label>

                                        <div class="col-sm-5">
                                        <input type="text" class="form-control" name="detail" id="field-1" placeholder="Add Detail" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-1" class="col-sm-3 control-label">Amount</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="Amount" id="field-1" placeholder="Add Amount" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="field-2" class="col-sm-3 control-label">Date</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" name="Selected_date" id="datepicker1" placeholder="Select a date" required>
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
            $(function() {
                $("#datepicker1").datepicker();
            });
        </script>
<script>
            // Include jQuery library if not already included
            $(document).ready(function() {
                $('#add_expense').submit(function(event) {
                    event.preventDefault();

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
                                $('input[name="type"]').val('');
                                $('input[name="amount"]').val('');
                                $('input[name="detail"]').val('');
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





                $('#add_expense1').submit(function(event) {
                    event.preventDefault();

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
                                $('input[name="detail"]').val('');
                                $('input[name="Amount"]').val('');
                                $('input[name="Selected_date"]').val('');

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
@endpush
