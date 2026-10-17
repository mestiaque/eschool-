@extends('me::master')
@section('title', 'Take Attendance - ' . $class->name . ($section ? ' / ' . $section->name : ''))

@push('buttons')
<a href="{{ route('my.classes') }}" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-arrow-left"></i> My Classes
</a>
@endpush

@section('content')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-0">{{ sm_t('Date') }}</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()" max="{{ date('Y-m-d') }}">
            </div>
        </form>
    </div>
    <div class="card-header">
        <h6 class="mb-0">{{ $class->name }}{{ $section ? ' — ' . $section->name : '' }} &nbsp;•&nbsp; {{ sm_date(\Carbon\Carbon::parse($date)) }}</h6>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('my.attendance.store', $class->id) }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="section_id" value="{{ $sectionId }}">
            <table class="table table-sm table-encodex mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ sm_t('Roll') }}</th>
                        <th>{{ sm_t('Student') }}</th>
                        <th class="text-center">{{ sm_t('Present') }}</th>
                        <th class="text-center">{{ sm_t('Absent') }}</th>
                        <th class="text-center">{{ sm_t('Late') }}</th>
                        <th class="text-center">{{ sm_t('Excused') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    @php $current = $attendance->get($student->id)?->status ?? 'present'; @endphp
                    <tr>
                        <td>{{ $student->roll_number ?? '—' }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        @foreach(['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'] as $value => $label)
                        <td class="text-center">
                            <input type="radio" name="status[{{ $student->id }}]" value="{{ $value }}" {{ $current == $value ? 'checked' : '' }}>
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No students found in this class/section.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($students->isNotEmpty())
            <div class="p-2 border-top">
                <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>Save Attendance</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
