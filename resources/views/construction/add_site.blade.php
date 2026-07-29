@extends('layouts.app')

@section('content')
<ol class="breadcrumb bc-3" >
                                <li>
                        <a href="index.html"><i class="fa-home"></i>Home</a>
                    </li>
                            
                        <li class="active">
        
                                    <strong>Add site</strong>
                            </li>
                            </ol>
                    
        <h2>Add site</h2>
        <br />
        
        
        <div class="row">
            <div class="col-md-12">
                
                <div class="panel panel-primary" data-collapsed="0">
    
                    
                    <div class="panel-body">
                  
                        <form role="form" class="form-horizontal" id="site_add" action="{{ url('construction/save_site') }}">
            
                            <div class="form-group">
                                <label for="field-1" class="col-sm-3 control-label">Phase</label>
                                
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="phase"id="field-1" placeholder="Add Phase" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="field-1" class="col-sm-3 control-label"> Plot No</label>
                                
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="project_name" id="field-1" placeholder="Add Name" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="field-1" class="col-sm-3 control-label">Sector</label>
                                
                                <div class="col-sm-5">
                                    <input type="text" class="form-control" name="sector" id="sector" placeholder="Add Sector" required>
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
@endsection

@push('scripts')
<script>

    // Include jQuery library if not already included
    $(document).ready(function () {
    $('#site_add').submit(function (event) {
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
                    $('input[name="phase"]').val('');
                    $('input[name="project_name"]').val('');
                    $('input[name="sector"]').val('');

                    // Additional success handling if needed
                } else {
                    swal.fire.fire({
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
