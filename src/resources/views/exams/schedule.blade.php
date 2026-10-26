@extends('me::master')
@section('title', sm_t('Exam Schedule'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="resetScheduleForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Schedule') }}
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-4">
                <select name="exam_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Exams') }}</option>
                    @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <a href="{{ route('exams.schedule') }}" class="btn btn-sm btn-encodex-cancel">{{ sm_t('Reset') }}</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Exam') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Subject') }}</th>
                    <th>{{ sm_t('Date') }}</th>
                    <th>{{ sm_t('Time') }}</th>
                    <th>{{ sm_t('Room') }}</th>
                    <th>{{ sm_t('Full Mark') }}</th>
                    <th>{{ sm_t('Pass Mark') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $i => $schedule)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="small fw-semibold">{{ $schedule->exam->name ?? '—' }}</td>
                    <td class="small">{{ $schedule->class->name ?? '—' }}</td>
                    <td class="small">{{ $schedule->subject->name ?? '—' }}</td>
                    <td class="small">{{ $schedule->date ? sm_date($schedule->date) : '—' }}</td>
                    <td class="small">{{ sm_time($schedule->start_time) }} – {{ sm_time($schedule->end_time) }}</td>
                    <td class="small">{{ $schedule->room ?? '—' }}</td>
                    <td class="text-center">{{ $schedule->full_mark }}</td>
                    <td class="text-center">{{ $schedule->pass_mark }}</td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editSchedule({{ $schedule->id }}, {{ json_encode($schedule) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('exams.schedule.destroy', $schedule->id) }}" class="d-inline" onsubmit="return confirm('Delete this schedule entry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">{{ sm_t('No schedules found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $schedules->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title" id="scheduleModalTitle">{{ sm_t('Add Schedule') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="scheduleForm" method="POST" action="{{ route('exams.schedule.store') }}">
                @csrf
                <span id="scheduleMethodField"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Exam') }} <span class="text-danger">*</span></label>
                            <select name="exam_id" id="schedExamId" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Exam') }}</option>
                                @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Class') }} <span class="text-danger">*</span></label>
                            <select name="class_id" id="schedClassId" class="form-control form-control-sm" required onchange="loadSubjects(this.value, 'schedSubjectId', null)">
                                <option value="">{{ sm_t('Select Class') }}</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Subject') }} <span class="text-danger">*</span></label>
                            <select name="subject_id" id="schedSubjectId" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select Class First') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="schedDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Room') }}</label>
                            <input type="text" name="room" id="schedRoom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Start Time') }} <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="schedStartTime" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('End Time') }} <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="schedEndTime" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Full Mark') }} <span class="text-danger">*</span></label>
                            <input type="number" name="full_mark" id="schedFullMark" class="form-control form-control-sm" min="0" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Pass Mark') }} <span class="text-danger">*</span></label>
                            <input type="number" name="pass_mark" id="schedPassMark" class="form-control form-control-sm" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function loadSubjects(classId, selectId, selectedId) {
    const sel = document.getElementById(selectId);
    sel.innerHTML = '<option>{{ sm_t('Loading...') }}</option>';
    if (!classId) { sel.innerHTML = '<option value="">{{ sm_t('Select Class First') }}</option>'; return; }
    const res  = await fetch('{{ url('subjects-list') }}/' + classId);
    const data = await res.json();
    sel.innerHTML = '<option value="">{{ sm_t('Select Subject') }}</option>';
    data.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id; o.textContent = s.name;
        if (selectedId && s.id == selectedId) o.selected = true;
        sel.appendChild(o);
    });
}

function resetScheduleForm() {
    document.getElementById('scheduleModalTitle').textContent = 'Add Schedule';
    document.getElementById('scheduleForm').action = '{{ route('exams.schedule.store') }}';
    document.getElementById('scheduleMethodField').innerHTML = '';
    ['schedExamId','schedClassId','schedDate','schedRoom','schedStartTime','schedEndTime','schedFullMark','schedPassMark']
        .forEach(id => document.getElementById(id).value = '');
    document.getElementById('schedSubjectId').innerHTML = '<option value="">{{ sm_t('Select Class First') }}</option>';
}

function editSchedule(id, s) {
    document.getElementById('scheduleModalTitle').textContent = 'Edit Schedule';
    document.getElementById('scheduleForm').action = '/exams/schedule/' + id;
    document.getElementById('scheduleMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('schedExamId').value    = s.exam_id    ?? '';
    document.getElementById('schedDate').value      = s.date       ? s.date.substring(0,10) : '';
    document.getElementById('schedRoom').value      = s.room       ?? '';
    document.getElementById('schedStartTime').value = s.start_time ?? '';
    document.getElementById('schedEndTime').value   = s.end_time   ?? '';
    document.getElementById('schedFullMark').value  = s.full_mark  ?? '';
    document.getElementById('schedPassMark').value  = s.pass_mark  ?? '';
    // Load class then select subject
    const classSel = document.getElementById('schedClassId');
    classSel.value = s.class_id ?? '';
    loadSubjects(s.class_id, 'schedSubjectId', s.subject_id);
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}
</script>
@endsection
