@extends('layouts.admin')

@section('breadcrumbs')
    <span>Settings</span>
    <span data-crumb-sep>/</span>
    <span data-crumb-current>Manage</span>
@endsection

@section('content')
<x-page-header title="Set Restriction"
               subtitle="Control what each role is allowed to reach." />


            <div class="ui-card" data-collapsed="0">

                            <!--Start A_category -->
                       
                                <div class="ui-card-body">

                                    <form role="form" id="material_add"
                                        action="{{ url('admin_setting/save_setting') }}">

                                        <x-field label="Civil Setting">
    <select class="ui-select" id="civil" name="civil">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
</x-field>
                                          <x-field label="Finish Setting">
    <select class="ui-select" id="finish" name="finish">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
</x-field>


                                        <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-secondary">Set</button>
</div>
                                    </form>

                                </div>

{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
</div>
@endsection

@push('scripts')
<script>

            $('#civil').val({{ $setting->civil_status }})
            $('#finish').val({{ $setting->finish_status }})


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

           
        </script>
@endpush
