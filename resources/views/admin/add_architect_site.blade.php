@extends('layouts.admin')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/show_site') }}">Architecture</a>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Add site</span>
@endsection

@section('content')
<x-page-header title="Add Architecture Site"
               subtitle="Register a personal architecture project." />


            <div class="ui-card" data-collapsed="0">
                        <ul class="ui-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#add_personal_architect" aria-controls="home" role="tab" data-toggle="tab">Personal Project</a></li>

                        </ul>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane active" id="add_personal_architect">
                                <div class="ui-card-body">

                                    <form role="form" id="site_add" action="{{ url('admin_setting/save_site') }}">

                                        <x-field label="Phase">
    <input type="text" class="ui-input" name="phase" id="field-1" placeholder="Add Phase" required>
</x-field>

                                        <x-field label="PLot No">
    <input type="text" class="ui-input" name="project_name" id="field-1" placeholder="Add Name" required>
</x-field>

                                        <x-field label="Sector">
    <input type="text" class="ui-input" name="sector" id="sector" placeholder="Add Sector" required>
</x-field>

                                        <x-field label="Total Fee">
    <input type="number" class="ui-input" name="price" id="price" placeholder="Add Fee" required>
</x-field>





                                        <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-secondary">Add</button>
</div>
                                    </form>

                                </div>
                            </div>

                         
                        </div>
                    </div>

                
            




        
@endsection

@push('scripts')
<script>
            // Include jQuery library if not already included
            $(document).ready(function() {
                $('#site_add').submit(function(event) {
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
                                $('input[name="phase"]').val('');
                                $('input[name="project_name"]').val('');
                                $('input[name="sector"]').val('');
                                $('input[name="price"]').val('');

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


                $('#site_add_company').submit(function(event) {
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
                                $('input[name="phase1"]').val('');
                                $('input[name="project_name1"]').val('');
                                $('input[name="sector1"]').val('');
                                $('input[name="price1"]').val('');

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
