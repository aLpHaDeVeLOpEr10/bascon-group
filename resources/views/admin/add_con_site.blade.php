@extends('layouts.admin')

@section('title', 'Add Construction Site · BASCON GROUP')

@section('breadcrumbs')
    <a href="{{ url('admin_setting/show_con_site') }}">Project Management</a>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Add site</span>
@endsection

@section('content')
<x-page-header title="Add Construction Site"
               subtitle="Register a plot and set the management and architect fees for it." />

<x-card title="Site details">
    {{-- Field names, ids and the action URL are unchanged — the submit handler
         below still serialises this form and POSTs it exactly as before. --}}
    <form role="form" id="site_con_add" action="{{ url('admin_setting/save_con_site') }}">

        <x-field label="Phase">
    <input type="text" class="ui-input" name="phase" id="field-phase"
                       placeholder="e.g. Phase 5" required>
</x-field>

        <x-field label="Plot No">
    <input type="text" class="ui-input" name="project_name" id="field-plot"
                       placeholder="e.g. 412-A" required>
</x-field>

        <x-field label="Sector">
    <input type="text" class="ui-input" name="sector" id="sector"
                       placeholder="e.g. G-13" required>
</x-field>

        <x-field label="Management fee">
    <input type="text" class="ui-input" name="price" id="price"
                       placeholder="Add fee" required>
</x-field>

        <x-field label="Architect fee">
    <input type="text" class="ui-input" name="architect_fee" id="architect_fee"
                       placeholder="Add fee">
                <span class="ui-hint">Optional.</span>
</x-field>

        <div class="ui-form-actions">
            <button type="submit" class="ui-btn ui-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14" /><path d="M5 12h14" />
                </svg>
                Add site
            </button>
            <a href="{{ url('admin_setting/show_con_site') }}" class="ui-btn ui-btn-secondary">Cancel</a>
        </div>
    </form>
</x-card>
@endsection

@push('scripts')
<script>

    // Include jQuery library if not already included
    $(document).ready(function () {
    $('#site_con_add').submit(function (event) {
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
