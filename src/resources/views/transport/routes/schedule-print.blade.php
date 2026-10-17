@extends('me::printMaster2')
@section('title', sm_t('Bus Schedule Chart'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Bus Schedule Chart',
    'subtitle' => null,
])

@forelse($routes as $route)
<div style="font-weight:bold;margin:10px 0 4px;font-size:13px">{{ $route->route_name }} ({{ $route->start_point }} → {{ $route->end_point }})</div>
<table>
    <thead>
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
        @forelse($route->schedules->sortBy('departure_time') as $i => $sched)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $directions[$sched->direction] ?? $sched->direction }}</td>
            <td>{{ \Carbon\Carbon::parse($sched->departure_time)->format('h:i A') }}</td>
            <td>{{ $sched->arrival_time ? \Carbon\Carbon::parse($sched->arrival_time)->format('h:i A') : '—' }}</td>
            <td>{{ $sched->vehicle?->vehicle_number ?? '—' }}</td>
            <td>{{ $sched->vehicle?->driver_name ?? '—' }}</td>
            <td>{{ empty($sched->days) ? 'Every day' : implode(', ', array_map(fn($d) => $allDays[$d] ?? $d, $sched->days)) }}</td>
            <td>{{ $sched->notes ?? '' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No schedules</td>
        </tr>
        @endforelse
    </tbody>
</table>
@empty
<p class="text-center">No active routes found.</p>
@endforelse

@endsection
