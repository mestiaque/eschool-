@extends('me::master')
@section('title', sm_t('Expenses'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#expenseModal">
    <i class="fas fa-plus"></i> Add Expense
</button>
@endpush

@section('content')
<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2">
            <div class="col-md-3">
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Categories') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-search">{{ sm_t('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Date') }}</th>
                    <th>{{ sm_t('Category') }}</th>
                    <th>{{ sm_t('Amount') }}</th>
                    <th>{{ sm_t('Description') }}</th>
                    <th>{{ sm_t('Vendor') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ sm_date($expense->date) }}</td>
                    <td>{{ $expense->expenseCategory->name ?? '' }}</td>
                    <td>{{ sm_money($expense->amount, 2) }}</td>
                    <td>{{ $expense->description ?? '-' }}</td>
                    <td>{{ $expense->vendor_name ?? '-' }}</td>
                    <td>
                        @if($owner = \ME\SchoolManagement\Services\Ledger::ownerLabel($expense))
                            <span class="badge bg-secondary-subtle text-secondary" title="{{ sm_t('Managed automatically') }}">{{ sm_t($owner) }}</span>
                        @else
                        <button class="btn btn-sm btn-encodex-edit" onclick="editExpense({{ $expense->id }}, {{ $expense->expense_category_id }}, {{ $expense->amount }}, '{{ \Carbon\Carbon::parse($expense->date)->format('Y-m-d') }}', '{{ addslashes($expense->description ?? '') }}', '{{ addslashes($expense->vendor_name ?? '') }}')"><i class="fas fa-edit"></i></button>
                        <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-2">{{ $expenses->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">{{ sm_t('Add Expense') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseForm" method="POST" action="{{ route('expenses.store') }}">
                @csrf
                <input type="hidden" name="_method" id="expenseMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Category *</label>
                        <select name="expense_category_id" id="expCategory" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select') }}</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Amount *</label>
                        <input type="number" name="amount" id="expAmount" class="form-control form-control-sm" step="0.01" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" id="expDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Description') }}</label>
                        <textarea name="description" id="expDescription" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Vendor Name') }}</label>
                        <input type="text" name="vendor_name" id="expVendor" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function editExpense(id, categoryId, amount, date, description, vendor) {
    document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
    document.getElementById('expenseForm').action = '/expenses/' + id;
    document.getElementById('expenseMethod').value = 'PUT';
    document.getElementById('expCategory').value = categoryId;
    document.getElementById('expAmount').value = amount;
    document.getElementById('expDate').value = date;
    document.getElementById('expDescription').value = description;
    document.getElementById('expVendor').value = vendor;
    new bootstrap.Modal(document.getElementById('expenseModal')).show();
}
document.getElementById('expenseModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('expenseModalTitle').textContent = 'Add Expense';
    document.getElementById('expenseForm').action = '{{ route('expenses.store') }}';
    document.getElementById('expenseMethod').value = 'POST';
    document.getElementById('expenseForm').reset();
});
</script>
@endsection
