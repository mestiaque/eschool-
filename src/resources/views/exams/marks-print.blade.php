@extends('me::printMaster2')
@section('title', sm_t('Marks Entry Sheet'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Marks Entry Sheet',
    'subtitle' => collect([$examObj?->name, $subjectObj?->name])->filter()->implode(' — ') ?: null,
])

@if($students->isEmpty())
<p class="text-center">{{ sm_t('No data selected.') }}</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ sm_t('Student Name') }}</th>
            <th>{{ sm_t('Roll') }}</th>
            <th>{{ sm_t('Written') }}</th>
            <th>{{ sm_t('MCQ') }}</th>
            <th>{{ sm_t('Practical') }}</th>
            <th>{{ sm_t('CA') }}</th>
            <th>{{ sm_t('Total') }}</th>
            <th>{{ sm_t('Grade') }}</th>
            <th>{{ sm_t('Absent') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $i => $student)
        @php $mark = $marksMap[$student->id] ?? null; @endphp
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <td class="text-center">{{ $student->roll_number ?? '—' }}</td>
            <td class="text-center">{{ $mark?->written ?? '—' }}</td>
            <td class="text-center">{{ $mark?->mcq ?? '—' }}</td>
            <td class="text-center">{{ $mark?->practical ?? '—' }}</td>
            <td class="text-center">{{ $mark?->ca ?? '—' }}</td>
            <td class="text-center">{{ $mark?->total_mark ?? '—' }}</td>
            <td class="text-center">{{ $mark?->grade_name ?? '—' }}</td>
            <td class="text-center">{{ sm_t($mark && $mark->is_absent ? 'Yes' : 'No') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@endsection
