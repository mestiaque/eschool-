@extends('me::master')
@section('title', 'Submissions — ' . $assignment->title)

@push('buttons')
<a href="{{ route('my.assignments') }}" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-arrow-left"></i> {{ sm_t('Back') }}
</a>
@endpush

@section('content')

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-md-3">
                        <small class="text-muted d-block">{{ sm_t('Class') }}</small>
                        <strong>{{ $assignment->classe->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">{{ sm_t('Subject') }}</small>
                        <strong>{{ $assignment->subject->name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">{{ sm_t('Due Date') }}</small>
                        <strong>{{ sm_date($assignment->due_date) }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">{{ sm_t('Total Marks') }}</small>
                        <strong>{{ $assignment->total_marks }}</strong>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">{{ sm_t('Status') }}</small>
                        @php $statusColors = ['active'=>'success','closed'=>'secondary','cancelled'=>'danger']; @endphp
                        <span class="badge bg-{{ $bc = $statusColors[$assignment->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($assignment->status)) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body py-2 d-flex align-items-center gap-3">
                @php
                    $submittedCount    = $submissions->where('status', 'submitted')->count();
                    $lateCount         = $submissions->where('status', 'late')->count();
                    $notSubmittedCount = $submissions->where('status', 'not_submitted')->count();
                @endphp
                <div class="text-center">
                    <div class="fs-5 fw-bold text-success">{{ $submittedCount }}</div>
                    <small class="text-muted">{{ sm_t('Submitted') }}</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-warning">{{ $lateCount }}</div>
                    <small class="text-muted">{{ sm_t('Late') }}</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-danger">{{ $notSubmittedCount }}</div>
                    <small class="text-muted">{{ sm_t('Not Submitted') }}</small>
                </div>
                <div class="text-center">
                    <div class="fs-5 fw-bold text-primary">{{ $students->count() }}</div>
                    <small class="text-muted">{{ sm_t('Total') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('my.assignments.submissions.store', $assignment->id) }}">
    @csrf
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-encodex table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>{{ sm_t('Student') }}</th>
                        <th width="80">{{ sm_t('Roll No') }}</th>
                        <th width="150">{{ sm_t('Status') }}</th>
                        <th width="130">{{ sm_t('Submitted On') }}</th>
                        <th width="130">{{ sm_t('Marks') }}<br><small class="text-muted fw-normal">out of {{ $assignment->total_marks }}</small></th>
                        <th width="60">{{ sm_t('Grade') }}</th>
                        <th>{{ sm_t('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $i => $student)
                    @php
                        $sub     = $submissions[$student->id] ?? null;
                        $curStatus = $sub?->status ?? 'not_submitted';
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->roll_no ?? '-' }}</td>
                        <td>
                            <select name="status[{{ $student->id }}]"
                                    class="form-select form-select-sm sub-status-select"
                                    data-student="{{ $student->id }}">
                                <option value="not_submitted" {{ $curStatus === 'not_submitted' ? 'selected' : '' }}>{{ sm_t('Not Submitted') }}</option>
                                <option value="submitted"     {{ $curStatus === 'submitted'     ? 'selected' : '' }}>{{ sm_t('Submitted') }}</option>
                                <option value="late"          {{ $curStatus === 'late'          ? 'selected' : '' }}>{{ sm_t('Late') }}</option>
                            </select>
                        </td>
                        <td>
                            <input type="date" name="submitted_at[{{ $student->id }}]"
                                   class="form-control form-control-sm sub-date"
                                   data-student="{{ $student->id }}"
                                   value="{{ $sub?->submitted_at?->format('Y-m-d') }}"
                                   {{ $curStatus === 'not_submitted' ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input type="number" name="marks[{{ $student->id }}]"
                                   class="form-control form-control-sm sub-marks"
                                   data-student="{{ $student->id }}"
                                   data-total="{{ $assignment->total_marks }}"
                                   value="{{ $sub?->obtained_marks }}"
                                   min="0" max="{{ $assignment->total_marks }}" step="0.01"
                                   {{ $curStatus === 'not_submitted' ? 'disabled' : '' }}
                                   placeholder="0">
                        </td>
                        <td>
                            <span class="sub-grade badge
                                @if($sub?->grade === 'A+') bg-success
                                @elseif(in_array($sub?->grade, ['A','B'])) bg-primary
                                @elseif($sub?->grade === 'C') bg-info
                                @elseif($sub?->grade === 'D') bg-warning text-dark
                                @elseif($sub?->grade === 'F') bg-danger
                                @else bg-secondary
                                @endif"
                                id="sub-grade-{{ $student->id }}">
                                {{ $sub?->grade ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <input type="text" name="remarks[{{ $student->id }}]"
                                   class="form-control form-control-sm"
                                   value="{{ $sub?->remarks }}"
                                   placeholder="{{ sm_t('Optional') }}">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-3">{{ sm_t('No active students found for this class') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->isNotEmpty())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-sm btn-encodex-active me-2" id="markAllSubmitted">{{ sm_t('Mark All Submitted') }}</button>
                <button type="button" class="btn btn-sm btn-encodex-cancel" id="markAllNotSubmitted">{{ sm_t('Mark All Not Submitted') }}</button>
            </div>
            <button type="submit" class="btn btn-sm btn-encodex-save">
                <i class="fas fa-save"></i> {{ sm_t('Save Submissions') }}
            </button>
        </div>
        @endif
    </div>
</form>

<script>
// Same scale the server uses (Examinations → Grades, or the Bangladesh default).
const GRADE_SCALE = @json(\ME\SchoolManagement\Services\GradeScale::rows());
const FAIL_GRADE  = @json(\ME\SchoolManagement\Services\GradeScale::fail()['name']);
const PASS_MARK   = null;

function calcGrade(marks, total) {
    if (total <= 0) return '-';
    if (PASS_MARK !== null && marks < PASS_MARK) return FAIL_GRADE;
    const pct = (marks / total) * 100;
    for (const g of GRADE_SCALE) { if (pct >= g.from) return g.name; }
    return FAIL_GRADE;
}

function gradeClass(g) {
    return {
        'A+': 'bg-success-subtle text-success', 'A': 'bg-primary-subtle text-primary', 'A-': 'bg-primary-subtle text-primary', 'B': 'bg-primary-subtle text-primary',
        'C': 'bg-info-subtle text-info', 'D': 'bg-warning-subtle text-warning', 'F': 'bg-danger-subtle text-danger'
    }[g] || 'bg-secondary-subtle text-secondary';
}

document.querySelectorAll('.sub-marks').forEach(input => {
    input.addEventListener('input', function () {
        const sid   = this.dataset.student;
        const total = parseFloat(this.dataset.total) || 0;
        const val   = parseFloat(this.value);
        const badge = document.getElementById('sub-grade-' + sid);
        if (isNaN(val) || this.value === '') {
            badge.textContent = '-';
            badge.className   = 'sub-grade badge bg-secondary-subtle text-secondary';
        } else {
            const g = calcGrade(val, total);
            badge.textContent = g;
            badge.className   = 'sub-grade badge ' + gradeClass(g);
        }
    });
});

document.querySelectorAll('.sub-status-select').forEach(sel => {
    sel.addEventListener('change', function () {
        const sid   = this.dataset.student;
        const marks = document.querySelector(`.sub-marks[data-student="${sid}"]`);
        const date  = document.querySelector(`.sub-date[data-student="${sid}"]`);
        const isNS  = this.value === 'not_submitted';
        marks.disabled = isNS;
        date.disabled  = isNS;
        if (isNS) {
            marks.value = '';
            date.value  = '';
            const badge = document.getElementById('sub-grade-' + sid);
            badge.textContent = '-';
            badge.className   = 'sub-grade badge bg-secondary-subtle text-secondary';
        } else if (!date.value) {
            date.value = new Date().toISOString().split('T')[0];
        }
    });
});

document.getElementById('markAllSubmitted')?.addEventListener('click', function () {
    document.querySelectorAll('.sub-status-select').forEach(s => {
        s.value = 'submitted';
        s.dispatchEvent(new Event('change'));
    });
});

document.getElementById('markAllNotSubmitted')?.addEventListener('click', function () {
    document.querySelectorAll('.sub-status-select').forEach(s => {
        s.value = 'not_submitted';
        s.dispatchEvent(new Event('change'));
    });
});
</script>
@endsection
