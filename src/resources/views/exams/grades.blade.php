@extends('me::master')
@section('title', sm_t('Grade Setup'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#gradeModal" onclick="resetGradeForm()">
        <i class="fas fa-plus"></i> {{ sm_t('Add Grade') }}
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Grade') }}</th>
                    <th>{{ sm_t('Grade Point') }}</th>
                    <th>{{ sm_t('Mark From') }}</th>
                    <th>{{ sm_t('Mark To') }}</th>
                    <th>{{ sm_t('Description') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $i => $grade)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="fw-bold text-primary">{{ $grade->name }}</td>
                    <td class="text-center">{{ $grade->grade_point }}</td>
                    <td class="text-center">{{ $grade->mark_from }}</td>
                    <td class="text-center">{{ $grade->mark_to }}</td>
                    <td class="small text-muted">{{ $grade->description ?? '—' }}</td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editGrade({{ $grade->id }}, {{ json_encode($grade) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('exams.grades.destroy', $grade->id) }}" class="d-inline" onsubmit="return confirm('Delete this grade?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">{{ sm_t('No grades found. Add grades to enable auto-grading in marks entry.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $grades->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="gradeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title" id="gradeModalTitle">{{ sm_t('Add Grade') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="gradeForm" method="POST" action="{{ route('exams.grades.store') }}">
                @csrf
                <span id="gradeMethodField"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Grade Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="gradeName" class="form-control form-control-sm" placeholder="{{ sm_t('e.g. A+') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Grade Point') }} <span class="text-danger">*</span></label>
                            <input type="number" name="grade_point" id="gradePoint" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Mark From') }} <span class="text-danger">*</span></label>
                            <input type="number" name="mark_from" id="gradeMarkFrom" class="form-control form-control-sm" min="0" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Mark To') }} <span class="text-danger">*</span></label>
                            <input type="number" name="mark_to" id="gradeMarkTo" class="form-control form-control-sm" min="0" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label form-label-sm">{{ sm_t('Description') }}</label>
                            <input type="text" name="description" id="gradeDescription" class="form-control form-control-sm">
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
function resetGradeForm() {
    document.getElementById('gradeModalTitle').textContent = 'Add Grade';
    document.getElementById('gradeForm').action = '{{ route('exams.grades.store') }}';
    document.getElementById('gradeMethodField').innerHTML = '';
    ['gradeName','gradePoint','gradeMarkFrom','gradeMarkTo','gradeDescription'].forEach(id => document.getElementById(id).value = '');
}
function editGrade(id, g) {
    document.getElementById('gradeModalTitle').textContent = 'Edit Grade';
    document.getElementById('gradeForm').action = '/exams/grades/' + id;
    document.getElementById('gradeMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('gradeName').value        = g.name ?? '';
    document.getElementById('gradePoint').value       = g.grade_point ?? '';
    document.getElementById('gradeMarkFrom').value    = g.mark_from ?? '';
    document.getElementById('gradeMarkTo').value      = g.mark_to ?? '';
    document.getElementById('gradeDescription').value = g.description ?? '';
    new bootstrap.Modal(document.getElementById('gradeModal')).show();
}
</script>
@endsection
