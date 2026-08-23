@extends('layouts.app')

@section('title', 'Construction Costs · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Construction Costs</span>
@endsection

@section('content')
<x-page-header title="Construction Costs"
               subtitle="Every entry recorded against your project, by category." />

{{--
    One card, four ledgers.

    These replace four separate pages that each showed a roll-up — one line per
    material, with no way to see what made a figure up. The tables here are the
    same ones the site team works in: choose a category and every entry recorded
    against it is listed.

    The four differ only in which columns they have, so the markup below is a
    loop over one description of them and the script is a single loader keyed by
    the same names. Miscellaneous has no categories, so its pane has no picker.

    Every pane opens on its full list; the picker narrows it. A page that shows
    nothing until you choose something reads as broken, and the total above the
    table is only meaningful when it starts from the whole.
--}}
@php
    $ledgers = [
        [
            'key' => 'civil',
            'label' => 'Civil Materials',
            'options' => $civilCategories,
            'pick' => 'All materials',
            'columns' => [
                ['data' => 'serial_number', 'title' => 'Sr No'],
                ['data' => 'date', 'title' => 'Date'],
                ['data' => 'type', 'title' => 'Material'],
                ['data' => 'quantity', 'title' => 'Quantity'],
                ['data' => 'price', 'title' => 'Price'],
            ],
        ],
        [
            'key' => 'finishing',
            'label' => 'Finishing Materials',
            'options' => $finishCategories,
            'pick' => 'All materials',
            'columns' => [
                ['data' => 'serial_number', 'title' => 'Sr No'],
                ['data' => 'date', 'title' => 'Date'],
                ['data' => 'type', 'title' => 'Material'],
                ['data' => 'detail', 'title' => 'Detail'],
                ['data' => 'quantity', 'title' => 'Quantity'],
                ['data' => 'price', 'title' => 'Price'],
            ],
        ],
        [
            'key' => 'labour',
            'label' => 'Labour',
            'options' => $labourTypes,
            'pick' => 'All labour types',
            'columns' => [
                ['data' => 'serial_number', 'title' => 'Sr No'],
                ['data' => 'date', 'title' => 'Date'],
                ['data' => 'type', 'title' => 'Labour'],
                ['data' => 'description', 'title' => 'Detail'],
                ['data' => 'instalmet', 'title' => 'Instalment'],
            ],
        ],
        [
            'key' => 'misc',
            'label' => 'Miscellaneous',
            'options' => null,
            'pick' => null,
            'columns' => [
                ['data' => 'serial_number', 'title' => 'Sr No'],
                ['data' => 'date', 'title' => 'Date'],
                ['data' => 'detail', 'title' => 'Detail'],
                ['data' => 'price', 'title' => 'Price'],
            ],
        ],
        // The four above plus returns, on one timeline. Its own endpoint, so
        // `all` is not a category the others have to make an exception for.
        [
            'key' => 'all',
            'label' => 'All Data Entries',
            'options' => null,
            'pick' => null,
            'endpoint' => 'client/con_all_entries',
            'columns' => [
                ['data' => 'serial_number', 'title' => 'Sr No'],
                ['data' => 'date', 'title' => 'Date'],
                ['data' => 'source', 'title' => 'Source'],
                ['data' => 'type', 'title' => 'Type'],
                ['data' => 'detail', 'title' => 'Detail'],
                ['data' => 'quantity', 'title' => 'Quantity'],
                ['data' => 'price', 'title' => 'Price'],
            ],
        ],
    ];

    // What the script below needs: the same list, minus the <option> values it
    // does not use. Assembled here rather than inside @json, which cannot parse
    // a multi-line expression.
    $ledgerConfig = collect($ledgers)->map(fn ($ledger) => [
        'key' => $ledger['key'],
        'label' => $ledger['label'],
        'hasCategories' => $ledger['options'] !== null,
        'columns' => $ledger['columns'],
        'url' => url($ledger['endpoint'] ?? 'client/con_entries'),
    ])->values();
@endphp

{{-- A plain card, not a .ui-tab-total-host: that machinery pins the total to
     a tab strip's line by measuring the space beside it, and this page's strip
     runs the full width, so there is never any. The total shares a line with
     the picker below instead, which is a row this page can simply lay out. --}}
<div class="ui-card">
    <ul class="ui-tabs" role="tablist">
        @foreach ($ledgers as $ledger)
            <li role="presentation">
                <a href="#pane_{{ $ledger['key'] }}" aria-controls="pane_{{ $ledger['key'] }}"
                   role="tab" data-toggle="tab">{{ $ledger['label'] }}</a>
            </li>
        @endforeach

        {{-- Last, because it is the sum of the five before it. --}}
        <li role="presentation">
            <a href="#pane_grand" aria-controls="pane_grand" role="tab" data-toggle="tab">Grand Total</a>
        </li>
    </ul>

    <div class="tab-content">
        @foreach ($ledgers as $ledger)
            <div role="tabpanel" class="tab-pane" id="pane_{{ $ledger['key'] }}">
                {{-- Picker on the left, total on the right, centred on each
                     other. Everything wraps rather than shrinking, so on a
                     narrow screen the picker takes the width it needs and the
                     total drops to its own line, still right-aligned. --}}
                <div class="mb-4 flex flex-wrap items-center gap-4">
                    @if ($ledger['options'] !== null)
                        <label class="ui-label mb-0 shrink-0"
                               for="pick_{{ $ledger['key'] }}">Select List</label>

                        <div class="flex min-w-0 flex-1 items-center gap-4 sm:flex-none">
                            <select class="ui-select min-w-0 flex-1 sm:w-56 sm:flex-none"
                                    id="pick_{{ $ledger['key'] }}"
                                    data-ledger="{{ $ledger['key'] }}">
                                <option value="">{{ $ledger['pick'] }}</option>
                                @foreach ($ledger['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>

                            {{-- Refetches without a page reload; see [data-reload] in ui.js. --}}
                            <button type="button" class="ui-icon-btn ui-tip ui-reload"
                                    data-reload="#pick_{{ $ledger['key'] }}" data-tip="Reload data"
                                    aria-label="Reload data">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M3 12a9 9 0 0 1 15.5-6.2L21 8" />
                                    <path d="M21 3v5h-5" />
                                    <path d="M21 12a9 9 0 0 1-15.5 6.2L3 16" />
                                    <path d="M3 21v-5h5" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    <div class="ui-total ml-auto" id="total_{{ $ledger['key'] }}"></div>
                </div>

                <div>
                    <table id="table_{{ $ledger['key'] }}" width="100%" class="ui-table">
                        <thead>
                            <tr>
                                @foreach ($ledger['columns'] as $column)
                                    <th>{{ $column['title'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        @endforeach

        {{-- Grand Total. Everything here is known at render time, so the table
             is written out in Blade and DataTables is attached to the rows that
             are already in it — there is nothing to fetch. --}}
        <div role="tabpanel" class="tab-pane" id="pane_grand">
            <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <x-stat-card label="Payments received" :value="money($payment_recieved)"
                             icon="check" tone="success" wash />
                {{-- Below zero is money still owed, and reads as such. --}}
                <x-stat-card label="Remaining balance" :value="money($Remainung_Balace)" icon="clock"
                             :tone="$Remainung_Balace < 0 ? 'danger' : 'success'" wash />
            </div>

            <table id="table_grand" width="100%" class="ui-table">
                <thead>
                    <tr><th>Sr No</th><th>Type</th><th class="text-right">Total</th></tr>
                </thead>
                <tbody>
                    @foreach ([
                        ['Civil Total', $civil_price],
                        ['Finishing Total', $finish_price],
                        ['Labour Total', $labour_price],
                        ['Miscellaneous Total', $misc_price],
                    ] as $i => [$label, $amount])
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $label }}</td>
                            <td class="text-right tabular-nums">@money($amount)</td>
                        </tr>
                    @endforeach

                    {{-- Returns come off the total, so they are shown as the
                         deduction they are rather than another cost. --}}
                    @if ($return_total)
                        <tr>
                            <td>5</td>
                            <td>Less returns</td>
                            <td class="text-right tabular-nums text-rose-600">-@money($return_total)</td>
                        </tr>
                    @endif

                    <tr class="font-semibold">
                        <td>{{ $return_total ? 6 : 5 }}</td>
                        <td>Grand Total</td>
                        <td class="text-right tabular-nums">@money($Grand_total)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.CLIENT_LEDGERS = {!! json_encode($ledgerConfig) !!};

</script>

<script>
    /*
     * One loader for all four tables.
     *
     * Each ledger has its own columns but the same shape of request and the
     * same handling of the response, so the differences live in the config
     * above rather than in four near-identical copies of this.
     */
    $(function () {
        var tables = {};

        function load(ledger, category) {
            $.ajax({
                url: ledger.url,
                type: 'GET',
                data: { kind: ledger.key, category: category || '' },
                dataType: 'json',
                success: function (response) {
                    var rows = response.data || [];

                    rows.forEach(function (row, index) {
                        row.serial_number = index + 1;
                    });

                    $('#total_' + ledger.key).html(
                        '<h3>Total Price: ' + money(response.total_price) + '</h3>'
                    );

                    // Rebuilt rather than reloaded: DataTables keeps the column
                    // definitions from construction, and destroy() is the only
                    // way to hand it a fresh set of rows for the same table.
                    if (tables[ledger.key]) {
                        tables[ledger.key].destroy();
                    }

                    tables[ledger.key] = $('#table_' + ledger.key).DataTable({
                        data: rows,
                        columns: ledger.columns,
                        dom: '<"ui-dt-bar"lBf>rt<"ui-dt-foot"ip>',
                        createdRow: function (tr, data) {
                            if (data.source === 'Return Payment') $(tr).addClass('is-return');
                        },
                        buttons: [
                            {
                                extend: 'print',
                                text: 'Print Record',
                                className: 'dt-button',
                                title: '',
                                customize: function (win) {
                                    var heading = ledger.label + (category ? ' — ' + category : '');

                                    $(win.document.body).prepend(
                                        '<h1 style="text-align:center;font-size:20px;margin:0 0 14px;">BASCON GROUP</h1>'
                                    );

                                    $(win.document.body).prepend(
                                        '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                                        '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>'
                                    );

                                    // The heading goes in the table's own first
                                    // row so it travels with it across a page
                                    // break, rather than sitting above it.
                                    $(win.document.body).find('table thead').prepend(
                                        '<tr class="print-heading"><th colspan="' + ledger.columns.length + '" ' +
                                        'style="text-align:left;font-size:13px;">' + heading + '</th></tr>'
                                    );

                                    $(win.document.body).find('table').append(
                                        '<tfoot><tr><td colspan="' + (ledger.columns.length - 1) + '"></td>' +
                                        '<td style="white-space:nowrap;">Total: ' + money(response.total_price) +
                                        '</td></tr></tfoot>'
                                    );
                                }
                            },
                            {
                                extend: 'excel',
                                text: 'Download Excel',
                                className: 'dt-button',
                                filename: 'construction_' + ledger.key
                            }
                        ]
                    });
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error: ' + status + ' - ' + error);
                }
            });
        }

        $('#table_grand').DataTable({
            dom: '<"ui-dt-bar"B>rt',
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            buttons: [
                {
                    extend: 'print',
                    text: 'Print Record',
                    className: 'dt-button',
                    title: '',
                    customize: function (win) {
                        $(win.document.body).prepend(
                            '<h1 style="text-align:center;font-size:20px;margin:0 0 14px;">BASCON GROUP</h1>'
                        );
                        $(win.document.body).prepend(
                            '<div style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.2;">' +
                            '<img src="{{ asset('assets/images/water_mak.jpeg') }}" style="width:500px;" /></div>'
                        );
                        $(win.document.body).find('table thead').prepend(
                            '<tr><th colspan="3" style="text-align:left;font-size:13px;">Grand Total</th></tr>'
                        );
                    }
                },
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'dt-button',
                    filename: 'construction_grand_total'
                }
            ]
        });

        window.CLIENT_LEDGERS.forEach(function (ledger) {
            // Everything first; the picker filters from there. An empty
            // category is what the endpoint reads as "no filter".
            load(ledger, '');

            if (ledger.hasCategories) {
                $('#pick_' + ledger.key).on('change', function () {
                    load(ledger, $(this).val());
                });
            }
        });
    });
</script>
@endpush
