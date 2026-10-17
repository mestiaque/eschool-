@extends('me::master')
@section('title', sm_t('Expense Report'))

@push('buttons')
<a href="{{ route('reports.expenses.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print"><i class="fas fa-print"></i> Print</a>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card text-white bg-danger"><div class="card-body py-2"><small>Total Expenses</small><h5 class="mb-0">{{ sm_money($expenses->sum('amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-secondary"><div class="card-body py-2"><small>Total Records</small><h5 class="mb-0">{{ $expenses->count() }}</h5></div></div>
    </div>
</div>

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3"><input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date', date('Y-m-01')) }}"></div>
            <div class="col-md-3"><input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date', date('Y-m-d')) }}"></div>
            <div class="col-md-3">
                <select name="category_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Categories') }}</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Generate') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>{{ sm_t('Description') }}</th><th>{{ sm_t('Category') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Date') }}</th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $i => $exp)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $exp->description ?? '-' }}</td>
                    <td>{{ $exp->expenseCategory?->name }}</td>
                    <td>{{ sm_money($exp->amount, 2) }}</td>
                    <td>{{ sm_date($exp->date) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No expenses for the selected period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
