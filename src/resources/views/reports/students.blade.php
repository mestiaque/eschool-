@extends('me::master')
@section('title', sm_t('Student Report'))

@push('buttons')
<a href="{{ route('reports.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print">
    <i class="fas fa-print"></i> Print
</a>
@endpush

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-encodex table-sm mb-0">
            <thead class="table-light">
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
                @foreach($students as $student)
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->class->name ?? '-' }}</td>
                    <td>{{ $student->section->name ?? '-' }}</td>
                    <td>{{ $student->roll_number ?? '-' }}</td>
                    <td>{{ $student->phone }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>
                        @if($student->is_active)
                        <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
