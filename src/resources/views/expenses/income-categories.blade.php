@extends('me::master')
@section('title', sm_t('Income Categories'))
@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#catModal"><i class="fas fa-plus"></i> {{ sm_t('Add Category') }}</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-encodex mb-0">
            <thead class="table-light"><tr><th>#</th><th>{{ sm_t('Name') }}</th><th>{{ sm_t('Description') }}</th><th>{{ sm_t('Status') }}</th><th>{{ sm_t('Action') }}</th></tr></thead>
            <tbody>
                @forelse($categories as $i => $cat)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $cat->name }}</td>
                    <td>{{ $cat->description ?? '-' }}</td>
                    <td><span class="badge bg-{{ $bc = $cat->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($cat->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-encodex-edit" onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}', {{ $cat->is_active }})"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('expenses.income-categories.destroy', $cat->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">{{ sm_t('No income categories') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $categories->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="catTitle">{{ sm_t('Add Income Category') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="catForm" method="POST" action="{{ route('expenses.income-categories.store') }}">
                @csrf<input type="hidden" name="_method" id="catMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label">{{ sm_t('Name') }} *</label><input type="text" name="name" id="catName" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="form-label">{{ sm_t('Description') }}</label><textarea name="description" id="catDesc" class="form-control form-control-sm" rows="2"></textarea></div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Status') }}</label>
                        <select name="is_active" id="catActive" class="form-control form-control-sm"><option value="1">{{ sm_t('Active') }}</option><option value="0">{{ sm_t('Inactive') }}</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button></div>
            </form>
        </div>
    </div>
</div>
<script>
function editCat(id, name, desc, active) {
    document.getElementById('catTitle').textContent = 'Edit Category';
    document.getElementById('catForm').action = '/expenses/income-categories/' + id;
    document.getElementById('catMethod').value = 'PUT';
    document.getElementById('catName').value = name;
    document.getElementById('catDesc').value = desc;
    document.getElementById('catActive').value = active ? '1' : '0';
    new bootstrap.Modal(document.getElementById('catModal')).show();
}
</script>
@endsection
