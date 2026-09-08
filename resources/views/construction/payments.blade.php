@extends('layouts.app')

@section('title', 'Payments · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Payments</span>
@endsection

@section('content')
<x-page-header title="Payments"
               subtitle="Open a site to record the payments received against it." />

{{-- The same running / closed split as the Sites screen, built the same way:
     every site is fetched once and the tabs filter what is already here, so
     switching costs no request. --}}
<div class="mb-5 flex flex-wrap items-center gap-2.5" id="payment-status-tabs" role="tablist">
    <button type="button" class="ui-btn ui-btn-lg ui-btn-primary" role="tab"
            aria-selected="true" data-status="running">
        Running sites
        <span data-count></span>
    </button>
    <button type="button" class="ui-btn ui-btn-lg ui-btn-secondary" role="tab"
            aria-selected="false" data-status="closed">
        Closed sites
        <span data-count></span>
    </button>
</div>

<x-card flush>
    <table id="payment_sites" width="100%" class="ui-table">
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Site</th>
                <th class="text-right">Payment received</th>
                <th class="text-right">Remaining balance</th>
                <th class="w-px whitespace-nowrap text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sites as $site)
                <tr data-status="{{ $site['closed'] ? 'closed' : 'running' }}"
                    data-row-href="{{ url('construction/payment_details/' . $site['id']) }}">
                    <td></td>
                    <td class="font-medium text-neutral-900">{{ $site['name'] }}</td>
                    <td class="text-right tabular-nums" data-order="{{ $site['total'] }}">@money($site['total'])</td>
                    {{-- Same figure as the site's Grand Total tab: payments
                         received less the site's costs. Negative means the site
                         has spent past what the client has paid in. --}}
                    <td class="text-right tabular-nums {{ $site['remaining'] < 0 ? 'text-rose-600 font-medium' : '' }}"
                        data-order="{{ $site['remaining'] }}">@money($site['remaining'])</td>
                    <td class="whitespace-nowrap text-right">
                        <a href="{{ url('construction/payment_details/' . $site['id']) }}"
                           class="ui-btn ui-btn-sm ui-btn-secondary">View detail</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-card>
@endsection

@push('scripts')
<script>
    var siteStatus = 'running';

    // Rows are already in the markup, so this table is DataTables over a DOM
    // source rather than an ajax one — nothing here needs a round trip.
    $(document).ready(function () {
        /* Counted from the markup BEFORE DataTables takes the rows over.
           It detaches rows that are filtered out or on another page, so
           counting <tr>s afterwards only ever counts the tab already on
           screen — which is why the inactive tab always read (0).

           The site list is fixed for the life of the page, so these are
           written once rather than recomputed on every draw. */
        var counts = { running: 0, closed: 0 };

        $('#payment_sites tbody tr[data-status]').each(function () {
            var status = $(this).data('status');
            if (counts[status] !== undefined) counts[status]++;
        });

        $('#payment-status-tabs button[data-status]').each(function () {
            $(this).find('[data-count]').text('(' + counts[$(this).data('status')] + ')');
        });

        var table = $('#payment_sites').DataTable({
            "order": [[1, 'asc']],
            "columnDefs": [{ "targets": 0, "orderable": false }]
        });

        $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData, counter) {
            if (!settings.nTable || settings.nTable.id !== 'payment_sites') return true;

            var row = settings.aoData[index].nTr;
            return !row || $(row).data('status') === siteStatus;
        });

        // Sr No numbers the rows the active tab is showing, not their position
        // in the full set.
        table.on('draw', function () {
            table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        });

        table.draw();

        $('#payment-status-tabs').on('click', 'button[data-status]', function () {
            var status = $(this).data('status');
            if (status === siteStatus) return;

            siteStatus = status;

            $('#payment-status-tabs button[data-status]')
                .attr('aria-selected', 'false')
                .removeClass('ui-btn-primary').addClass('ui-btn-secondary');

            $(this).attr('aria-selected', 'true')
                .removeClass('ui-btn-secondary').addClass('ui-btn-primary');

            table.draw();
        });
    });
</script>
@endpush
