@extends('me::master')
@section('title', sm_t('Teacher Attendance'))

@section('content')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('teachers.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <table class="table table-encodex table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ sm_t('Teacher Name') }}</th>
                        <th>{{ sm_t('ID') }}</th>
                        <th>{{ sm_t('In Time') }}</th>
                        <th>{{ sm_t('Out Time') }}</th>
                        <th>{{ sm_t('Present') }}</th>
                        <th>{{ sm_t('Absent') }}</th>
                        <th>{{ sm_t('Late') }}</th>
                        <th>{{ sm_t('Excused') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $i => $teacher)
                    @php $att = $teacher->attendances->first(); @endphp
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $teacher->full_name }}</td>
                        <td>{{ $teacher->teacher_id }}</td>
                        <input type="hidden" name="teacher_ids[]" value="{{ $teacher->id }}">
                        <td><input type="time" name="in_time[{{ $teacher->id }}]" class="form-control form-control-sm form-control-xs" value="{{ $att->in_time ?? '' }}"></td>
                        <td><input type="time" name="out_time[{{ $teacher->id }}]" class="form-control form-control-sm form-control-xs" value="{{ $att->out_time ?? '' }}"></td>
                        <td><input type="radio" name="status[{{ $teacher->id }}]" value="present" {{ (!$att || $att->status == 'present') ? 'checked' : '' }}></td>
                        <td><input type="radio" name="status[{{ $teacher->id }}]" value="absent" {{ ($att && $att->status == 'absent') ? 'checked' : '' }}></td>
                        <td><input type="radio" name="status[{{ $teacher->id }}]" value="late" {{ ($att && $att->status == 'late') ? 'checked' : '' }}></td>
                        <td><input type="radio" name="status[{{ $teacher->id }}]" value="excused" {{ ($att && $att->status == 'excused') ? 'checked' : '' }}></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-3">No teachers found</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if(count($teachers) > 0)
            <div class="p-2">
                <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save"></i> Save Attendance</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
