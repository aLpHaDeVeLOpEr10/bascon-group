/**
 * Site details — the construction module's busiest screen.
 *
 * Lifted out of resources/views/construction/site_settings.blade.php, which had
 * grown to 3,350 lines with 2,400 of them JavaScript. Nothing here changed in
 * the move; the only difference is that every server value now arrives through
 * one config object instead of being interpolated inline, so this file is plain
 * JavaScript that an editor can lint and a bundler can minify.
 *
 * The view emits that object as window.SITE_SETTINGS before loading this:
 *
 *     siteId     the project this page is for
 *     siteName   its display name, used in print headings
 *     base       application root, for url() below
 *     watermark  the image the print views stamp behind the table
 *     totals     pre-formatted money strings for the print footers
 *     options    the category lists the Add Entry form chooses between
 *
 * It still relies on the globals the page already loads as classic scripts —
 * jQuery, DataTables, jQuery UI and the Swal shim in ui.js. This runs as a
 * deferred module, so all of them are defined by the time it executes.
 */

const S = window.SITE_SETTINGS || {};

/** Absolute URL for an application path, the way Blade's url() built them. */
S.url = function (path) {
    return String(S.base || '').replace(/\/$/, '') + '/' + String(path).replace(/^\//, '');
};

if (!window.SITE_SETTINGS) {
    // Loaded on a page that did not emit the config: do nothing rather than
    // throw on the first selector.
    console.warn('site-settings.js loaded without SITE_SETTINGS');
} else {

    /*
     * The unified Add Entry form.
     *
     * Each ledger keeps its own endpoint and its own field names — those are
     * the legacy CodeIgniter contracts and the rollups depend on them — so
     * this maps one set of neutral controls onto whichever one is chosen.
     * `null` means the ledger has no such field, which is also what hides the
     * row.
     */
    var ENTRY_KINDS = {
        civil: {
            url: S.url('construction/Add_brick'),
            item: 'catgory', detail: null, quantity: 'brick_quantity',
            amount: 'brick_price', date: 'selected_date1',
            itemLabel: 'Material', amountLabel: 'Price',
            options: S.options.civil
        },
        finishing: {
            url: S.url('construction/Add_b_category'),
            item: 'catgory', detail: 'Detail', quantity: 'brick_quantity',
            amount: 'brick_price', date: 'selected_date2',
            itemLabel: 'Material', amountLabel: 'Price',
            options: S.options.finishing
        },
        labour: {
            url: S.url('construction/labour_instalment'),
            item: 'labour_type', detail: 'Detail', quantity: null,
            amount: 'bill_labour', date: 'selected_date',
            itemLabel: 'Labour type', amountLabel: 'Instalment',
            options: S.options.labour
        },
        misc: {
            url: S.url('construction/misc_add'),
            item: null, detail: 'Detail_misc', quantity: null,
            amount: 'ammoun_misc', date: 'selected_date3',
            itemLabel: null, amountLabel: 'Amount',
            options: []
        }
    };

    $(document).ready(function () {
        var form = $('#entry_form');

        function kind() {
            return ENTRY_KINDS[$('#entry_kind').val()];
        }

        function row(name) {
            return $('[data-entry-row="' + name + '"]');
        }

        // Show only the fields the chosen ledger actually stores, and relabel
        // the ones whose meaning shifts (a material vs a labour type).
        function applyKind() {
            var k = kind();

            row('item').toggle(Boolean(k.item));
            row('detail').toggle(Boolean(k.detail));
            row('quantity').toggle(Boolean(k.quantity));

            if (k.item) {
                var select = $('#entry_item').empty();

                select.append($('<option>').val('').text('Select ' + k.itemLabel.toLowerCase()));
                k.options.forEach(function (name) {
                    select.append($('<option>').val(name).text(name));
                });

                // The field component renders its label as a sibling of the
                // control's box. (Naming the tag literally here would make Blade
                // compile it as a component, even inside a comment.)
                row('item').find('label').first().text(k.itemLabel);
            }

            form.find('label').filter(function () {
                return ['Price', 'Instalment', 'Amount'].indexOf($(this).text().trim()) !== -1;
            }).first().text(k.amountLabel);
        }

        function message(tone, text) {
            var icons = {
                success: '<path d="M20 6 9 17l-5-5"/>',
                info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
                danger: '<path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>'
            };

            $('#entry_message')
                .attr('class', 'ui-alert ui-alert-' + tone + ' mb-5')
                .html('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" ' +
                      'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icons[tone] + '</svg>' +
                      '<span></span>')
                .find('span').text(text).end()
                .prop('hidden', false);
        }

        $('#entry_kind').on('change', applyKind);
        applyKind();

        form.submit(function (event) {
            event.preventDefault();

            var k = kind();
            var $submit = form.find('button[type="submit"]');
            if ($submit.prop('disabled')) return;

            var item = $.trim($('#entry_item').val() || '');

            if (k.item && !item) {
                message('danger', 'Choose a ' + k.itemLabel.toLowerCase() + ' first.');
                return;
            }

            // Built by hand rather than serialize(): the inputs carry neutral
            // ids and the endpoint expects its own names.
            var payload = { proj_id: form.find('[name="proj_id"]').val() };

            if (k.item) payload[k.item] = item;
            if (k.detail) payload[k.detail] = $('#entry_detail').val();
            if (k.quantity) payload[k.quantity] = $('#entry_quantity').val();

            payload[k.amount] = $('#entry_amount').val();
            payload[k.date] = $('#entry_date').val();

            $submit.prop('disabled', true).addClass('is-loading');

            $.ajax({
                url: k.url,
                type: 'POST',
                data: payload,
                dataType: 'json',
                complete: function () {
                    $submit.prop('disabled', false).removeClass('is-loading');
                },
                success: function (response) {
                    if (!response.success) {
                        message('danger', 'The entry could not be saved. Please try again.');
                        return;
                    }

                    message('success', 'Entry added to ' + $('#entry_kind option:selected').text() + '.');

                    // Keep only the category: entries are usually added in
                    // runs of the same kind. Everything else clears, so a stale
                    // value can never be carried into the next entry unnoticed.
                    $('#entry_item').val('');
                    $('#entry_detail').val('');
                    $('#entry_quantity').val('');
                    $('#entry_date').val('');
                    $('#entry_amount').val('').focus();
                },
                error: function (xhr) {
                    var text = 'The entry could not be saved. Please try again.';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        text = Object.values(xhr.responseJSON.errors)[0][0];
                    }

                    message('danger', text);
                }
            });
        });
    });


    /** Sits at the head of every printed sheet, above the table. */
    var PRINT_MASTHEAD = 'BASCON GROUP';

    /**
     * Heading for a print button.
     *
     * Three of the tables on this page show one material at a time, chosen
     * from a <select> above them, so the table name alone does not say what
     * was printed — every category prints as "Civil Materials". `selectId`
     * names the dropdown to fold into the heading.
     *
     * DataTables evaluates a `title` function at export time rather than at
     * init, which is what makes this work: the heading follows whatever is
     * selected when the button is pressed.
     *
     * Those <option>s carry no value attribute, so val() hands back the label
     * text — including the leading space the option markup opens with, hence
     * the trim. The first option is a placeholder rather than a material, and
     * is left out of the heading entirely.
     */
    function printHeading(tableName, selectId, placeholder) {
        var site = S.siteName;

        if (!selectId) return tableName + ' — ' + site;

        var picked = ($('#' + selectId).val() || '').trim();
        var suffix = picked && picked !== placeholder ? ' (' + picked + ')' : '';

        return tableName + suffix + ' — ' + site;
    }

    /**
     * Puts that heading in the printed table's own first row instead of in an
     * <h1> above it, so the sheet reads as one block and the heading travels
     * with the table if the rows spill onto a second page.
     *
     * Every print button used to build this row by hand with a hardcoded
     * colspan — "5" on one table, "6" on nine others, several with a stray
     * empty <th> tacked on to cover the miscount. The count is taken from the
     * header row itself here, before the heading is prepended to it, so it
     * cannot drift when a column is added or moved.
     *
     * The heading carries a site name from the database, so it goes in as
     * text rather than markup.
     */
    function printHeadingRow(win, heading) {
        var body = $(win.document.body);

        // DataTables writes its `title` into the print window twice: once as
        // the document <title> and again as an <h1> above the table. Only the
        // first is wanted — the browser draws the document title along the top
        // of every printed page, so the <h1> was a second BASCON GROUP sitting
        // directly on the table's own heading.
        //
        // Dropped here rather than by blanking `title`, which would take the
        // document <title> with it and leave the top of the page empty.
        body.find('h1').remove();

        var thead = body.find('table thead');
        if (!thead.length) return;

        var columns = thead.find('tr').first().children().length || 1;

        thead.prepend(
            '<tr class="print-heading">' +
            '<th colspan="' + columns + '" ' +
            'style="font-size:14px;font-weight:700;text-align:center;padding:8px 6px;' +
            'text-transform:none;letter-spacing:normal;color:#18181b;">' +
            $('<div/>').text(heading).html() +
            '</th></tr>'
        );
    }



    $(function() {
        $("#datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicker1").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicker2").datepicker({
            dateFormat: 'dd/mm/yy'
        });

        $("#datepicke3").datepicker({
            dateFormat: 'dd/mm/yy'
        });
        $("#datepicke6").datepicker({
            dateFormat: 'dd/mm/yy'
        });
    });


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


    $('.get_category').on('change', function() {
        var Category = $('#category').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#show_site')) {
            $('#show_site').DataTable().destroy();
        }

        $.ajax({
            url: S.url('construction/show_bricks/' + S.siteId) + '/' + Category,
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
                $('#total_price').html('<h4>Total Amount: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#show_site').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        }, {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },
                        {
                            /* Actions belong in a column of their own. They
                               used to be rendered into the Materials cell
                               beneath the material name, which made that
                               column two things at once and left the row with
                               no action column at all. */
                            "data": null,
                            "orderable": false,
                            "className": "whitespace-nowrap text-right",
                            "render": function(data, type, row) {
                                return '<div class="ui-row-actions">' +
                                    '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openUpdateModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deleteCivil(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>' +
                                    '</div>';
                            }
                        },

                    ],

                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Every print button on this page used to head its
                            // output "BASCON GROUP" — DataTables falls back to
                            // the document title when none is given, so eleven
                            // different tables printed under the same heading
                            // and a printout could not be identified once it
                            // left the screen. Each now names its own table,
                            // the material it is filtered to where there is
                            // one, and the site it belongs to.
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {


                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert "Civil Material" heading in the table header (centered with colspan="5")
                                printHeadingRow(win, printHeading('Civil Materials', 'category', 'Select Material'));

                                // Append total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' +
                                    '<td>Total Amount: ' + money(total_price) + '</td>' +
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);

                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );
                            }


                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    $(document).ready(function() {
        // Handle form submission
        $('#Bcategory_form').submit(function(event) {
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
                        $('#b_catecory').val('');
                        $('#datepicker2').val('');

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


    // 'change', not 'click': on a <select>, click fires when the list is merely
    // opened, so every click reloaded the table before a choice had been made.
    $('.get__b_category').on('change', function() {
        var Category = $('#b_category').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#b_categoer_table')) {
            $('#b_categoer_table').DataTable().destroy();
        }

        $.ajax({
            url: S.url('construction/show_b_category/' + S.siteId) + '/' + Category,
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

                $('#total_price_b').html('<h4>Total Amount: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#b_categoer_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        }, {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },
                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openfinishModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletefinish(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Finishing Materials', 'b_category', 'Select Material'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' +
                                    '<td>Total Quantity: ' + quantity + '</td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }

                        },

                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });

    });


    $(document).ready(function() {
        // Handle form submission
        $('#labour_form').submit(function(event) {
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
                        $('#c_category').val('');
                        $('#datepicker').val('');
                        v
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


    $(document).ready(function() {
        // Handle form submission
        $('#misc_form').submit(function(event) {
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
                        $('input[name="Detail_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('#datepicke3').val('');

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

    $(document).ready(function() {
        // Handle form submission
        $('#return_form').submit(function(event) {
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
                        $('input[name="Detail_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('input[name="ammoun_misc"]').val('');
                        $('#datepicke3').val('');

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


    // 'change' for the same reason as .get__b_category above.
    $('.get__labour').on('change', function() {
        var Category = $('#labour_value').val();

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#labour_table')) {
            $('#labour_table').DataTable().destroy();
        }

        $.ajax({
            url: S.url('construction/show_labour/' + S.siteId) + '/' + Category,
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var total_priceaaa = response.project_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#total_price_labour').html('<h4>Projcet Done: ' + money(total_priceaaa) + '</h4><h4>Total payed: ' + money(total_price) + '</h4>');

                var oAllLinksTable = $('#labour_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "description"
                        },
                        {
                            "data": "instalmet"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openmiscrModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletelabour(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Labour Instalments', 'labour_value', 'Select'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });

    });


    var civilAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (civilAccountTable) {
            civilAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: S.url('construction/show_civil_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#civil_total324').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                civilAccountTable = $('#civil_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Civil Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    var miscTable = null; // Declare a variable to store DataTable instance

    $('#total_misc').click(function() {

        if (miscTable) {
            miscTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: S.url('construction/misc_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#misclanious_total').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                miscTable = $('#misc_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openmiscrModal(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="deletemisc(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Miscellaneous'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    var returnTable = null;

    $('#return_tab').click(function() {

        if (returnTable) {
            returnTable.destroy();
        }

        $.ajax({
            url: S.url('construction/return_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#return_total').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                returnTable = $('#return_table').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                        {
                            // New column for delete and update buttons
                            "data": null,
                            "render": function(data, type, row) {
                                // 'data' parameter contains the row data
                                return '<button type="button" title="Edit" aria-label="Edit" class="ui-icon-action" onclick="openreturnrModal22(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4Z"/></svg></button>' +
                                    '<button type="button" title="Delete" aria-label="Delete" class="ui-icon-action is-danger" onclick="return_delete(' + data.id + ')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>';

                            }
                        }
                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Returned Payments'));

                                // Hide the last column (Action column) during printing
                                $(win.document.body).find('table tbody tr td:last-child').css('display', 'none');
                                // :not(.print-heading) — the heading row's single cell is also a
                                // last child, so a bare th:last-child hid the heading along with
                                // the Action column.
                                $(win.document.body).find('table thead tr:not(.print-heading) th:last-child').css('display', 'none');

                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });



    });

    function openreturnrModal22(userId) {

        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: S.url('construction/get_return_details'),
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#return_id').val(response.data.id);
                            $('#returnform input[name="detail"]').val(response.data.detail);
                            $('#returnform input[name="price"]').val(response.data.price);

                            // Show the modal
                            $('#openreturnrModal').modal('show');
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


    var finishAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (finishAccountTable) {
            finishAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: S.url('construction/show_finish_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                $('#finish_total324').html('<h3>Total Amount: ' + money(total_price) + '</h3>');
                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                // Initialize DataTable and store the instance in the variable
                finishAccountTable = $('#finish_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "quantity"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Finishing Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="5"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    var AllAccountTable = null;

    $('#total_pay').click(function() {
        if (AllAccountTable) {
            AllAccountTable.destroy();
        }

        $.ajax({
            url: S.url('construction/total_entries/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var sites_data = response.data;

                // Add serial number
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                AllAccountTable = $('#Total_entries_account').DataTable({
                    data: sites_data,
                    // Source sits right after the date: this table pools Civil,
                    // Finishing, Labour and Miscellaneous rows together, so
                    // which ledger a row came from belongs up front rather than
                    // stranded in the last column.
                    columns: [{
                            data: 'serial_number',
                            title: '#'
                        },
                        {
                            data: 'date',
                            title: 'Date'
                        },
                        {
                            data: 'source',
                            title: 'Source'
                        },
                        {
                            data: 'type',
                            title: 'Type'
                        },
                        {
                            data: 'detail',
                            title: 'Detail'
                        },
                        {
                            data: 'quantity',
                            title: 'Quantity'
                        },
                        {
                            data: 'price',
                            title: 'Price'
                        },
                    ],
                    /* A return is money leaving the ledger again, so its row is
                       tinted like an alert instead of reading as another cost. */
                    createdRow: function(row, data) {
                        if (data.source === 'Return Payment') {
                            $(row).addClass('is-return');
                        }
                    },
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible'
                            },
                            customize: function(win) {
                                printHeadingRow(win, printHeading('All Entries'));

                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'total_entries'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    var miscleAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (miscleAccountTable) {
            miscleAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: S.url('construction/show_misc_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#miscle_account123').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                miscleAccountTable = $('#miscle_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "detail"
                        },
                        {
                            "data": "price"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Miscellaneous Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="3"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


    var labourAccountTable = null; // Declare a variable to store DataTable instance

    $('#total_pay').click(function() {
        // Check if DataTable is already initialized
        if (labourAccountTable) {
            labourAccountTable.destroy(); // Destroy the existing DataTable
        }

        $.ajax({
            url: S.url('construction/show_labour_total/' + S.siteId),
            type: "GET",
            dataType: "json",
            success: function(response) {
                var total_price = response.total_price;
                var sites_data = response.data;

                // Add a new column with serial numbers starting from 1
                sites_data.forEach(function(record, index) {
                    record.serial_number = index + 1;
                });

                $('#Labour_account123').html('<h3>Total Amount: ' + money(total_price) + '</h3>');

                // Initialize DataTable and store the instance in the variable
                labourAccountTable = $('#Labour_account').DataTable({
                    "data": sites_data,
                    "columns": [{
                            "data": "serial_number"
                        },
                        {
                            "data": "date"
                        },
                        {
                            "data": "type"
                        },
                        {
                            "data": "description"
                        },
                        {
                            "data": "instalmet"
                        },

                    ],
                    dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                    buttons: [{
                            extend: 'print',
                            // Masthead only. Which table this is, which material it is
                            // filtered to and which site it belongs to all go in the
                            // table's own first row instead — printHeadingRow, below.
                            title: PRINT_MASTHEAD,
                            text: 'Print Record',
                            className: 'dt-button',
                            exportOptions: {
                                columns: ':visible' // Export only visible columns
                            },
                            customize: function(win) {
                                // Add watermark
                                $(win.document.body).prepend(
                                    '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                    '<img src="' + S.watermark + '" style="width:500px;" />' +
                                    '</div>'
                                );

                                // Remove any existing total row to prevent duplication
                                $(win.document.body).find('.total-row').remove();

                                // Insert the heading row for the table (centered with colspan="6")
                                printHeadingRow(win, printHeading('Labour Total'));
                                // Append the total row to the end of the table body
                                const totalRow = '<tr class="total-row" style="font-weight: bold;">' +
                                    '<td colspan="4"></td>' + // totalQuantity is your variable for the total quantity
                                    '<td>Total Amount: ' + money(total_price) + '</td>' + // totalPrice is your variable for the Total Amount
                                    '</tr>';

                                // Append the total row after the table body
                                $(win.document.body).find('table tbody').append(totalRow);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'dt-button',
                            filename: 'data_export'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + " - " + error);
            }
        });
    });


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
                    url: S.url('construction/get_civil_details'),
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#civil_id').val(response.data.id);
                            $('#updateForm input[name="type"]').val(response.data.type);
                            $('#updateForm input[name="quantity"]').val(response.data.quantity);
                            $('#updateForm input[name="price"]').val(response.data.price);


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
                url: S.url('construction/update_civil'),
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
                            oAllLinksTable.destroy();
                            // Set to false to use the same page
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

            });
        });
    });

    /* deleteCivil and deletelabour were each declared twice in the view, in
       different <script> tags with byte-identical bodies — the second silently
       replaced the first. Separate tags tolerate that; one module does not, so
       the duplicates are gone. Behaviour is unchanged: the surviving copy is
       the same code. */
    function deleteCivil(userId) {
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
                    url: S.url('construction/delete_civil'),
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#show_site').DataTable();
                            var row = table.row('#tr_' + userId);

                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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


    // Assuming jQuery is included
    function openfinishModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: S.url('construction/get_finish_details'),
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#finish_id').val(response.data.id);
                            $('#finishForm input[name="type"]').val(response.data.type);
                            $('#finishForm input[name="quantity"]').val(response.data.quantity);
                            $('#finishForm input[name="price"]').val(response.data.price);
                            $('#finishForm input[name="detail"]').val(response.data.detail);



                            // Show the modal
                            $('#finishModal').modal('show');
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
        $('#finishForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: S.url('construction/update_finish'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#finishModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

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

            });
        });
    });


    function deletefinish(userId) {
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
                    url: S.url('construction/delete_finish'),
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#finishModal').DataTable();
                            var row = table.row('#tr_' + userId);

                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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

    function deletelabour(userId) {
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
                    url: S.url('construction/delete_labour'),
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#labour_table').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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


    function deletemisc(userId) {
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
                    url: S.url('construction/delete_misc'),
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#misc_table').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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

    function return_delete(userId) {
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
                    url: S.url('construction/return_delete'),
                    type: "POST",
                    data: {
                        userId: userId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            // Find the row by userId
                            var table = $('#returnTable').DataTable();
                            var row = table.row('#tr_' + userId);
                            // Remove the row from DataTable
                            row.remove().draw();

                            Swal.fire(
                                'Deleted!',
                                'Deleted Successfully.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'Failed to delete . Please try again.',
                                'error'
                            );
                        }
                    },
                    error: function() {
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


    function openlabourModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: S.url('construction/get_labour_details'),
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#labour_id').val(response.data.id);
                            $('#labourForm input[name="type"]').val(response.data.type);
                            $('#labourForm input[name="description"]').val(response.data.description);
                            $('#labourForm input[name="instalmet"]').val(response.data.instalmet);



                            // Show the modal
                            $('#openlabourModal').modal('show');
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
        $('#labourForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: S.url('construction/update_labour'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openlabourModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

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

            });
        });
    });



    function openmiscrModal(userId) {
        // Assuming you have included the SweetAlert library for a loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
                // Fetch user details via AJAX
                $.ajax({
                    url: S.url('construction/get_misc_details'),
                    type: 'GET',
                    data: {
                        userId: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            // Populate form fields with retrieved data
                            $('#misc_id').val(response.data.id);
                            $('#miscForm input[name="detail"]').val(response.data.detail);
                            $('#miscForm input[name="price"]').val(response.data.price);

                            // Show the modal
                            $('#openmiscrModal').modal('show');
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
        $('#miscForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: S.url('construction/update_misc'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openmiscrModal').modal('hide');

                        // Show success message with SweetAlert
                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

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

            });
        });
    });

    $(document).ready(function() {
        $('#returnform').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: S.url('construction/update_rerturn'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Close the update modal
                        $('#openreturnrModal').modal('hide');

                        Swal.fire({
                            title: 'Success',
                            text: 'User updated successfully!',
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {

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

            });
        });
    });

    $(document).ready(function() {
        var dataTable = $('#grand_account').DataTable({
            dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
            buttons: [{
                    extend: 'print',
                    // Masthead only. Which table this is, which material it is
                    // filtered to and which site it belongs to all go in the
                    // table's own first row instead — printHeadingRow, below.
                    title: PRINT_MASTHEAD,
                    text: 'Print Record',
                    className: 'dt-button',
                    exportOptions: {
                        columns: ':visible' // Export only visible columns
                    },
                    customize: function(win) {
                        // Add watermark
                        $(win.document.body).prepend(
                            '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                            '<img src="' + S.watermark + '" style="width:500px;" />' +
                            '</div>'
                        );
                        // Insert the heading row for the table (centered with colspan="6")
                        printHeadingRow(win, printHeading('Grand Total'));

                    }
                },
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'dt-button',
                    filename: 'data_export'
                }
            ]
        });

        // Add a new row with values
        dataTable.row.add([
            '1',
            'Civil Total',
            '' + S.totals.civil + '',
        ]).draw();

        dataTable.row.add([
            '2',
            'Finish Total',
            '' + S.totals.finishing + '',
        ]).draw();

        dataTable.row.add([
            '3',
            'Labour total',
            '' + S.totals.labour + '',
        ]).draw();

        dataTable.row.add([
            '4',
            'Miscellaneous Total',
            '' + S.totals.misc + '',
        ]).draw();

        dataTable.row.add([
            '<h3>5</h3>',
            '<h3>Grand Total</h3>',
            '<h3>' + S.totals.grand + '</h3>',
        ]).draw();
    });


/*
 * The tables render their action buttons as HTML with inline onclick handlers,
 * and an inline handler is resolved against the global scope. In the view these
 * were plain <script> declarations, which are global; in a module they are not,
 * so every one of them has to be published deliberately.
 *
 * Miss one and the button silently does nothing, so this list is the contract
 * between the render callbacks above and the markup they produce.
 */
Object.assign(window, {
    openUpdateModal,
    openfinishModal,
    openmiscrModal,
    openreturnrModal22,
    deleteCivil,
    deletefinish,
    deletelabour,
    deletemisc,
    return_delete,
});

}
