@extends('layouts.admin')

@section('title', 'Dashboard · BASCON GROUP')

@section('breadcrumbs')
    <span data-crumb-current>Dashboard</span>
@endsection

@php
    // The chart scales to its own largest point. Both series share one axis —
    // they are the same unit, and a second y-scale would let the two be drawn
    // to different rulers and silently misread against each other.
    $peak = max(1, collect($months)->flatMap(fn ($m) => [$m['cost'], $m['received']])->max());

    /* ------------------------------------------------------------ geometry
       The plot is one fixed 720x240 viewBox scaled to whatever width the card
       has, so every coordinate below can be worked out here in PHP instead of
       being measured in the browser. Strokes are drawn with
       vector-effect="non-scaling-stroke" so they stay 2px however far it
       scales. */
    $W = 720; $H = 240; $padX = 18; $padTop = 16; $padBottom = 26;
    $plotH = $H - $padTop - $padBottom;
    $step = (count($months) > 1) ? ($W - 2 * $padX) / (count($months) - 1) : 0;
    $baseline = $padTop + $plotH;

    $pointsFor = function (string $key) use ($months, $peak, $padX, $padTop, $plotH, $step) {
        $out = [];
        foreach ($months as $i => $m) {
            $out[] = [
                round($padX + $i * $step, 2),
                round($padTop + (1 - $m[$key] / $peak) * $plotH, 2),
            ];
        }
        return $out;
    };

    /* A cubic through each pair with horizontal control handles: it reads as a
       curve rather than a zigzag, and because both handles are level with the
       points they join it cannot overshoot into negative territory the way a
       cardinal spline can. */
    $curve = function (array $pts) {
        $d = 'M'.$pts[0][0].','.$pts[0][1];
        for ($i = 1; $i < count($pts); $i++) {
            [$x0, $y0] = $pts[$i - 1];
            [$x1, $y1] = $pts[$i];
            $h = round(($x1 - $x0) / 2, 2);
            $d .= ' C'.($x0 + $h).','.$y0.' '.($x1 - $h).','.$y1.' '.$x1.','.$y1;
        }
        return $d;
    };

    $series = [];
    foreach (['cost', 'received'] as $key) {
        $pts = $pointsFor($key);
        $series[$key] = [
            'points' => $pts,
            'line' => $curve($pts),
            'area' => $curve($pts).' L'.end($pts)[0].','.$baseline.' L'.$pts[0][0].','.$baseline.' Z',
        ];
    }

    // Axis labels: whole millions read faster than nine digits on a gridline.
    $compact = function ($v) {
        if ($v >= 1000000) return rtrim(rtrim(number_format($v / 1000000, 1), '0'), '.').'M';
        if ($v >= 1000) return round($v / 1000).'k';
        return (string) round($v);
    };

    $pendingTotal = array_sum($pending);
@endphp

@section('content')
<x-page-header title="Dashboard"
               subtitle="Every site's position at a glance — cost recorded, money received, and what is still owed." />


{{-- --------------------------------------------------------------- charts --}}
{{-- Change over time, two measures, one unit, one axis. Full width now that
     nothing sits beside it. --}}
<div>
    <x-card>
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="ui-card-title">Cost and receipts by month</h2>
                <p class="ui-card-subtitle">Last 12 months across every site.</p>
            </div>

            {{-- Two series, so a legend is not optional. --}}
            <ul class="viz-legend">
                <li><span class="viz-swatch" data-series="cost"></span>Cost recorded</li>
                <li><span class="viz-swatch" data-series="received"></span>Payments received</li>
            </ul>
        </div>

        <div class="viz-chart">
            {{-- Uniform scaling, deliberately: with preserveAspectRatio="none" a
                 wide card stretches x and squashes y by different factors, and
                 every dot renders as an ellipse. The box keeps the viewBox's
                 3:1 ratio instead and sizes itself from the card width. --}}
            <svg class="viz-svg" viewBox="0 0 {{ $W }} {{ $H }}" preserveAspectRatio="xMidYMid meet"
                 role="img"
                 aria-label="Monthly cost recorded against payments received for the last twelve months.">
                <defs>
                    {{-- Stops are painted from the same two validated series
                         tokens, so the fill can never drift from its line. --}}
                    @foreach (['cost', 'received'] as $key)
                        <linearGradient id="viz-grad-{{ $key }}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   class="viz-stop-a" data-series="{{ $key }}" />
                            <stop offset="100%" class="viz-stop-b" data-series="{{ $key }}" />
                        </linearGradient>
                    @endforeach
                </defs>

                {{-- Grid: recessive, and only four lines. --}}
                @foreach ([0, 0.25, 0.5, 0.75, 1] as $f)
                    @php $y = round($padTop + (1 - $f) * $plotH, 2); @endphp
                    <line class="viz-grid" x1="0" y1="{{ $y }}" x2="{{ $W }}" y2="{{ $y }}" />
                    <text class="viz-axis" x="4" y="{{ $y - 5 }}">{{ $compact($peak * $f) }}</text>
                @endforeach

                @foreach (['cost', 'received'] as $key)
                    <path class="viz-area" data-series="{{ $key }}"
                          fill="url(#viz-grad-{{ $key }})" d="{{ $series[$key]['area'] }}" />
                    <path class="viz-line" data-series="{{ $key }}" d="{{ $series[$key]['line'] }}"
                          vector-effect="non-scaling-stroke" fill="none" />
                @endforeach

                @foreach (['cost', 'received'] as $key)
                    @foreach ($series[$key]['points'] as $i => [$x, $y])
                        <circle class="viz-dot" data-series="{{ $key }}" cx="{{ $x }}" cy="{{ $y }}" r="3.5"
                                vector-effect="non-scaling-stroke"
                                style="animation-delay: {{ 420 + $i * 45 }}ms" />
                    @endforeach
                @endforeach
            </svg>

            {{-- Hover lives in an HTML layer over the SVG: one full-height
                 column per month, so the crosshair and readout are picked up
                 anywhere in that month rather than only on a 7px dot. Both
                 series are read out together, which is the comparison the
                 chart exists to make. --}}
            <div class="viz-hits">
                @foreach ($months as $m)
                    <span class="viz-hit"
                          data-tip="{{ $m['label'] }} {{ $m['year'] }} · Cost {{ money($m['cost']) }} · Received {{ money($m['received']) }}"></span>
                @endforeach
            </div>
        </div>

        <div class="viz-ticks">
            @foreach ($months as $m)
                <span class="viz-tick">{{ $m['label'] }}</span>
            @endforeach
        </div>

        {{-- The table view the palette's contrast warning obliges, and the
             thing anyone will actually want when they need exact figures. --}}
        <details class="viz-table">
            <summary>Show the numbers</summary>

            <div class="ui-table-wrap mt-3">
                <table class="ui-table">
                    <thead>
                        <tr><th>Month</th><th class="text-right">Cost recorded</th><th class="text-right">Payments received</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($months as $m)
                            <tr>
                                <td>{{ $m['label'] }} {{ $m['year'] }}</td>
                                <td class="text-right tabular-nums">@money($m['cost'])</td>
                                <td class="text-right tabular-nums">@money($m['received'])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </x-card>

</div>

{{-- ------------------------------------------------------- queue + sites --}}
<div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">

    <x-card title="Waiting on you" subtitle="Entries a worker has filed that are not yet approved.">
        @if ($pendingTotal === 0)
            <x-empty-state title="Nothing pending"
                           message="Every civil, finishing and labour entry has been reviewed." />
        @else
            <ul class="space-y-2">
                <li><a href="{{ url('admin_setting/civil_requets') }}" class="ui-queue-row">
                    <span>Civil materials</span><span class="ui-queue-count">{{ $pending['civil'] }}</span></a></li>
                <li><a href="{{ url('admin_setting/finish_requets') }}" class="ui-queue-row">
                    <span>Finishing materials</span><span class="ui-queue-count">{{ $pending['finishing'] }}</span></a></li>
                <li><a href="{{ url('admin_setting/civil_requets') }}" class="ui-queue-row">
                    <span>Labour instalments</span><span class="ui-queue-count">{{ $pending['labour'] }}</span></a></li>
            </ul>
        @endif

        <div class="mt-5 border-t border-line pt-4">
            <p class="text-[12.5px] text-neutral-500">Company expenses recorded</p>
            <p class="mt-1 text-[19px] font-semibold tabular-nums text-neutral-900">@money($expenses)</p>
            <a href="{{ url('admin_setting/show_expense') }}"
               class="mt-2 inline-block text-[12.5px] font-medium text-brand-700">View expenses →</a>
        </div>
    </x-card>

    <x-card class="xl:col-span-2" flush title="Sites by recorded value"
            subtitle="Balance is what the client has paid, less what has been recorded against the site.">
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Status</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Received</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sites as $site)
                        <tr data-row-href="{{ url('admin_setting/show_con_details/' . $site['id']) }}">
                            <td class="font-medium text-neutral-900">{{ $site['name'] }}</td>
                            <td>
                                <span class="ui-pill {{ $site['closed'] ? 'is-muted' : 'is-live' }}">
                                    {{ $site['closed'] ? 'Closed' : 'Running' }}
                                </span>
                            </td>
                            <td class="text-right tabular-nums">@money($site['cost'])</td>
                            <td class="text-right tabular-nums">@money($site['received'])</td>
                            {{-- Same rule as the site page: below zero is money owed. --}}
                            <td @class(['text-right tabular-nums font-semibold', 'text-rose-600' => $site['balance'] < 0])>
                                @money($site['balance'])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
