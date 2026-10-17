@extends('me::printMaster2')
@section('title', sm_t('Income & Expense Report'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Income & Expense Report',
    'subtitle' => (request('from_date') || request('to_date')) ? ('Date: ' . (request('from_date') ?: '...') . ' to ' . (request('to_date') ?: '...')) : null,
])

<p class="text-center" style="font-weight:bold;">
    Total Income: {{ sm_money($totalIncome, 2) }}
    &nbsp; | &nbsp;
    Total Expense: {{ sm_money($totalExpense, 2) }}
    &nbsp; | &nbsp;
    Net Balance:
    <span style="{{ $profit < 0 ? 'color:#c00;' : '' }}">{{ sm_money($profit, 2) }}</span>
</p>

<p style="font-weight:bold;margin-bottom:5px;">Expenses</p>
<table>
    <thead>
        <tr>
            <th>{{ sm_t('Description') }}</th>
            <th>{{ sm_t('Category') }}</th>
            <th>{{ sm_t('Amount') }}</th>
            <th>{{ sm_t('Date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $exp)
        <tr>
            <td>{{ $exp->description ?? '-' }}</td>
            <td>{{ $exp->expenseCategory?->name }}</td>
            <td>{{ sm_money($exp->amount, 2) }}</td>
            <td>{{ sm_date($exp->date) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No expenses</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal-row">
            <td colspan="2" class="text-right">Total Expense</td>
            <td colspan="2">{{ sm_money($totalExpense, 2) }}</td>
        </tr>
    </tfoot>
</table>

<p style="font-weight:bold;margin-bottom:5px;">Income</p>
<table>
    <thead>
        <tr>
            <th>{{ sm_t('Description') }}</th>
            <th>{{ sm_t('Category') }}</th>
            <th>{{ sm_t('Amount') }}</th>
            <th>{{ sm_t('Date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($incomes as $inc)
        <tr>
            <td>{{ $inc->description ?? '-' }}</td>
            <td>{{ $inc->incomeCategory?->name }}</td>
            <td>{{ sm_money($inc->amount, 2) }}</td>
            <td>{{ sm_date($inc->date) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No income</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="subtotal-row">
            <td colspan="2" class="text-right">Total Income</td>
            <td colspan="2">{{ sm_money($totalIncome, 2) }}</td>
        </tr>
    </tfoot>
</table>

@endsection
