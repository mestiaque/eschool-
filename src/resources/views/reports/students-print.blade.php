@extends('me::printMaster2')
@section('title', sm_t('Student Report'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Student Report',
    'subtitle' => collect([
        request('class_id') ? 'Class: ' . optional($classes->firstWhere('id', request('class_id')))->name : null,
        request('gender') ? 'Gender: ' . ucfirst(request('gender')) : null,
    ])->filter()->implode(' | ') ?: null,
])

<table>
    <thead>
        <tr>
            <th>{{ sm_t('ID') }}</th>
            <th>{{ sm_t('Name') }}</th>
            <th>{{ sm_t('Class') }}</th>
            <th>{{ sm_t('Section') }}</th>
            <th>{{ sm_t('Roll') }}</th>
            <th>{{ sm_t('Phone') }}</th>
            <th>{{ sm_t('Gender') }}</th>
            <th>{{ sm_t('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
        <tr>
            <td>{{ $student->student_id }}</td>
            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
            <td>{{ $student->class->name ?? '-' }}</td>
            <td>{{ $student->section->name ?? '-' }}</td>
            <td>{{ $student->roll_number ?? '-' }}</td>
            <td>{{ $student->phone }}</td>
            <td>{{ $student->gender }}</td>
            <td>{{ $student->is_active ? 'Active' : 'Inactive' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No students found</td>
        </tr>
        @endforelse
    </tbody>
</table>

@endsection
