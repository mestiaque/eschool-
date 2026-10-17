@extends('me::printMaster2')
@section('title', $room->name . ' - Schedule')

@section('contents')

@include('school-management::partials.print-header', [
    'title' => $room->name . ' - Schedule',
    'subtitle' => $room->building ? 'Building: ' . $room->building : null,
])

<table>
    <thead>
        <tr>
            <th style="width: 100px;">{{ sm_t('Day') }}</th>
            <th style="width: 160px;">{{ sm_t('Time') }}</th>
            <th>{{ sm_t('Subject') }}</th>
            <th>{{ sm_t('Class / Section') }}</th>
            <th>{{ sm_t('Title') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($days as $day)
        @php $daySchedule = $scheduleByDay[$day]; @endphp
        @forelse($daySchedule as $routine)
        <tr>
            <td>{{ $day }}</td>
            <td>
                {{ \Carbon\Carbon::parse($routine->start_time)->format('h:i A') }}
                &ndash;
                {{ \Carbon\Carbon::parse($routine->end_time)->format('h:i A') }}
            </td>
            <td>{{ $routine->subject->name ?? '-' }}</td>
            <td>
                {{ $routine->class->name ?? '-' }}
                @if($routine->section)
                &bull; {{ $routine->section->name }}
                @endif
            </td>
            <td>{{ $routine->title ?? '—' }}</td>
        </tr>
        @empty
        <tr>
            <td>{{ $day }}</td>
            <td colspan="4" class="text-center">No classes scheduled</td>
        </tr>
        @endforelse
        @endforeach
    </tbody>
</table>

@endsection
