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

                    <strong>Set Restiction</strong>
                </li>
            </ol>

            <h2>Set Restiction</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">

                     

                            <!--Start A_category -->
                       
                                <div class="panel-body">

                                    <form role="form" class="form-horizontal" id="material_add"
                                        action="{{ url('admin_setting/save_setting') }}">

                                        <div class="form-group">
                                                <label class="col-sm-3 control-label">Civil Setting</label>
                                        <div class="col-sm-5">
                                                    <select class="form-control" id="civil" name="civil">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
                                                </div>
                                        </div>
                                          <div class="form-group">
                                                <label class="col-sm-3 control-label">Finish Setting</label>
                                                <div class="col-sm-5">
                                                    <select class="form-control" id="finish" name="finish">
                                                    <option> Select Material</option>
                                                       
                                                        
                                                        <option value="1">Approve By admin</option>
                                                        <option value="0">Instant</option>
                                           
                                                    </select>
                                                </div>
                                          </div>


                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-5">
                                                <button type="submit" class="btn btn-default">Set</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>



        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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
