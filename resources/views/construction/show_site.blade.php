@extends('layouts.app')

@section('title', 'Sites · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Sites</span>
@endsection

@section('content')
<x-page-header title="Sites"
               subtitle="Open a site to record its materials, labour and payments.">
    <x-slot:actions>
        <a href="{{ url('construction/add_site') }}" class="ui-btn ui-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14" /><path d="M5 12h14" />
            </svg>
            Add site
        </a>
    </x-slot:actions>
</x-page-header>

<x-card flush>
    {{-- Table id and column order are unchanged; the DataTable below binds to
         them. A stray </th> in the original header row is dropped. --}}
    <table id="show_site" width="100%" style="white-space: nowrap;" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Plot No.</th>
                <th>Sector</th>
                <th>Phase</th>
                <th class="w-px whitespace-nowrap text-right">Action</th>
            </tr>
        </thead>
    </table>
</x-card>

<div class="modal" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="updateModalLabel">Update site</h2>
                <button type="button" class="modal-close" data-dismiss="modal" aria-label="Close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg></button>
            </div>

            <div class="modal-body">
                {{-- Field names and the form id are unchanged; the prefill and
                     submit handlers below rely on them. --}}
                <form id="updateForm">

                    <div class="mb-4 space-y-1.5">
    <label for="update-phase" class="ui-label">Phase</label>
                        <input type="text" class="ui-input" name="phase" id="update-phase"
                               placeholder="Phase" required>
</div>

                    <div class="mb-4 space-y-1.5">
    <label for="update-plot" class="ui-label">Plot No</label>
                        <input type="text" class="ui-input" name="project_name" id="update-plot"
                               placeholder="Plot No" required>

                        <input type="hidden" name="id">
</div>

                    <div class="mb-4 space-y-1.5">
    <label for="sector" class="ui-label">Sector</label>
                        <input type="text" class="ui-input" name="sector" id="sector"
                               placeholder="Sector" required>
</div>

                    <div class="ui-form-actions is-end">
                        <button type="button" class="ui-btn ui-btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="ui-btn ui-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
<script>
    $(document).ready(function () {
        var oAllLinksTable = $('#show_site').DataTable({
            "ajax": {
                "url": "{{ url('construction/get_site') }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            "columns": [
                { "data": "id" },
                { 
                    "data": "project_name",
                    "render": function (data, type, row) {
                        // Assuming 'row.id' contains the unique identifier for the row
                        return '<a href="{{ url('construction/show_details') }}/' + row.id + '">' + data + '</a>';
                    }
                },
                { "data": "sector" },
                { "data": "phase" },
                {
                // New column for delete and update buttons
                "data": null,
                "render": function (data, type, row) {
                    // 'data' parameter contains the row data
                    return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                            '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteUser(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';
         
                }
            }
                
            ],
            "createdRow": function (row, data, dataIndex) {
                // Set the ID for each row
                $(row).attr("id", 'tr_' + data.id);

                // Clicking anywhere in the row opens the site, except on the
                // action buttons and the plot-number link, which ui.js skips.
                $(row).attr("data-row-href", "{{ url('construction/show_details') }}/" + data.id);

                // Set the content for the first cell (Sr No)
                $('td:eq(0)', row).html(dataIndex + 1);
            }
        });
    });


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
                url: "{{ url('construction/delete_user') }}",
                type: "POST",
                data: { userId: userId },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        // Remove the deleted row from DataTable
                        var rowId = '#tr_' + userId;
                        var table = $('#show_site').DataTable();
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
                error: function () {
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
<script>
    // Assuming jQuery is included
    function openUpdateModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: "{{ url('construction/get_site_details') }}",
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
                            $('#updateForm input[name="phase"]').val(response.data.phase);
                            $('#updateForm input[name="project_name"]').val(response.data.project_name);
                            $('#updateForm input[name="sector"]').val(response.data.sector);

  

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



    $(document).ready(function() {
        $('#updateForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: "{{ url('construction/update_site') }}",
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
                            var oAllLinksTable = $('#show_site').DataTable();
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
</script>
@endpush
