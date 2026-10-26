@extends('me::master')
@section('title', sm_t('Bus Schedule Chart'))
@push('buttons')
    <a href="{{ route('transport.routes') }}" class="btn btn-sm btn-encodex-list no-print">
        <i class="fas fa-arrow-left"></i> {{ sm_t('Routes') }}
    </a>
    <a href="{{ route('transport.schedule.print') }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> {{ sm_t('Print') }}
    </a>
@endpush

@section('content')

@php
$dirColors = [
    'morning'   => ['bg' => '#fff3cd', 'border' => '#ffc107', 'text' => '#664d03'],
    'afternoon' => ['bg' => '#cff4fc', 'border' => '#0dcaf0', 'text' => '#055160'],
    'evening'   => ['bg' => '#d1e7dd', 'border' => '#198754', 'text' => '#0f5132'],
    'night'     => ['bg' => '#d3d3d3', 'border' => '#6c757d', 'text' => '#1a1a1a'],
];
@endphp

{{-- Filter --}}
<div class="card shadow mb-3 no-print">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-0">{{ sm_t('Filter Direction') }}</label>
                <select id="filterDir" class="form-control form-control-sm" onchange="filterSchedule()">
                    <option value="">{{ sm_t('All Directions') }}</option>
                    @foreach($directions as $dk => $dv)
                    <option value="{{ $dk }}">{{ $dv }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-0">{{ sm_t('Filter Day') }}</label>
                <select id="filterDay" class="form-control form-control-sm" onchange="filterSchedule()">
                    <option value="">{{ sm_t('All Days') }}</option>
                    @foreach($allDays as $dk => $dv)
                    <option value="{{ $dk }}">{{ $dv }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-0">{{ sm_t('Filter Route') }}</label>
                <select id="filterRoute" class="form-control form-control-sm" onchange="filterSchedule()">
                    <option value="">{{ sm_t('All Routes') }}</option>
                    @foreach($routes as $route)
                    <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-sm btn-encodex-clear" onclick="resetFilter()">
                    <i class="fas fa-times"></i> {{ sm_t('Reset') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Legend --}}
<div class="d-flex gap-3 mb-3 flex-wrap no-print">
    @foreach($directions as $dk => $dv)
    <span class="badge" style="background:{{ $dirColors[$dk]['border'] }};color:{{ $dirColors[$dk]['text'] }};border:1px solid {{ $dirColors[$dk]['border'] }}">
        {{ $dv }}
    </span>
    @endforeach
    <span class="ms-auto text-muted small">{{ sm_t('Sorted by departure time') }}</span>
</div>

{{-- ── Timeline Chart ── --}}
@php
    // Collect all schedules across all routes and sort by departure time
    $allSchedules = collect();
    foreach ($routes as $route) {
        foreach ($route->schedules as $sched) {
            $allSchedules->push([
                'sched'   => $sched,
                'route'   => $route,
            ]);
        }
    }
    $allSchedules = $allSchedules->sortBy(fn($s) => $s['sched']->departure_time);

    // Get min/max time for chart axis
    $times = $allSchedules->map(fn($s) => $s['sched']->departure_time)->sort()->values();
    $minTime = $times->isNotEmpty() ? strtotime('today ' . $times->first()) : strtotime('today 06:00');
    $maxTime = $times->isNotEmpty() ? strtotime('today ' . $times->last()) + 3600 : strtotime('today 18:00');
    $totalMins = ($maxTime - $minTime) / 60;
@endphp

{{-- Route-wise schedule cards --}}
@foreach($routes as $route)
@php $hasSchedules = $route->schedules->isNotEmpty(); @endphp
<div class="route-block mb-3" data-route="{{ $route->id }}" @if(!$hasSchedules) style="display:none" @endif>
    <div class="card shadow">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $route->route_name }}</strong>
                <span class="text-muted ms-2 small">
                    <i class="fas fa-map-marker-alt me-1"></i>{{ $route->start_point }}
                    <i class="fas fa-arrow-right mx-1"></i>{{ $route->end_point }}
                </span>
            </div>
            <span class="badge bg-primary-subtle text-primary">{{ sm_money($route->fee, 0) }} | {{ $route->schedules->count() }} schedules</span>
        </div>
        <div class="card-body p-0">

            {{-- Table view --}}
            <table class="table table-sm table-bordered table-encodex mb-0" id="tbl_{{ $route->id }}">
                <thead class="text-center" style="font-size:12px">
                    <tr>
                        <th>#</th>
                        <th>{{ sm_t('Direction') }}</th>
                        <th>{{ sm_t('Departure') }}</th>
                        <th>{{ sm_t('Arrival') }}</th>
                        <th>{{ sm_t('Vehicle') }}</th>
                        <th>{{ sm_t('Driver') }}</th>
                        <th>{{ sm_t('Days') }}</th>
                        <th>{{ sm_t('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($route->schedules->sortBy('departure_time') as $i => $sched)
                    @php
                        $dc = $dirColors[$sched->direction] ?? ['bg'=>'#fff','border'=>'#ccc','text'=>'#333'];
                    @endphp
                    <tr class="sched-row text-center" style="font-size:12px"
                        data-dir="{{ $sched->direction }}"
                        data-days="{{ implode(',', $sched->days ?? []) }}"
                        data-route="{{ $route->id }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="badge" style="background:{{ $dc['border'] }};color:{{ $dc['text'] }}">
                                {{ $directions[$sched->direction] ?? $sched->direction }}
                            </span>
                        </td>
                        <td>
                            <strong style="color:{{ $dc['border'] }}">
                                {{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i A') }}
                            </strong>
                        </td>
                        <td>
                            {{ $sched->arrival_time ? \Carbon\Carbon::parse($sched->arrival_time)->format('h:i A') : '—' }}
                        </td>
                        <td>
                            @if($sched->vehicle)
                                <span class="badge bg-light-subtle text-dark border">{{ $sched->vehicle->vehicle_number }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td>{{ optional($sched->vehicle)->driver_name ?? '—' }}</td>
                        <td>
                            @if(empty($sched->days))
                                <span class="text-muted small">{{ sm_t('Every day') }}</span>
                            @else
                                <span class="small">
                                    @foreach($sched->days as $d){{ $allDays[$d] ?? $d }}{{ !$loop->last ? ', ' : '' }}@endforeach
                                </span>
                            @endif
                        </td>
                        <td class="text-start text-muted small">{{ $sched->notes ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Visual timeline bar --}}
            @if($route->schedules->isNotEmpty())
            <div class="p-3 border-top bg-light no-print" style="overflow-x:auto">
                <div style="min-width:600px;position:relative">
                    {{-- Time axis --}}
                    @php
                        $rMin = strtotime('today ' . $route->schedules->min('departure_time'));
                        $rMax = max(strtotime('today ' . $route->schedules->max('departure_time')) + 3600,
                                    $route->schedules->max('arrival_time') ? strtotime('today '.$route->schedules->max('arrival_time')) + 1800 : 0);
                        $rTotalMins = max(($rMax - $rMin) / 60, 60);
                        // Generate hour ticks
                        $hourStart = (int) date('G', $rMin);
                        $hourEnd   = (int) ceil(date('G', $rMax));
                    @endphp
                    <div class="d-flex" style="margin-bottom:6px;margin-left:0px">
                        @for($h = $hourStart; $h <= $hourEnd; $h++)
                        @php
                            $pct = max(0, (($h * 60) - (date('H', $rMin)*60 + date('i', $rMin))) / $rTotalMins * 100);
                        @endphp
                        <div style="position:absolute;left:{{ $pct }}%;font-size:10px;color:#999;transform:translateX(-50%)">
                            {{ $h < 12 ? $h.':00 AM' : ($h == 12 ? '12:00 PM' : ($h-12).':00 PM') }}
                        </div>
                        @endfor
                    </div>
                    <div style="height:30px;margin-top:16px;position:relative;background:#e9ecef;border-radius:4px">
                        @foreach($route->schedules->sortBy('departure_time') as $sched)
                        @php
                            $depMins  = (strtotime('today '.$sched->departure_time) - $rMin) / 60;
                            $leftPct  = ($depMins / $rTotalMins) * 100;
                            $dc       = $dirColors[$sched->direction] ?? ['bg'=>'#fff','border'=>'#ccc','text'=>'#333'];
                            $arrMins  = $sched->arrival_time
                                ? (strtotime('today '.$sched->arrival_time) - $rMin) / 60
                                : $depMins + 10;
                            $widthPct = max(3, (($arrMins - $depMins) / $rTotalMins) * 100);
                        @endphp
                        <div title="{{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i A') }}{{ $sched->arrival_time ? ' → '.\Carbon\Carbon::parse($sched->arrival_time)->format('h:i A') : '' }}{{ $sched->vehicle ? ' | '.$sched->vehicle->vehicle_number : '' }}"
                             style="position:absolute;left:{{ $leftPct }}%;width:{{ $widthPct }}%;height:100%;
                                    background:{{ $dc['bg'] }};border:2px solid {{ $dc['border'] }};
                                    border-radius:4px;cursor:pointer;display:flex;align-items:center;
                                    justify-content:center;font-size:10px;font-weight:600;color:{{ $dc['text'] }};
                                    overflow:hidden;white-space:nowrap;padding:0 4px"
                             data-bs-toggle="tooltip">
                            {{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i') }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endforeach

@if($routes->where(fn($r) => $r->schedules->isNotEmpty())->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle me-1"></i>
    No schedules found. Go to <a href="{{ route('transport.routes') }}">{{ sm_t('Transport Routes') }}</a> and add schedules for each route.
</div>
@endif

<script>
// Initialize tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { placement: 'top' });
});

function filterSchedule() {
    const dir   = document.getElementById('filterDir').value;
    const day   = document.getElementById('filterDay').value;
    const route = document.getElementById('filterRoute').value;

    document.querySelectorAll('.route-block').forEach(block => {
        if (route && block.dataset.route !== route) {
            block.style.display = 'none'; return;
        }
        const rows = block.querySelectorAll('.sched-row');
        let anyVisible = false;
        rows.forEach(row => {
            const matchDir = !dir || row.dataset.dir === dir;
            const matchDay = !day || !row.dataset.days || row.dataset.days === '' || row.dataset.days.split(',').includes(day);
            const show = matchDir && matchDay;
            row.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });
        block.style.display = anyVisible ? '' : 'none';
    });
}

function resetFilter() {
    document.getElementById('filterDir').value   = '';
    document.getElementById('filterDay').value   = '';
    document.getElementById('filterRoute').value = '';
    document.querySelectorAll('.route-block').forEach(b => b.style.display = '');
    document.querySelectorAll('.sched-row').forEach(r => r.style.display = '');
}
</script>

<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    .route-block { page-break-inside: avoid; }
}
</style>
@endsection
