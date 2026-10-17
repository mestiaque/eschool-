@extends('me::master')
@section('title', 'Enter Marks — ' . $test->title)

@push('buttons')
<a href="{{ route('my.class-tests') }}" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endpush

@section('content')

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Class</small>
                        <strong>{{ $test->classe->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Subject</small>
                        <strong>{{ $test->subject->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Test Date</small>
                        <strong>{{ sm_date($test->test_date) }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Total Marks</small>
                        <strong>{{ $test->total_marks }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Pass Marks</small>
                        <strong>{{ $test->pass_marks }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body py-2 d-flex align-items-center gap-3">
                @php
                    $submitted  = $results->whereNotIn('status', ['absent'])->count();
                    $passCount  = $results->where('status', 'pass')->count();
                    $failCount  = $results->where('status', 'fail')->count();
                    $absentCount = $results->where('status', 'absent')->count();
                @endphp
                <div class="text-center">
                    <div class="fs-5 fw-bold text-success">{{ $passCount }}</div>
                    <small class="text-muted">Pass</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-danger">{{ $failCount }}</div>
                    <small class="text-muted">Fail</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-secondary">{{ $absentCount }}</div>
                    <small class="text-muted">Absent</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-primary">{{ $students->count() }}</div>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('my.class-tests.marks.store', $test->id) }}">
    @csrf
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-encodex table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>{{ sm_t('Student') }}</th>
                        <th width="80">{{ sm_t('Roll No') }}</th>
                        <th width="120">{{ sm_t('Status') }}</th>
                        <th width="130">{{ sm_t('Obtained Marks') }}<br><small class="text-muted fw-normal">out of {{ $test->total_marks }}</small></th>
                        <th width="60">{{ sm_t('Grade') }}</th>
                        <th>{{ sm_t('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $i => $student)
                    @php $result = $results[$student->id] ?? null; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->roll_no ?? '-' }}</td>
                        <td>
                            <select name="status[{{ $student->id }}]" class="form-select form-select-sm status-select"
                                    data-student="{{ $student->id }}">
                                <option value="absent" {{ ($result?->status ?? 'absent') === 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="present" {{ in_array($result?->status, ['pass', 'fail', 'present']) ? 'selected' : '' }}>Present</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="marks[{{ $student->id }}]"
                                   class="form-control form-control-sm marks-input"
                                   data-student="{{ $student->id }}"
                                   data-total="{{ $test->total_marks }}"
                                   data-pass="{{ $test->pass_marks }}"
                                   value="{{ $result?->obtained_marks }}"
                                   min="0" max="{{ $test->total_marks }}" step="0.01"
                                   {{ ($result === null || $result->status === 'absent') ? 'disabled' : '' }}
                                   placeholder="0">
                        </td>
                        <td>
                            <span class="grade-badge badge
                                @if($result?->grade === 'A+') bg-success
                                @elseif(in_array($result?->grade, ['A','B'])) bg-primary
                                @elseif($result?->grade === 'C') bg-info
                                @elseif($result?->grade === 'D') bg-warning text-dark
                                @elseif($result?->grade === 'F') bg-danger
                                @else bg-secondary
                                @endif"
                                id="grade-{{ $student->id }}">
                                {{ $result?->grade ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <input type="text" name="remarks[{{ $student->id }}]"
                                   class="form-control form-control-sm"
                                   value="{{ $result?->remarks }}"
                                   placeholder="Optional">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No active students found for this class</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->isNotEmpty())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-sm btn-encodex-active me-2" id="markAllPresent">{{ sm_t('Mark All Present') }}</button>
                <button type="button" class="btn btn-sm btn-encodex-cancel" id="markAllAbsent">{{ sm_t('Mark All Absent') }}</button>
            </div>
            <button type="submit" class="btn btn-sm btn-encodex-save">
                <i class="fas fa-save"></i> Save Marks
            </button>
        </div>
        @endif
    </div>
</form>

<script>
const gradeLabels = ['A+','A','B','C','D','F'];

function calcGrade(marks, total) {
    if (total <= 0) return '-';
    const pct = (marks / total) * 100;
    if (pct >= 80) return 'A+';
    if (pct >= 70) return 'A';
    if (pct >= 60) return 'B';
    if (pct >= 50) return 'C';
    if (pct >= 40) return 'D';
    return 'F';
}

function gradeClass(g) {
    return {
        'A+': 'bg-success-subtle text-success', 'A': 'bg-primary-subtle text-primary', 'B': 'bg-primary-subtle text-primary',
        'C': 'bg-info-subtle text-info', 'D': 'bg-warning-subtle text-warning', 'F': 'bg-danger-subtle text-danger'
    }[g] || 'bg-secondary-subtle text-secondary';
}

document.querySelectorAll('.marks-input').forEach(input => {
    input.addEventListener('input', function () {
        const sid   = this.dataset.student;
        const total = parseFloat(this.dataset.total) || 0;
        const val   = parseFloat(this.value);
        const badge = document.getElementById('grade-' + sid);
        if (isNaN(val) || this.value === '') {
            badge.textContent = '-';
            badge.className = 'grade-badge badge bg-secondary-subtle text-secondary';
        } else {
            const g = calcGrade(val, total);
            badge.textContent = g;
            badge.className = 'grade-badge badge ' + gradeClass(g);
        }
    });
});

document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', function () {
        const sid   = this.dataset.student;
        const input = document.querySelector(`input[data-student="${sid}"].marks-input`);
        if (this.value === 'absent') {
            input.disabled = true;
            input.value    = '';
            const badge = document.getElementById('grade-' + sid);
            badge.textContent = '-';
            badge.className   = 'grade-badge badge bg-secondary-subtle text-secondary';
        } else {
            input.disabled = false;
            input.focus();
        }
    });
});

document.getElementById('markAllPresent')?.addEventListener('click', function () {
    document.querySelectorAll('.status-select').forEach(s => {
        s.value = 'present';
        s.dispatchEvent(new Event('change'));
    });
});

document.getElementById('markAllAbsent')?.addEventListener('click', function () {
    document.querySelectorAll('.status-select').forEach(s => {
        s.value = 'absent';
        s.dispatchEvent(new Event('change'));
    });
});
</script>
@endsection
