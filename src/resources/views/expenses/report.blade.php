@extends('me::master')
@section('title', sm_t('Expense Report'))

@push('buttons')
<a href="{{ route('expenses.report.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print"><i class="fas fa-print"></i> {{ sm_t('Print') }}</a>
@endpush

@section('content')
<form method="GET" class="glass-search-form row g-2 align-items-end mb-3">
    <div class="col-md-3"><input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date', date('Y-m-01')) }}"></div>
    <div class="col-md-3"><input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date', date('Y-m-d')) }}"></div>
    <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Generate') }}</button></div>
</form>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-white bg-success"><div class="card-body py-2"><small>{{ sm_t('Total Income') }}</small><h5 class="mb-0">{{ sm_money($totalIncome, 2) }}</h5></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger"><div class="card-body py-2"><small>{{ sm_t('Total Expense') }}</small><h5 class="mb-0">{{ sm_money($totalExpense, 2) }}</h5></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-{{ ($totalIncome - $totalExpense) >= 0 ? 'primary' : 'warning' }}">
            <div class="card-body py-2"><small>{{ sm_t('Net Balance') }}</small><h5 class="mb-0">{{ sm_money($totalIncome - $totalExpense, 2) }}</h5></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Expenses') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-encodex table-sm mb-0">
                    <thead class="table-light"><tr><th>{{ sm_t('Description') }}</th><th>{{ sm_t('Category') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Date') }}</th></tr></thead>
                    <tbody>
                        @forelse($expenses as $exp)
                        <tr>
                            <td>{{ $exp->description ?? '-' }}</td>
                            <td>{{ $exp->expenseCategory?->name }}</td>
                            <td>{{ sm_money($exp->amount, 2) }}</td>
                            <td>{{ $exp->date?->format('d M') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">{{ sm_t('No expenses') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ sm_t('Income') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-encodex table-sm mb-0">
                    <thead class="table-light"><tr><th>{{ sm_t('Description') }}</th><th>{{ sm_t('Category') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Date') }}</th></tr></thead>
                    <tbody>
                        @forelse($incomes as $inc)
                        <tr>
                            <td>{{ $inc->description ?? '-' }}</td>
                            <td>{{ $inc->incomeCategory?->name }}</td>
                            <td>{{ sm_money($inc->amount, 2) }}</td>
                            <td>{{ $inc->date?->format('d M') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-2">{{ sm_t('No income') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
