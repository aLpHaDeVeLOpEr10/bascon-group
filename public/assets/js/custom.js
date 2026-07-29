$(document).ready(function() {
    // Handle form submission
    $('#brick_addition_form').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

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
                    $('input[name="brick_quantity"]').val('');
                    $('input[name="brick_price"]').val('');
                    $('#catgory').val('');
                    $('#datepicker1').val('');

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






$('#get_category').click(function() {
    var Category = $('#category').val();

    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#show_site')) {
        $('#show_site').DataTable().destroy();
    }

    $.ajax({
        url: "<?php echo base_url('construction/show_bricks/' . $const_id . '/'); ?>" + Category,
        type: "GET",
        dataType: "json",
        success: function(response) {
            var total_price = response.total_price;
            var quantity = response.quantity;


            var sites_data = response.data;

            // Add a new column with serial numbers starting from 1
            sites_data.forEach(function(record, index) {
                record.serial_number = index + 1;
            });
            $('#total_price').html('<h4 style="display: inline; margin-left:60px;">Total Amount: ' + total_price + '</h4>');

            var oAllLinksTable = $('#show_site').DataTable({
                "data": sites_data,
                "columns": [{
                        "data": "serial_number"
                    }, {
                        "data": "date"
                    },
                    {
                        // Combined column for name and buttons
                        "data": null,
                        "render": function(data, type, row) {
                            // 'data' parameter contains the row data
                            return '<div>' +
                                '<div>' + data.type + '</div>' +
                                '<i title="Edit" class="fas fa-edit btn btn-primary" onclick="openUpdateModal(' + data.id + ')"></i>&nbsp' +
                                '<i title="Delete" class="fas fa-trash-alt btn btn-danger" onclick="deleteCivil(' + data.id + ')"></i></div>';
                        },
                    },
                    {
                        "data": "quantity"
                    },
                    {
                        "data": "price"
                    },

                ],

                dom: 'lBfrtip',
                buttons: [{
                        extend: 'print',
                        text: 'Print Record',
                        className: 'btn btn-secondary',
                        customize: function(win) {
                            // Add watermark
                            $(win.document.body).prepend(
                                '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                '<img src="<?php echo base_url; ?>assets/images/water_mak.jpeg" style="width:500px;" />' +
                                '</div>'
                            );

                            // Remove any existing total row to prevent duplication
                            $(win.document.body).find('.total-row').remove();

                            // Insert "Civil Material" heading in the table header (centered with colspan="5")
                            const headingRow = '<tr style="font-size: 24px; font-weight: bold; text-align: center;">' +
                                '<th colspan="5" style="text-align: center;">Civil Material</th>' +
                                '</tr>';

                            // Prepend the heading row to the table's thead
                            $(win.document.body).find('table thead').prepend(headingRow); // Insert "Civil Material" heading in the table 

                            // Append total row to the end of the table body
                            const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                '<td colspan="3"></td>' +
                                '<td>Total Quantity: ' + quantity + '</td>' +
                                '<td>Total Amount: ' + total_price + '</td>' +
                                '</tr>';

                            // Append the total row after the table body
                            $(win.document.body).find('table tbody').append(totalRow);


                        }


                    },
                    {
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btn btn-primary',
                        filename: 'data_export'
                    }
                ]
            });
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error: " +error);
        }
    });
});
