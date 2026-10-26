@extends('me::master')
@section('title', sm_t('Exams'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#examModal" onclick="resetExamForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Exam') }}
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Name') }}</th>
                    <th>{{ sm_t('Type') }}</th>
                    <th>{{ sm_t('Start Date') }}</th>
                    <th>{{ sm_t('End Date') }}</th>
                    <th>{{ sm_t('Published') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $i => $exam)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="fw-semibold">{{ $exam->name }}</td>
                    <td><span class="badge bg-info-subtle text-info">{{ $exam->examType->name ?? '—' }}</span></td>
                    <td class="small">{{ $exam->start_date ? sm_date($exam->start_date) : '—' }}</td>
                    <td class="small">{{ $exam->end_date ? sm_date($exam->end_date) : '—' }}</td>
                    <td><span class="badge bg-{{ $bc = $exam->is_published ? 'success' : 'warning' }}-subtle text-{{ $bc }}">{{ sm_t($exam->is_published ? 'Published' : 'Draft') }}</span></td>
                    <td><span class="badge bg-{{ $bc = $exam->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($exam->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td>
                        <a href="{{ route('exams.schedule') }}?exam_id={{ $exam->id }}" class="btn btn-sm btn-encodex-show" title="Schedule"><i class="fas fa-calendar-alt"></i></a>
                        <a href="{{ route('exams.marks') }}?exam_id={{ $exam->id }}" class="btn btn-sm btn-encodex-show" title="Marks"><i class="fas fa-pencil-alt"></i></a>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editExam({{ $exam->id }}, {{ json_encode($exam) }})"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('exams.destroy', $exam->id) }}" class="d-inline" onsubmit="return confirm('Delete this exam?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">{{ sm_t('No exams found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $exams->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="examModal" tabindex="-1">
    <div class="modal-dialog modal-lg glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title" id="examModalTitle">{{ sm_t('Add Exam') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="examForm" method="POST" action="{{ route('exams.store') }}">
                @csrf
                <span id="examMethodField"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-8 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Exam Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="examName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Exam Type') }} <span class="text-danger">*</span></label>
                            <select name="exam_type_id" id="examTypeId" class="form-control form-control-sm" required>
                                <option value="">{{ sm_t('Select') }}</option>
                                @foreach($examTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Start Date') }}</label>
                            <input type="date" name="start_date" id="examStartDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('End Date') }}</label>
                            <input type="date" name="end_date" id="examEndDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                            <select name="is_active" id="examIsActive" class="form-control form-control-sm">
                                <option value="1">{{ sm_t('Active') }}</option><option value="0">{{ sm_t('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Published') }}</label>
                            <select name="is_published" id="examIsPublished" class="form-control form-control-sm">
                                <option value="0">{{ sm_t('No (Draft)') }}</option><option value="1">{{ sm_t('Yes') }}</option>
                            </select>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Notice') }}</label>
                            <textarea name="notice" id="examNotice" class="form-control form-control-sm" rows="2"></textarea>
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
function resetExamForm() {
    document.getElementById('examModalTitle').textContent = 'Add Exam';
    document.getElementById('examForm').action = '{{ route('exams.store') }}';
    document.getElementById('examMethodField').innerHTML = '';
    ['examName','examNotice'].forEach(id => document.getElementById(id).value = '');
    ['examTypeId','examStartDate','examEndDate'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('examIsActive').value = '1';
    document.getElementById('examIsPublished').value = '0';
}
function editExam(id, e) {
    document.getElementById('examModalTitle').textContent = 'Edit Exam';
    document.getElementById('examForm').action = '/exams/' + id;
    document.getElementById('examMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('examName').value        = e.name ?? '';
    document.getElementById('examTypeId').value      = e.exam_type_id ?? '';
    document.getElementById('examStartDate').value   = e.start_date ? e.start_date.substring(0,10) : '';
    document.getElementById('examEndDate').value     = e.end_date   ? e.end_date.substring(0,10)   : '';
    document.getElementById('examIsActive').value    = e.is_active    ? '1' : '0';
    document.getElementById('examIsPublished').value = e.is_published ? '1' : '0';
    document.getElementById('examNotice').value      = e.notice ?? '';
    new bootstrap.Modal(document.getElementById('examModal')).show();
}
</script>
@endsection
