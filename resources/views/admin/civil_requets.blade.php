@extends('layouts.admin')

@section('breadcrumbs')
    <span>Request</span>
    <span data-crumb-sep>|</span>
    <span data-crumb-current>Civil Request</span>
@endsection

@section('content')
<x-page-header title="Civil Requests"
               subtitle="Approve or reject civil material requests raised from sites." />


            <div class="ui-card" data-collapsed="0">


                        <div class="ui-card-body">
                            <table id="show_user" width="100%" style="white-space: nowrap;" class="ui-table">
                                <thead>

                                <tr>
                                        <th>Sr No</th>
                                        <th>Name</th>
                                        <th>Project</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>price</th>
                                        <th>date</th>
                                        <th>Action</th>

                                    </tr>
                                </thead>
                            </table>

                        </div>

                    </div>

<!-- update_modal.php -->

<!-- update_modal.php -->

        
@endsection

@push('scripts')
{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}
<script>
    $(document).ready(function () {
        var oAllLinksTable = $('#show_user').DataTable({
            "ajax": {
                "url": "{{ url('admin_setting/get_civil') }}",
                "type": "GET",
                "dataType": "json",
                "dataSrc": "data"
            },
            "columns": [
                { "data": "id" },
                { "data": "login_user" },
                { "data": "proj_name" },
                { "data": "type" },
                { "data": "quantity" },
                { "data": "price" },
                { "data": "date" },
                {
                // New column for delete and update buttons
                "data": null,

                
                "render": function (data, type, row) {
                    // 'data' parameter contains the row data
                    return '<button class="ui-btn ui-btn-primary" onclick="accept_request(' + data.id + ')">Accept</button>'+"  "+
                    '<button class="ui-btn ui-btn-danger" onclick="reject_request(' + data.id + ')">Reject</button>' ;
                           
                }
            }
                
            ],
            "createdRow": function (row, data, dataIndex) {
                // Set the ID for each row
                $(row).attr("id", 'tr_' + data.id);
                
                // Set the content for the first cell (Sr No)
                $('td:eq(0)', row).html(dataIndex + 1);
            }
        });
    });



    function reject_request(userId) {
    // Use SweetAlert for confirmation
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to reject this request!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Reject it!',
        width: '600px', 
        
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request
            $.ajax({
                url: "{{ url('admin_setting/reject_civil') }}",
                type: "POST",
                data: { userId: userId },
                dataType: "json",
                success: function (response) {
                    if (response.success) {


                        Swal.fire(
                            'Rejected!',
                            'Request has been rejected.',
                            'success'
                        );
                        var oAllLinksTable = $('#show_user').DataTable();
                        oAllLinksTable.ajax.reload();
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to reject accept. Please try again.',
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

function accept_request(userId) {
    // Use SweetAlert for confirmation
    Swal.fire({
        title: 'Are you sure?',
        text: 'You want to Accept this request!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, accept it!',
        width: '600px', 
        
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request
            $.ajax({
                url: "{{ url('admin_setting/accept_civil') }}",
                type: "POST",
                data: { userId: userId },
                dataType: "json",
                success: function (response) {
                    if (response.success) {


                        Swal.fire(
                            'Accepted!',
                            'Request has been accepted.',
                            'success'
                        );
                        var oAllLinksTable = $('#show_user').DataTable();
                        oAllLinksTable.ajax.reload();
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to accept request. Please try again.',
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
@endpush
