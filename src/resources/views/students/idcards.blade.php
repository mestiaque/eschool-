@extends('me::master')
@section('title', sm_t('Generate ID Cards'))

@section('content')
<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <select name="class_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($students->pluck('classe')->unique('id')->filter() as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th><input type="checkbox" id="selectAll" onchange="document.querySelectorAll('.sc').forEach(c=>c.checked=this.checked)"></th>
                    <th>{{ sm_t('Student ID') }}</th>
                    <th>{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Section') }}</th>
                    <th>{{ sm_t('Roll') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td><input type="checkbox" class="sc" value="{{ $student->id }}"></td>
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->classe?->name ?? '-' }}</td>
                    <td>{{ $student->section?->name ?? '-' }}</td>
                    <td>{{ $student->roll_number ?? '-' }}</td>
                    <td>
                        <a href="{{ route('students.idcard.print', $student->id) }}" target="_blank" class="btn btn-sm btn-encodex-print">
                            <i class="fas fa-id-card"></i> Print
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No students found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $students->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
