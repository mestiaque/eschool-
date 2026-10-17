@extends('me::master')
@section('title', sm_t('Student Promotion'))

@section('content')


<div class="row g-3">

    {{-- Left: Filter Panel --}}
    <div class="col-md-4 col-xl-3">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Load Students</h6>
            </div>
            <div class="card-body">
                <form method="GET" id="loadForm" class="glass-search-form">
                    <div class="mb-3">
                        <label class="form-label">{{ sm_t('From Class') }} <span class="text-danger">*</span></label>
                        <select name="from_class" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Class') }}</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('from_class') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ sm_t('From Academic Year') }} <span class="text-danger">*</span></label>
                        <select name="from_year" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Year') }}</option>
                            @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('from_year') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-encodex-search w-100">
                        <i class="fas fa-users me-1"></i> Load Students
                    </button>
                </form>
            </div>
        </div>

        @if(request('from_class') && request('from_year') && $students->isNotEmpty())
        <div class="card shadow mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-arrow-right me-1"></i> Promote To</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ sm_t('To Class') }} <span class="text-danger">*</span></label>
                    <select id="toClassGlobal" class="form-control form-control-sm" onchange="applyGlobalClass(this.value)">
                        <option value="">{{ sm_t('Select Class') }}</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Applies to all selected students</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ sm_t('To Academic Year') }} <span class="text-danger">*</span></label>
                    <select id="toYearGlobal" class="form-control form-control-sm" onchange="applyGlobalYear(this.value)">
                        <option value="">{{ sm_t('Select Year') }}</option>
                        @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Mark all as:</small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-encodex-active" onclick="markAll('promoted')">
                            <i class="fas fa-check"></i> Promoted
                        </button>
                        <button type="button" class="btn btn-encodex-deactive" onclick="markAll('detained')">
                            <i class="fas fa-times"></i> Detained
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Select:</small>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-encodex-clear" onclick="selectAll(true)">{{ sm_t('All') }}</button>
                        <button type="button" class="btn btn-encodex-clear" onclick="selectAll(false)">{{ sm_t('None') }}</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Student Table --}}
    <div class="col-md-8 col-xl-9">
        @if(!request('from_class') || !request('from_year'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i> Select class and academic year to load students.
            </div>
        @elseif($students->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                No students found for the selected class and academic year.
            </div>
        @else
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-1"></i>
                    {{ $students->count() }} Student(s) — Ready for Promotion
                </h6>
                <span class="badge bg-secondary-subtle text-secondary">
                    {{ $classes->firstWhere('id', request('from_class'))?->name }}
                    &rarr; {{ $academicYears->firstWhere('id', request('from_year'))?->name }}
                </span>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('students.promotion.store') }}" id="promotionForm">
                    @csrf
                    <input type="hidden" name="from_class" value="{{ request('from_class') }}">
                    <input type="hidden" name="from_year"  value="{{ request('from_year') }}">
                    <input type="hidden" name="to_class"   id="toClassHidden"  value="">
                    <input type="hidden" name="to_year"    id="toYearHidden"   value="">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th style="width:40px">
                                        <input type="checkbox" id="checkAll" checked
                                            onchange="document.querySelectorAll('.student-check').forEach(c=>c.checked=this.checked)">
                                    </th>
                                    <th>#</th>
                                    <th class="text-start">{{ sm_t('Student Name') }}</th>
                                    <th>{{ sm_t('Roll No') }}</th>
                                    <th>{{ sm_t('New Roll No') }}</th>
                                    <th>{{ sm_t('To Section') }}</th>
                                    <th>{{ sm_t('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $i => $student)
                                <tr id="row-{{ $student->id }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="student_ids[]"
                                            value="{{ $student->id }}"
                                            class="student-check" checked>
                                    </td>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        @if($student->section)
                                        <small class="text-muted">{{ $student->section->name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $student->roll_number ?? '—' }}</td>
                                    <td>
                                        <input type="text"
                                            name="roll_numbers[{{ $student->id }}]"
                                            class="form-control form-control-sm new-roll text-center"
                                            value="{{ $student->roll_number }}"
                                            placeholder="New roll"
                                            style="min-width:80px">
                                    </td>
                                    <td>
                                        <select name="to_sections[{{ $student->id }}]"
                                            class="form-control form-control-sm to-section"
                                            style="min-width:110px">
                                            <option value="">{{ sm_t('Same Section') }}</option>
                                            @foreach($sections as $sec)
                                            <option value="{{ $sec->id }}"
                                                {{ $student->section_id == $sec->id ? 'selected' : '' }}>
                                                {{ $sec->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <select name="mark_status[{{ $student->id }}]"
                                            class="form-control form-control-sm mark-status"
                                            onchange="updateRowStyle(this)"
                                            style="min-width:110px">
                                            <option value="promoted" selected>✓ Promoted</option>
                                            <option value="detained">✗ Detained</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            "Detained" students will be recorded but <strong>not moved</strong> to the new class.
                        </small>
                        <button type="button" class="btn btn-sm btn-encodex-save" onclick="submitPromotion()">
                            <i class="fas fa-arrow-circle-up me-1"></i> Promote Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function applyGlobalClass(val) {
    document.getElementById('toClassHidden').value = val;
}
function applyGlobalYear(val) {
    document.getElementById('toYearHidden').value = val;
}
function markAll(status) {
    document.querySelectorAll('.mark-status').forEach(sel => {
        sel.value = status;
        updateRowStyle(sel);
    });
}
function selectAll(checked) {
    document.querySelectorAll('.student-check').forEach(c => c.checked = checked);
    document.getElementById('checkAll').checked = checked;
}
function updateRowStyle(sel) {
    const row = sel.closest('tr');
    if (!row) return;
    if (sel.value === 'detained') {
        row.style.opacity = '0.5';
        row.style.background = '#fff5f5';
    } else {
        row.style.opacity = '1';
        row.style.background = '';
    }
}
function submitPromotion() {
    const toClass = document.getElementById('toClassHidden').value;
    const toYear  = document.getElementById('toYearHidden').value;
    if (!toClass) { alert('Please select "To Class" from the left panel.'); return; }
    if (!toYear)  { alert('Please select "To Academic Year" from the left panel.'); return; }
    const checked = document.querySelectorAll('.student-check:checked');
    if (checked.length === 0) { alert('Please select at least one student.'); return; }
    if (!confirm('Promote ' + checked.length + ' student(s) to the new class?')) return;
    document.getElementById('promotionForm').submit();
}
</script>
@endsection
