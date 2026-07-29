@extends('layouts.admin')

@section('content')
<ol class="breadcrumb bc-3">
                <li>
                    <a href="index.html"><i class="fa-home"></i>Home</a>
                </li>

                <li class="active">

                    <strong>Add user</strong>
                </li>
            </ol>

            <h2>Add User</h2>
            <br />


            <div class="row">
                <div class="col-md-12">

                    <div class="panel panel-primary" data-collapsed="0">


                        <div class="panel-body">

                            <form role="form" class="form-horizontal" id="user_add" action="{{ url('admin_setting/save_user') }}">

                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Name</label>

                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="name" id="field-1" placeholder="Name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Username</label>

                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="username" id="field-1" placeholder="Username" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Email</label>

                                    <div class="col-sm-5">
                                        <input type="email" class="form-control" name="email" id="sector" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Password</label> 
                                 


                                    <div class="col-sm-5" >
                                    <div class="col-sm-10"style="padding:0px" >
                                        <input type="password" class="form-control" name="password" id="password-input" placeholder="Password"  required>
                                    </div>
                                    <div class="col-sm-2" style="padding:0px">
                                    <button style="width:59px;    background: transparent" type="button" id="toggle-password" class="btn btn-default"><i class="fa fa-eye"></i></button>

                                    </div>
                                    </div>
                                  
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Address</label>

                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="address" id="sector" placeholder="Address" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Contact</label>

                                    <div class="col-sm-5">
                                        <input type="number" class="form-control" name="contact" id="sector" placeholder="Contact" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Role</label>

                                    <div class="col-sm-5">
                                        <select class="form-control" name="user_role">
                                            <option> Select Role</option>


                                            <option> Worker</option>
                                            <option> Client</option>

                                        </select>
                                    </div>
                                </div>

                                {{-- $sites is supplied by AdminSettingController::addUser;
                                     the legacy view ran its own query here. --}}
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-3 control-label">Project</label>
                                    <div class="col-sm-5">
                                        <select class="form-control" name="proj_id">
                                            <option value=""> Select Project</option>


                                            @foreach ($sites as $row)
                                                <option value="{{ $row->id }}">
                                                    {{ $row->project_name . '-' . $row->phase . '-' . $row->sector }}
                                                </option>
                                            @endforeach

                                        </select>
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
            document.getElementById('toggle-password').addEventListener('click', function() {
                var passwordInput = document.getElementById('password-input');
                var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            });
        </script>
<script>
            // Include jQuery library if not already included
            $(document).ready(function() {
                $('#user_add').submit(function(event) {
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
                                $('input[name="name"]').val('');
                                $('input[name="username"]').val('');
                                $('input[name="email"]').val('');
                                $('input[name="password"]').val('');
                                $('input[name="address"]').val('');
                                $('input[name="contact"]').val('');
                                $('input[name="role"]').val('');


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
