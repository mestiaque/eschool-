@extends('me::master')
@section('title', sm_t('Student Attendance'))
@push('buttons')
    <a href="{{ route('students.attendance.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print no-print">
        <i class="fas fa-print"></i> {{ sm_t('Print') }}
    </a>
@endpush

@section('content')

<style>
@media print { .no-print { display: none !important; } }

.att-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.att-table {
    border-collapse: collapse;
    min-width: 100%;
    font-size: 0.82rem;
}
.att-table th, .att-table td {
    border: 1px solid #dee2e6;
    padding: 0;
    text-align: center;
    white-space: nowrap;
}
.att-table th {
    background: #212529;
    color: #fff;
    padding: 4px 6px;
    position: sticky;
    top: 0;
    z-index: 2;
}
.att-table .col-student {
    text-align: left;
    padding: 4px 8px;
    min-width: 160px;
    position: sticky;
    left: 0;
    background: #fff;
    z-index: 1;
}
.att-table th.col-student {
    left: 0;
    z-index: 3;
    background: #212529;
}
.att-table th.col-roll {
    position: sticky;
    left: 0;
    z-index: 3;
    background: #212529;
}
.att-cell {
    width: 32px;
    height: 32px;
    cursor: pointer;
    user-select: none;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    font-weight: 700;
    font-size: 0.78rem;
    border-radius: 3px;
}
.att-cell.present  { color: #198754; background: #d1e7dd; }
.att-cell.absent   { color: #dc3545; background: #f8d7da; }
.att-cell.late     { color: #856404; background: #fff3cd; }
.att-cell.excused  { color: #0d6efd; background: #cfe2ff; }
.att-cell.empty    { color: #aaa; }
.att-cell.weekend  { background: #e9ecef; color: #6c757d; cursor: not-allowed; }
.att-cell.locked   { cursor: not-allowed; outline: 1px dashed #adb5bd; outline-offset: -2px; }
.att-cell.holiday  { background: #fff0f0; color: #dc3545; cursor: not-allowed; }
.day-col { min-width: 34px; max-width: 34px; }
.day-name-row th { font-size: 0.68rem; font-weight: 400; color: #adb5bd; padding: 1px 0; background: #2c3034; }
.day-name-row th.weekend-head { color: #ffc107; }
.day-name-row th.holiday-head { color: #f88; }
.summary-col { min-width: 30px; font-weight: 700; }
.att-table tbody tr:hover .col-student { background: #f8f9fa; }
.att-table tbody tr td.col-student { font-weight: 500; }
td.day-col { padding: 2px; }
</style>

@php
    $months = [
        1=>'January',2=>'February',3=>'March',4=>'April',
        5=>'May',6=>'June',7=>'July',8=>'August',
        9=>'September',10=>'October',11=>'November',12=>'December'
    ];
    $dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $currentYear  = date('Y');
    $yearRange    = range($currentYear - 2, $currentYear + 1);
@endphp

<div class="card shadow">
    <div class="card-body py-2 border-bottom no-print">
        <form method="GET" id="filterForm" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <select name="class_id" class="form-control form-control-sm" id="classSelect" required>
                    <option value="">{{ sm_t('Select Class') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="section_id" class="form-control form-control-sm" id="sectionSelect">
                    <option value="">{{ sm_t('All Sections') }}</option>
                    @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-control form-control-sm">
                    @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="year" class="form-control form-control-sm">
                    @foreach($yearRange as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md">
                <button type="submit" class="btn btn-sm btn-encodex-search">
                    <i class="fas fa-search"></i> {{ sm_t('Load Sheet') }}
                </button>
                <a href="{{ route('students.attendance') }}" class="btn btn-sm btn-encodex-clear">
                    <i class="fas fa-eraser"></i> {{ sm_t('Reset') }}
                </a>
            </div>
        </form>
    </div>

    @if(!request('class_id'))
    <div class="card-body no-print">
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-1"></i> {{ sm_t('Please select a class and month to load the attendance sheet.') }}
        </div>
    </div>
    @elseif($students->isEmpty())
    <div class="card-body">
        <div class="alert alert-warning mb-0">{{ sm_t('No students found for the selected class/section.') }}</div>
    </div>
    @else
    <div class="card-body p-1">
        {{-- Legend + Title --}}
        <div class="d-flex justify-content-between align-items-center mb-2 no-print">
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <span class="att-cell present" style="width:auto;padding:2px 8px;display:inline-flex;">P &nbsp; Present</span>
                <span class="att-cell absent"  style="width:auto;padding:2px 8px;display:inline-flex;">A &nbsp; Absent</span>
                <span class="att-cell late"    style="width:auto;padding:2px 8px;display:inline-flex;">L &nbsp; Late</span>
                <span class="att-cell excused" style="width:auto;padding:2px 8px;display:inline-flex;">E &nbsp; Excused</span>
                <span class="att-cell weekend" style="width:auto;padding:2px 8px;display:inline-flex;">— &nbsp; Weekend</span>
                <span class="att-cell holiday" style="width:auto;padding:2px 8px;display:inline-flex;">H &nbsp; Holiday</span>
            </div>
            <small class="text-muted">Click = toggle P/A &nbsp;|&nbsp; Double-click = edit</small>
        </div>
        <div class="att-table-wrap">
@php $showSection = !request('section_id'); @endphp
            <table class="att-table">
                <thead>
                    {{-- Date number row --}}
                    <tr>
                        <th class="col-student">#</th>
                        <th class="col-student" style="min-width:160px">{{ sm_t('Student Name') }}</th>
                        <th style="min-width:40px">{{ sm_t('Roll') }}</th>
                        @if($showSection)<th style="min-width:40px">{{ sm_t('Section') }}</th>@endif
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayOfWeek = (int) date('w', mktime(0,0,0,$month,$d,$year));
                                $exception  = $holidays[$d] ?? null;   // holiday or extra school day
                                $isExtraDay = $exception && ($exception->type ?? 'holiday') === 'school_day';
                                $isWeekend  = in_array($dayOfWeek, $weekendDays) && !$isExtraDay;
                                $isHoliday  = $exception && !$isExtraDay;
                                $dateStr   = $year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '-' . str_pad($d,2,'0',STR_PAD_LEFT);
                            @endphp
                            <th class="day-col {{ $isWeekend ? 'weekend-head' : '' }} {{ $isHoliday ? 'holiday-head' : '' }}"
                                title="{{ $isHoliday ? $exception->title : ($isWeekend ? sm_t('Weekly holiday') : ($isExtraDay ? $exception->title : $dateStr)) }}">
                                {{ $d }}
                            </th>
                        @endfor
                        <th class="summary-col" style="background:#198754">P</th>
                        <th class="summary-col" style="background:#dc3545">A</th>
                        <th class="summary-col" style="background:#856404">L</th>
                    </tr>
                    {{-- Day name row --}}
                    <tr class="day-name-row">
                        <th></th><th></th><th></th>@if($showSection)<th></th>@endif
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayOfWeek = (int) date('w', mktime(0,0,0,$month,$d,$year));
                                $exception  = $holidays[$d] ?? null;   // holiday or extra school day
                                $isExtraDay = $exception && ($exception->type ?? 'holiday') === 'school_day';
                                $isWeekend  = in_array($dayOfWeek, $weekendDays) && !$isExtraDay;
                                $isHoliday  = $exception && !$isExtraDay;
                            @endphp
                            <th class="{{ $isWeekend ? 'weekend-head' : '' }} {{ $isHoliday ? 'holiday-head' : '' }}">
                                {{ $dayNames[$dayOfWeek] }}
                            </th>
                        @endfor
                        <th></th><th></th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $idx => $student)
                    @php
                        $studentAtts = $attendanceMap[$student->id] ?? [];
                        $pCount = 0; $aCount = 0; $lCount = 0;
                        foreach($studentAtts as $a) {
                            if($a['status'] === 'present') $pCount++;
                            elseif($a['status'] === 'absent')  $aCount++;
                            elseif($a['status'] === 'late')    $lCount++;
                        }
                    @endphp
                    <tr>
                        <td class="col-student" style="min-width:30px;max-width:30px;text-align:center;">{{ $idx + 1 }}</td>
                        <td class="col-student">{{ $student->first_name }} {{ $student->last_name }}</td>
                        <td>{{ $student->roll_number ?? '-' }}</td>
                        @if($showSection)<td>{{ $student->section?->name ?? '—' }}</td>@endif

                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayOfWeek = (int) date('w', mktime(0,0,0,$month,$d,$year));
                                $exception  = $holidays[$d] ?? null;   // holiday or extra school day
                                $isExtraDay = $exception && ($exception->type ?? 'holiday') === 'school_day';
                                $isWeekend  = in_array($dayOfWeek, $weekendDays) && !$isExtraDay;
                                $isHoliday  = $exception && !$isExtraDay;
                                $dateStr   = $year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '-' . str_pad($d,2,'0',STR_PAD_LEFT);
                                $att       = $studentAtts[$d] ?? null;
                                $status    = $att['status'] ?? null;
                                $note      = $att['note'] ?? '';
                                $statusLabels = ['present'=>'P','absent'=>'A','late'=>'L','excused'=>'E'];
                                $label     = $status ? $statusLabels[$status] : '';
                            @endphp
                            <td class="day-col">
                                @if($isWeekend || $isHoliday)
                                    {{-- Closed day: read-only; an entry recorded here anyway still shows (and is counted). --}}
                                    <div class="att-cell {{ $status ? $status . ' locked' : ($isHoliday ? 'holiday' : 'weekend') }}"
                                         title="{{ $isHoliday ? $exception->title : sm_t('Weekly holiday') }}{{ $status ? ' — ' . sm_t('recorded on a closed day') : '' }}">
                                        {{ $status ? $label : ($isHoliday ? 'H' : '—') }}
                                    </div>
                                @else
                                    <div class="att-cell {{ $status ?? 'empty' }}"
                                         data-student="{{ $student->id }}"
                                         data-date="{{ $dateStr }}"
                                         data-status="{{ $status ?? '' }}"
                                         data-note="{{ $note }}"
                                         title="{{ $dateStr }}{{ $note ? ' | '.$note : '' }}"
                                         onclick="toggleAttendance(this)"
                                         ondblclick="openEditModal(this)">
                                        {{ $label }}
                                    </div>
                                @endif
                            </td>
                        @endfor

                        <td class="summary-col" style="color:#198754">{{ $pCount }}</td>
                        <td class="summary-col" style="color:#dc3545">{{ $aCount }}</td>
                        <td class="summary-col" style="color:#856404">{{ $lCount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@if(request('class_id') && $students->isNotEmpty())
{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="editModalTitle">{{ sm_t('Edit Attendance') }}</h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">
                <div class="mb-2">
                    <label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                    <select id="editStatus" class="form-control form-control-sm">
                        <option value="present">{{ sm_t('Present (P)') }}</option>
                        <option value="absent">{{ sm_t('Absent (A)') }}</option>
                        <option value="late">{{ sm_t('Late (L)') }}</option>
                        <option value="excused">{{ sm_t('Excused (E)') }}</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm">{{ sm_t('Note') }}</label>
                    <input type="text" id="editNote" class="form-control form-control-sm" placeholder="{{ sm_t('Optional note') }}">
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                <button type="button" class="btn btn-sm btn-encodex-save" onclick="saveEdit()">{{ sm_t('Save') }}</button>
            </div>
        </div>
    </div>
</div>

@endif

<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const CELL_URL   = '{{ route("students.attendance.cell") }}';

let _editCell = null;

function toggleAttendance(cell) {
    const current = cell.dataset.status;
    const next    = current === 'present' ? 'absent' : 'present';
    saveAttendance(cell, next, cell.dataset.note || '');
}

function openEditModal(cell) {
    _editCell = cell;
    document.getElementById('editModalTitle').textContent =
        'Edit — ' + cell.dataset.date;
    document.getElementById('editStatus').value = cell.dataset.status || 'present';
    document.getElementById('editNote').value   = cell.dataset.note   || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function saveEdit() {
    if (!_editCell) return;
    const status = document.getElementById('editStatus').value;
    const note   = document.getElementById('editNote').value;
    saveAttendance(_editCell, status, note, function() {
        bootstrap.Modal.getInstance(document.getElementById('editModal'))?.hide();
    });
}

function saveAttendance(cell, status, note, callback) {
    const labels = { present:'P', absent:'A', late:'L', excused:'E' };
    const classes = { present:'present', absent:'absent', late:'late', excused:'excused' };

    // Optimistic UI update (rolled back if the server refuses)
    const before = { text: cell.textContent, cls: cell.className, status: cell.dataset.status, note: cell.dataset.note };
    const rollback = (msg) => {
        cell.textContent = before.text; cell.className = before.cls;
        cell.dataset.status = before.status; cell.dataset.note = before.note;
        updateRowSummary(cell);
        if (window.toastr) toastr.error(msg); else alert(msg);
    };
    cell.textContent    = labels[status];
    cell.className      = 'att-cell ' + classes[status];
    cell.dataset.status = status;
    cell.dataset.note   = note;

    fetch(CELL_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            student_id: cell.dataset.student,
            date:       cell.dataset.date,
            status:     status,
            note:       note,
        })
    })
    .then(r => r.json().catch(() => ({})).then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (!ok || !data.success) {
            rollback(data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Attendance was not saved.'));
            return;
        }
        updateRowSummary(cell);
        if (callback) callback();
    })
    .catch(() => rollback('Attendance was not saved (network error).'));
}

function updateRowSummary(cell) {
    const row = cell.closest('tr');
    if (!row) return;
    let p = 0, a = 0, l = 0;
    row.querySelectorAll('.att-cell[data-status]').forEach(c => {
        const s = c.dataset.status;
        if (s === 'present') p++;
        else if (s === 'absent')  a++;
        else if (s === 'late')    l++;
    });
    const cols = row.querySelectorAll('td.summary-col');
    if (cols[0]) cols[0].textContent = p;
    if (cols[1]) cols[1].textContent = a;
    if (cols[2]) cols[2].textContent = l;
}

// Load sections dynamically on class change
document.getElementById('classSelect')?.addEventListener('change', function() {
    const classId = this.value;
    const secSel  = document.getElementById('sectionSelect');
    secSel.innerHTML = '<option value="">{{ sm_t('All Sections') }}</option>';
    if (!classId) return;
    fetch('{{ url('sections') }}/' + classId)
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id; opt.textContent = s.name;
                secSel.appendChild(opt);
            });
        });
});
</script>
@endsection
