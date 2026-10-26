@extends('me::master')
@section('title', sm_t('Income'))
@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#incomeModal"><i class="fas fa-plus"></i> Add Income</button>
@endpush

@section('content')

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-white bg-success"><div class="card-body py-2"><small>Total Income</small><h5 class="mb-0">{{ sm_money($incomes->sum('amount'), 2) }}</h5></div></div>
    </div>
</div>

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3"><input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}"></div>
            <div class="col-md-3"><input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped table-encodex mb-0">
            <thead class="table-light"><tr><th>#</th><th>{{ sm_t('Category') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Date') }}</th><th>{{ sm_t('Description') }}</th><th>{{ sm_t('Action') }}</th></tr></thead>
            <tbody>
                @forelse($incomes as $i => $inc)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $inc->incomeCategory?->name }}</td>
                    <td>{{ sm_money($inc->amount, 2) }}</td>
                    <td>{{ sm_date($inc->date) }}</td>
                    <td><small>{{ $inc->description ?? '-' }}</small></td>
                    <td>
                        @if($owner = \ME\SchoolManagement\Services\Ledger::ownerLabel($inc))
                            <span class="badge bg-secondary-subtle text-secondary" title="{{ sm_t('Managed automatically') }}">{{ sm_t($owner) }}</span>
                        @else
                        <button class="btn btn-sm btn-encodex-edit" onclick="editIncome({{ $inc->id }}, {{ $inc->income_category_id }}, {{ $inc->amount }}, '{{ $inc->date?->format('Y-m-d') }}', '{{ addslashes($inc->description ?? '') }}')"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('expenses.income.destroy', $inc->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No income records</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">{{ $incomes->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="incomeModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="incomeTitle">{{ sm_t('Add Income') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form id="incomeForm" method="POST" action="{{ route('expenses.income.store') }}">
                @csrf<input type="hidden" name="_method" id="incomeMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Category') }}</label>
                        <select name="income_category_id" id="incCat" class="form-control form-control-sm">
                            <option value="">{{ sm_t('Select') }}</option>
                            @foreach($incomeCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">Amount *</label><input type="number" name="amount" id="incAmount" class="form-control form-control-sm" step="0.01" required></div>
                        <div class="col"><label class="form-label">Date *</label><input type="date" name="date" id="incDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                    </div>
                    <div class="mb-2"><label class="form-label">{{ sm_t('Description') }}</label><textarea name="description" id="incDescription" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button></div>
            </form>
        </div>
    </div>
</div>
<script>
function editIncome(id, cat, amount, date, description) {
    document.getElementById('incomeTitle').textContent = 'Edit Income';
    document.getElementById('incomeForm').action = '/expenses/income/' + id;
    document.getElementById('incomeMethod').value = 'PUT';
    document.getElementById('incCat').value = cat;
    document.getElementById('incAmount').value = amount;
    document.getElementById('incDate').value = date;
    document.getElementById('incDescription').value = description;
    new bootstrap.Modal(document.getElementById('incomeModal')).show();
}
document.getElementById('incomeModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('incomeTitle').textContent = 'Add Income';
    document.getElementById('incomeForm').action = '{{ route('expenses.income.store') }}';
    document.getElementById('incomeMethod').value = 'POST';
    document.getElementById('incomeForm').reset();
});
</script>
@endsection
