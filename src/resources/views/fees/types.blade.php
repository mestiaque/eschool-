@extends('me::master')
@section('title', sm_t('Fee Types'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#feeTypeModal">
        <i class="fas fa-plus"></i> Add Fee Type
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
                    <th class="text-end">{{ sm_t('Default Amount') }}</th>
                    <th>{{ sm_t('Description') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feeTypes as $i => $type)
                <tr>
                    <td class="text-muted small">{{ $i+1 }}</td>
                    <td class="fw-semibold">{{ $type->name }}</td>
                    <td><span class="badge bg-info-subtle text-info">{{ ucfirst($type->type ?? 'other') }}</span></td>
                    <td class="text-end">{{ sm_money($type->amount ?? 0, 2) }}</td>
                    <td class="small text-muted">{{ $type->description ?? '—' }}</td>
                    <td><span class="badge bg-{{ $bc = $type->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-encodex-edit" onclick="editFeeType({{ $type->id }}, {{ json_encode($type) }})"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('fees.types.destroy', $type->id) }}" class="d-inline" onsubmit="return confirm('Delete this fee type?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No fee types defined.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $feeTypes->links('pagination::bootstrap-5') }}</div>

{{-- Add / Edit Modal --}}
<div class="modal fade" id="feeTypeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="feeTypeModalTitle">{{ sm_t('Add Fee Type') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="feeTypeForm" action="{{ route('fees.types.store') }}">
                @csrf
                <span id="feeTypeMethod"></span>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-8 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="ftName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Type') }}</label>
                            <select name="type" id="ftType" class="form-control form-control-sm">
                                @foreach(['monthly'=>'Monthly','admission'=>'Admission','exam'=>'Exam','transport'=>'Transport','library'=>'Library','hostel'=>'Hostel','other'=>'Other'] as $k=>$v)
                                <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Default Amount') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">{{ sm_currency() }}</span>
                                <input type="number" name="amount" id="ftAmount" class="form-control form-control-sm" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                            <select name="is_active" id="ftActive" class="form-control form-control-sm">
                                <option value="1">{{ sm_t('Active') }}</option>
                                <option value="0">{{ sm_t('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Description') }}</label>
                            <textarea name="description" id="ftDesc" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editFeeType(id, data) {
    document.getElementById('feeTypeModalTitle').textContent = 'Edit Fee Type';
    document.getElementById('feeTypeForm').action = '/fees/types/' + id;
    document.getElementById('feeTypeMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('ftName').value   = data.name   ?? '';
    document.getElementById('ftType').value   = data.type   ?? 'other';
    document.getElementById('ftAmount').value = data.amount ?? 0;
    document.getElementById('ftActive').value = data.is_active ? '1' : '0';
    document.getElementById('ftDesc').value   = data.description ?? '';
    new bootstrap.Modal(document.getElementById('feeTypeModal')).show();
}
document.getElementById('feeTypeModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('feeTypeModalTitle').textContent = 'Add Fee Type';
    document.getElementById('feeTypeForm').action = '{{ route('fees.types.store') }}';
    document.getElementById('feeTypeMethod').innerHTML = '';
    document.getElementById('feeTypeForm').reset();
});
</script>
@endpush
@endsection
