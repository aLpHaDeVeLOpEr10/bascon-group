@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/show_expense') }}">Money Management</a>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Add Expense</span>
@endsection

@section('content')
<x-page-header title="Add Expense"
               subtitle="Record an expense against the business." />


            <div class="ui-card" data-collapsed="0">
                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab" data-toggle="tab">Add Expense</a></li>
                                <li role="presentation"><a href="#B_category" id="payment_recieved" aria-controls="profile" role="tab" data-toggle="tab">Add Miscellaneous Credit</a></li>

                            </ul>
                        </div>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <form role="form" id="add_expense" action="{{ url('admin_setting/save_expense') }}">

                                    <x-field label="Select">
    <select class="ui-select" name="type">
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
</x-field>
                                    <x-field label="Detail">
    <input type="text" class="ui-input" name="detail" id="field-1" placeholder="Add Detail" required>
</x-field>
                                    <x-field label="Amount">
    <input type="text" class="ui-input" name="amount" id="field-1" placeholder="Add Amount" required>
</x-field>
                                   

                                    <x-field label="Date">
    <input type="text" class="ui-input" name="selected_date" id="datepicker" placeholder="Select a date" required>
</x-field>






                                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-secondary">Add</button>
</div>
                                </form>

                            </div>

                            <!-- Start B_category -->
                            <div role="tabpanel" class="tab-pane" id="B_category">
                                <form role="form" id="add_expense1" action="{{ url('admin_setting/save_misc_Admin') }}">

                                    <x-field label="Detail">
    <input type="text" class="ui-input" name="detail" id="field-1" placeholder="Add Detail" required>
</x-field>

                                    <x-field label="Amount">
    <input type="text" class="ui-input" name="Amount" id="field-1" placeholder="Add Amount" required>
</x-field>

                                    <x-field label="Date">
    <input type="text" class="ui-input" name="Selected_date" id="datepicker1" placeholder="Select a date" required>
</x-field>






                                    <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-secondary">Add</button>
</div>
                                </form>
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
