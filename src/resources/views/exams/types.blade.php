@extends('me::master')
@section('title', sm_t('Exam Types'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#typeModal" onclick="resetTypeForm()">
        <i class="fas fa-plus"></i> Add Exam Type
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr><th>#</th><th>{{ sm_t('Name') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Action') }}</th></tr>
            </thead>
            <tbody>
                @forelse($examTypes as $i => $type)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="fw-semibold">{{ $type->name }}</td>
                    <td><span class="badge bg-{{ $bc = $type->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editType({{ $type->id }}, '{{ addslashes($type->name) }}', {{ $type->is_active ? 1 : 0 }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('exams.types.destroy', $type->id) }}" class="d-inline" onsubmit="return confirm('Delete this exam type?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No exam types found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $examTypes->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <div class="modal-content">
            <div class="modal-header py-2"><h6 class="modal-title" id="typeModalTitle">{{ sm_t('Add Exam Type') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="typeForm" method="POST" action="{{ route('exams.types.store') }}">
                @csrf
                <span id="typeMethodField"></span>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label form-label-sm">{{ sm_t('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="typeName" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                        <select name="is_active" id="typeActive" class="form-control form-control-sm">
                            <option value="1">{{ sm_t('Active') }}</option><option value="0">{{ sm_t('Inactive') }}</option>
                        </select>
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
function resetTypeForm() {
    document.getElementById('typeModalTitle').textContent = 'Add Exam Type';
    document.getElementById('typeForm').action = '{{ route('exams.types.store') }}';
    document.getElementById('typeMethodField').innerHTML = '';
    document.getElementById('typeName').value = '';
    document.getElementById('typeActive').value = '1';
}
function editType(id, name, active) {
    document.getElementById('typeModalTitle').textContent = 'Edit Exam Type';
    document.getElementById('typeForm').action = '/exams/types/' + id;
    document.getElementById('typeMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('typeName').value = name;
    document.getElementById('typeActive').value = active ? '1' : '0';
    new bootstrap.Modal(document.getElementById('typeModal')).show();
}
</script>
@endsection
