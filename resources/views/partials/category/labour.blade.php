{{--
    Material / labour catalogue page body — shared verbatim by the admin and
    the construction (worker) side.

    The two differ in exactly two things: the shell they sit in, and the
    controller prefix their AJAX talks to. The prefix arrives as $prefix
    ('admin_setting' or 'construction'); everything else is one copy, so the
    worker's page cannot drift from the admin's.

    The endpoint names below keep the legacy swaps and typos — save_Amaterial
    writes a CIVIL category, get_Agategory returns FINISHING rows — because
    both route groups register them under those names. See routes/web.php.
--}}

<x-page-header title="Labour"
               subtitle="Manage the labour categories available to sites." />


            <div class="ui-card" data-collapsed="0">

                        <div>

                            <!-- Nav tabs -->
                            <ul class="ui-tabs" role="tablist">
                                <li role="presentation"><a href="#A_category" aria-controls="home" role="tab"
                                        data-toggle="tab">Add Category</a></li>
                               

                            </ul>
                        </div>

                        <div class="tab-content">


                            <!--Start A_category -->
                            <div role="tabpanel" class="tab-pane" id="A_category">
                                <div class="ui-card-body">

                                    <form role="form" id="material_add"
                                        action="{{ url($prefix.'/save_labour') }}">

                                        {{-- A picker rather than free text.
                                             Labour types are a small, settled
                                             set, and typing them by hand is how
                                             "Tile fixing", "Tile-Fixer" and
                                             "Tile-Fixing" became three of them.

                                             + and the bin edit the catalogue
                                             itself (labour_category), so a name
                                             added here survives a refresh and
                                             one removed here is gone. The form
                                             below is a separate act: it assigns
                                             a type from this list to a site. --}}
                                        <x-field label="Category Name">
    <div class="flex items-center gap-3">
                                                <select class="ui-select min-w-0 flex-1" name="name"
                                                        id="labour_name" required>
                                                    <option value="">Select labour type</option>
                                                    @foreach ($labourTypes as $type)
                                                        <option value="{{ $type }}">{{ $type }}</option>
                                                    @endforeach
                                                </select>

                                                <button type="button" class="ui-icon-btn ui-tip shrink-0"
                                                        id="labour_name_new" data-tip="Add a new type"
                                                        aria-label="Add a new labour type"
                                                        aria-expanded="false" aria-controls="labour_name_row">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M12 5v14" /><path d="M5 12h14" />
                                                    </svg>
                                                </button>

                                                <button type="button" class="ui-icon-btn ui-tip shrink-0"
                                                        id="labour_name_delete" data-tip="Remove this type"
                                                        aria-label="Remove the selected labour type">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" />
                                                        <path d="M19 6l-1 14H6L5 6" />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Shown by the + above. Hidden with an inline style rather
                                                 than a class so nothing in the utility layer can win. --}}
                                            <div class="mt-2.5 flex items-center gap-3" id="labour_name_row"
                                                 style="display: none;">
                                                <input type="text" class="ui-input min-w-0 flex-1"
                                                       id="labour_name_input" placeholder="New labour type"
                                                       autocomplete="off">
                                                <button type="button" class="ui-btn ui-btn-secondary shrink-0"
                                                        id="labour_name_save">Add</button>
                                                <button type="button" class="ui-btn ui-btn-ghost shrink-0"
                                                        id="labour_name_cancel">Cancel</button>
                                            </div>
</x-field>

                                        <x-field label="Project Done">
    <input type="Number" class="ui-input" name="price" id="field-1"
                                                    placeholder="Price" required>
</x-field>
                                        <x-field label="Select List">
    <select class="ui-select" id="labour_value" name="project_id">
                                                    <option> Select site</option>
                                                    @foreach ($sites as $row)

                                                        <option value=' {{ $row->id }}'> {{ $row->project_name.'-'.$row->sector.'-'.$row->phase }}</option>
                                                    @endforeach
                                                </select>
</x-field>


                                        <div class="flex flex-wrap items-center gap-2.5 pt-5">
    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg">Add</button>
</div>
                                    </form>

                                </div>

                            </div>

                            
                        </div>
                    </div>

        
{{-- SweetAlert2 is loaded once in partials/scripts; this duplicate tag is removed. --}}

@push('scripts')
<script>

            // Include jQuery library if not already included
            /*
             * The + and the bin beside the type picker.
             *
             * Both edit the catalogue on the server and rebuild the <select>
             * from what comes back, rather than patching the list locally: an
             * option that only exists in the browser disappears on the next
             * refresh, which is exactly how this read as broken before.
             */
            $(document).ready(function () {
                var picker = $('#labour_name');
                var row = $('#labour_name_row');
                var input = $('#labour_name_input');
                var toggle = $('#labour_name_new');
                var placeholder = picker.find('option').first().text();

                function closeRow() {
                    row.hide();
                    input.val('');
                    toggle.attr('aria-expanded', 'false');
                }

                // The server sends the whole list back after every change, so
                // the picker is always showing what is actually stored.
                function repopulate(types, selected) {
                    picker.empty().append($('<option>').val('').text(placeholder));

                    $.each(types, function (_, type) {
                        picker.append($('<option>').val(type).text(type));
                    });

                    picker.val(selected || '');
                }

                toggle.on('click', function () {
                    var opening = row.is(':hidden');

                    row.toggle(opening);
                    toggle.attr('aria-expanded', opening ? 'true' : 'false');

                    if (opening) {
                        input.trigger('focus');
                    } else {
                        input.val('');
                    }
                });

                $('#labour_name_cancel').on('click', closeRow);

                $('#labour_name_save').on('click', function () {
                    var name = $.trim(input.val());
                    var button = $(this);

                    if (!name || button.prop('disabled')) {
                        input.trigger('focus');
                        return;
                    }

                    button.prop('disabled', true);

                    $.ajax({
                        url: "{{ url($prefix.'/save_labour_category') }}",
                        type: 'POST',
                        data: { name: name },
                        dataType: 'json',
                        complete: function () {
                            button.prop('disabled', false);
                        },
                        success: function (response) {
                            if (!response.success) {
                                swal.fire({ title: 'Error', text: 'Failed to add the type.', icon: 'error' });
                                return;
                            }

                            repopulate(response.types, response.name);
                            closeRow();

                            // `existed` rather than a blanket "added": the
                            // endpoint matches case-insensitively, so asking
                            // for "painter" selects the "Painter" already
                            // there instead of making a near-duplicate.
                            if (response.existed) {
                                swal.fire({
                                    title: 'Already listed',
                                    text: '"' + response.name + '" is already a labour type.',
                                    icon: 'info',
                                });
                            }
                        },
                        error: function () {
                            swal.fire({ title: 'Error', text: 'Error in Ajax request', icon: 'error' });
                        }
                    });
                });

                $('#labour_name_delete').on('click', function () {
                    var name = picker.val();
                    var button = $(this);

                    if (!name) {
                        swal.fire({
                            title: 'Nothing selected',
                            text: 'Choose the labour type you want to remove.',
                            icon: 'info',
                        });
                        return;
                    }

                    swal.fire({
                        title: 'Remove "' + name + '"?',
                        text: 'It will no longer be offered when assigning labour to a site.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, remove it',
                    }).then(function (result) {
                        if (!result.isConfirmed || button.prop('disabled')) return;

                        button.prop('disabled', true);

                        $.ajax({
                            url: "{{ url($prefix.'/delete_labour_category') }}",
                            type: 'POST',
                            data: { name: name },
                            dataType: 'json',
                            complete: function () {
                                button.prop('disabled', false);
                            },
                            success: function (response) {
                                if (!response.success) {
                                    // Still in use somewhere; the message names
                                    // how many sites, which is what the person
                                    // needs in order to decide what to do.
                                    swal.fire({
                                        title: 'In use',
                                        text: response.message,
                                        icon: 'warning',
                                    });
                                    return;
                                }

                                repopulate(response.types, '');
                                swal.fire({ title: 'Removed', text: '"' + name + '" is no longer listed.', icon: 'success' });
                            },
                            error: function () {
                                swal.fire({ title: 'Error', text: 'Error in Ajax request', icon: 'error' });
                            }
                        });
                    });
                });

                // Enter in the new-type box means Add, not submit the form.
                input.on('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        $('#labour_name_save').trigger('click');
                    }
                });
            });

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
                                $('#labour_name').val('');

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

            $(document).ready(function () {
                var oAllLinksTable = $('#show_user').DataTable({
                    "ajax": {
                        "url": "{{ url($prefix.'/get_labourcat') }}",
                        "type": "GET",
                        "dataType": "json",
                        "dataSrc": "data"
                    },
                    "columns": [
                        { "data": "id" },
                        { "data": "type" },
                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function (data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button class="ui-btn ui-btn-danger" onclick="deleteUser(' + data.id + ')">Delete</button>';
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
                            url: "{{ url($prefix.'/delete_category') }}",
                            type: "POST",
                            data: { userId: userId },
                            dataType: "json",
                            success: function (response) {
                                if (response.success) {
                                    // Remove the deleted row from DataTable
                                    var rowId = '#tr_' + userId;
                                    var table = $('#show_user').DataTable();
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
@endpush
