@extends('me::printMaster2')
@section('title', sm_t('Class Routines'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Class Routine List',
    'subtitle' => collect([
        request('class_id') ? 'Class: ' . optional($classes->firstWhere('id', request('class_id')))->name : null,
        request('day') ? 'Day: ' . request('day') : null,
        request('room_id') ? 'Room: ' . optional($rooms->firstWhere('id', request('room_id')))->name : null,
        request('title') ? 'Title: ' . request('title') : null,
    ])->filter()->implode(' | ') ?: null,
])

<table>
    <thead>
        <tr>
            <th>{{ sm_t('Title') }}</th>
            <th>{{ sm_t('Class') }}</th>
            <th>{{ sm_t('Section') }}</th>
            <th>{{ sm_t('Subject') }}</th>
            <th>{{ sm_t('Day') }}</th>
            <th>{{ sm_t('Start Time') }}</th>
            <th>{{ sm_t('End Time') }}</th>
            <th>{{ sm_t('Room') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($routines as $routine)
        <tr>
            <td>{{ $routine->title ?? '—' }}</td>
            <td>{{ $routine->class->name ?? '' }}</td>
            <td>{{ $routine->section->name ?? '-' }}</td>
            <td>{{ $routine->subject->name ?? '' }}</td>
            <td>{{ $routine->day }}</td>
            <td>{{ sm_time($routine->start_time) }}</td>
            <td>{{ sm_time($routine->end_time) }}</td>
            <td>{{ $routine->room->name ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No routines found</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection
