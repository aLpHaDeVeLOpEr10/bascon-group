@extends('layouts.admin')

@section('title', 'Add User · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/show_user') }}">Registration</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Add user</span>
@endsection

@section('content')
<x-page-header title="Add User"
               subtitle="Create a worker or client account and assign it to a project.">
    <x-slot:actions>
        <a href="{{ url('admin_setting/show_user') }}" class="ui-btn ui-btn-secondary">View all users</a>
    </x-slot:actions>
</x-page-header>

<x-card title="Account details">
    {{-- Field names, the form id and the action URL are unchanged — the submit
         handler below still serialises this form and POSTs it exactly as
         before. Duplicated `id="field-1"` / `id="sector"` attributes from the
         legacy markup are replaced with unique ids so each <label for> works;
         no script referenced them. --}}
    <form role="form" id="user_add" action="{{ url('admin_setting/save_user') }}">

        <x-field label="Name">
    <input type="text" class="ui-input" name="name" id="field-name"
                       placeholder="Full name" required>
</x-field>

        <x-field label="Username">
    <input type="text" class="ui-input" name="username" id="field-username"
                       placeholder="Username" required>
</x-field>

        <x-field label="Email">
    <input type="email" class="ui-input" name="email" id="field-email"
                       placeholder="name@example.com" required>
                <span class="ui-hint">Used to sign in.</span>
</x-field>

        {{-- The visibility toggle is the shared [data-toggle-password] control
             from ui.js. The input name and id are unchanged. --}}
        <x-field label="Password" for="password-input">
            <div class="relative">
                <input type="password" class="ui-input pr-11" name="password" id="password-input"
                       placeholder="Password" required>

                <button type="button"
                        class="ui-field-btn"
                        data-toggle-password="#password-input"
                        aria-label="Show password"
                        aria-pressed="false">
                    <svg data-icon-show viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg data-icon-hide class="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10.7 5.1A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a18.4 18.4 0 0 1-2.7 3.7" />
                        <path d="M6.6 6.6A18.5 18.5 0 0 0 2 12s3.5 7 10 7a10.7 10.7 0 0 0 5.4-1.4" />
                        <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" /><path d="m2 2 20 20" />
                    </svg>
                </button>
            </div>
        </x-field>

        <x-field label="Address">
    <input type="text" class="ui-input" name="address" id="field-address"
                       placeholder="Address" required>
</x-field>

        <x-field label="Contact">
    <input type="number" class="ui-input" name="contact" id="field-contact"
                       placeholder="Phone number" required>
</x-field>

        <x-field label="Role">
    <select class="ui-select" name="user_role" id="field-role">
                    <option> Select Role</option>
                    <option> Worker</option>
                    <option> Client</option>
                </select>
</x-field>

        {{-- $sites is supplied by AdminSettingController::addUser;
             the legacy view ran its own query here. --}}
        <x-field label="Project">
    <select class="ui-select" name="proj_id" id="field-project">
                    <option value=""> Select Project</option>

                    @foreach ($sites as $row)
                        <option value="{{ $row->id }}">
                            {{ $row->project_name . '-' . $row->phase . '-' . $row->sector }}
                        </option>
                    @endforeach
                </select>
</x-field>

        <div class="ui-form-actions">
            <button type="submit" class="ui-btn ui-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14" /><path d="M5 12h14" />
                </svg>
                Add user
            </button>
            <a href="{{ url('admin_setting/show_user') }}" class="ui-btn ui-btn-secondary">Cancel</a>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
{{-- The old inline password-toggle script is gone; #toggle-password no longer
     exists and the shared [data-toggle-password] handler covers it. --}}
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
