@extends('me::master')
@section('title', sm_t('Marks Entry'))
@push('buttons')
    @if($students->isNotEmpty())
    <a href="{{ route('exams.marks.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> {{ sm_t('Print') }}
    </a>
    @endif
@endpush

@section('content')


<div class="card shadow">
    <div class="card-body py-2 border-bottom no-print">
        <form method="GET" class="glass-search-form row g-2 align-items-end" id="filterForm">
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">{{ sm_t('Exam') }} *</label>
                <select name="exam_id" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Exam') }}</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">{{ sm_t('Class') }} *</label>
                <select name="class_id" class="form-control form-control-sm" id="classSelect" required>
                    <option value="">{{ sm_t('Select Class') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label form-label-sm mb-1">{{ sm_t('Subject') }} *</label>
                <select name="subject_id" class="form-control form-control-sm" id="subjectSelect" required>
                    <option value="">{{ sm_t('Select Subject') }}</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md">
                <label class="form-label form-label-sm mb-1">&nbsp;</label>
                <button type="submit" class="btn btn-sm btn-encodex-search d-block w-100">
                    <i class="fas fa-search"></i> {{ sm_t('Load Students') }}
                </button>
            </div>
        </form>
    </div>

    @if(!request('exam_id') || !request('class_id') || !request('subject_id'))
    <div class="card-body">
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-1"></i> {{ sm_t('Select exam, class, and subject to start mark entry.') }}
        </div>
    </div>
    @elseif($students->isEmpty())
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ sm_t('No active students found for the selected class.') }}
        </div>
    </div>
    @else

    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-pencil-alt me-1"></i>
            {{ $examObj?->name }} &mdash; {{ $subjectObj?->name }}
            <span class="badge bg-secondary-subtle text-secondary ms-2">{{ $students->count() }} students</span>
            @if($schedule)
            <span class="badge bg-light-subtle text-dark border ms-1">Full: {{ $schedule->full_mark }}</span>
            <span class="badge bg-light-subtle text-dark border ms-1">Pass: {{ $schedule->pass_mark }}</span>
            @endif
        </h6>
        <div class="no-print">
            <button type="button" class="btn btn-sm btn-encodex-deactive" onclick="markAllAbsent()">
                <i class="fas fa-user-slash"></i> {{ sm_t('Mark All Absent') }}
            </button>
            <button type="button" class="btn btn-sm btn-encodex-active ms-1" onclick="clearAllAbsent()">
                <i class="fas fa-user-check"></i> {{ sm_t('Clear Absent') }}
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('exams.marks.store') }}" id="marksForm">
        @csrf
        <input type="hidden" name="exam_id"    value="{{ request('exam_id') }}">
        <input type="hidden" name="class_id"   value="{{ request('class_id') }}">
        <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-encodex mb-0">
                @php $multiSection = $students->pluck('section_id')->unique()->count() > 1; @endphp
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th class="text-start">{{ sm_t('Student Name') }}</th>
                        <th>{{ sm_t('Roll') }}</th>
                        @if($multiSection)<th>{{ sm_t('Section') }}</th>@endif
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
                    <tr id="row-{{ $student->id }}" class="{{ ($mark && $mark->is_absent) ? 'table-secondary' : '' }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td class="text-center">{{ $student->roll_number ?? '—' }}</td>
                        @if($multiSection)<td class="text-center">{{ $student->section?->name ?? '—' }}</td>@endif

                        <input type="hidden" name="student_ids[]" value="{{ $student->id }}">

                        <td style="min-width:80px">
                            <input type="number" name="written[{{ $student->id }}]"
                                class="form-control form-control-sm text-center mark-input"
                                data-student="{{ $student->id }}"
                                value="{{ $mark?->written ?? '' }}"
                                min="0" step="0.5" placeholder="0">
                        </td>
                        <td style="min-width:80px">
                            <input type="number" name="mcq[{{ $student->id }}]"
                                class="form-control form-control-sm text-center mark-input"
                                data-student="{{ $student->id }}"
                                value="{{ $mark?->mcq ?? '' }}"
                                min="0" step="0.5" placeholder="0">
                        </td>
                        <td style="min-width:80px">
                            <input type="number" name="practical[{{ $student->id }}]"
                                class="form-control form-control-sm text-center mark-input"
                                data-student="{{ $student->id }}"
                                value="{{ $mark?->practical ?? '' }}"
                                min="0" step="0.5" placeholder="0">
                        </td>
                        <td style="min-width:80px">
                            <input type="number" name="ca[{{ $student->id }}]"
                                class="form-control form-control-sm text-center mark-input"
                                data-student="{{ $student->id }}"
                                value="{{ $mark?->ca ?? '' }}"
                                min="0" step="0.5" placeholder="0">
                        </td>
                        <td class="text-center fw-bold" id="total-{{ $student->id }}">
                            {{ $mark?->total_mark ?? '—' }}
                        </td>
                        <td class="text-center" id="grade-{{ $student->id }}">
                            @if($mark?->grade_name)
                                <span class="badge bg-{{ $bc = $mark->grade_name === 'F' ? 'danger' : 'success' }}-subtle text-{{ $bc }}">{{ $mark->grade_name }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="is_absent[{{ $student->id }}]"
                                class="absent-check form-check-input"
                                data-student="{{ $student->id }}"
                                value="1"
                                onchange="toggleAbsent(this)"
                                {{ ($mark && $mark->is_absent) ? 'checked' : '' }}>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center no-print">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                {{ sm_t('Totals are calculated automatically. Absent students will be saved with 0 marks.') }}
            </small>
            <button type="submit" class="btn btn-sm btn-encodex-save">
                <i class="fas fa-save me-1"></i> {{ sm_t('Save All Marks') }}
            </button>
        </div>
    </form>
    @endif
</div>

<script>
// Load subjects dynamically when class changes
document.getElementById('classSelect')?.addEventListener('change', function () {
    const classId  = this.value;
    const subSel   = document.getElementById('subjectSelect');
    subSel.innerHTML = '<option value="">{{ sm_t('Loading...') }}</option>';
    if (!classId) { subSel.innerHTML = '<option value="">{{ sm_t('Select Subject') }}</option>'; return; }

    fetch('{{ url('subjects/class') }}/' + classId)
        .then(r => r.json())
        .then(data => {
            subSel.innerHTML = '<option value="">{{ sm_t('Select Subject') }}</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id; opt.textContent = s.name;
                subSel.appendChild(opt);
            });
        })
        .catch(() => subSel.innerHTML = '<option value="">{{ sm_t('Select Subject') }}</option>');
});

// Auto-calculate total on input change
document.querySelectorAll('.mark-input').forEach(input => {
    input.addEventListener('input', function () {
        const sid     = this.dataset.student;
        const written = parseFloat(document.querySelector(`input[name="written[${sid}]"]`)?.value) || 0;
        const mcq     = parseFloat(document.querySelector(`input[name="mcq[${sid}]"]`)?.value)     || 0;
        const prac    = parseFloat(document.querySelector(`input[name="practical[${sid}]"]`)?.value)|| 0;
        const ca      = parseFloat(document.querySelector(`input[name="ca[${sid}]"]`)?.value)      || 0;
        const total   = written + mcq + prac + ca;
        const el = document.getElementById('total-' + sid);
        if (el) el.textContent = total > 0 ? total.toFixed(1).replace('.0','') : '—';
    });
});

function toggleAbsent(checkbox) {
    const sid = checkbox.dataset.student;
    const row = document.getElementById('row-' + sid);
    if (checkbox.checked) {
        row?.querySelectorAll('.mark-input').forEach(i => { i.value = ''; i.disabled = true; });
        row.classList.add('table-secondary');
        const el = document.getElementById('total-' + sid);
        if (el) el.textContent = 'AB';
    } else {
        row?.querySelectorAll('.mark-input').forEach(i => i.disabled = false);
        row.classList.remove('table-secondary');
        const el = document.getElementById('total-' + sid);
        if (el) el.textContent = '—';
    }
}

function markAllAbsent() {
    document.querySelectorAll('.absent-check').forEach(cb => {
        if (!cb.checked) { cb.checked = true; toggleAbsent(cb); }
    });
}
function clearAllAbsent() {
    document.querySelectorAll('.absent-check').forEach(cb => {
        if (cb.checked) { cb.checked = false; toggleAbsent(cb); }
    });
}

// Initialize disabled state for already-absent students
document.querySelectorAll('.absent-check:checked').forEach(cb => {
    const sid = cb.dataset.student;
    document.getElementById('row-' + sid)?.querySelectorAll('.mark-input')
        .forEach(i => i.disabled = true);
});
</script>
@endsection
