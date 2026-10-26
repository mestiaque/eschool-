@extends('me::printMaster2')
@section('title', sm_t('Expense Report'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Expense Report',
    'subtitle' => collect([
        request('category_id') ? 'Category: ' . optional($categories->firstWhere('id', request('category_id')))->name : null,
        (request('from_date') || request('to_date')) ? 'Date: ' . (request('from_date') ?: '...') . ' to ' . (request('to_date') ?: '...') : null,
    ])->filter()->implode(' | ') ?: null,
])

<p class="text-right" style="font-weight:bold;">
    Total Expenses: {{ sm_money($expenses->sum('amount'), 2) }}
    &nbsp; | &nbsp;
    Total Records: {{ $expenses->count() }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ sm_t('Description') }}</th>
            <th>{{ sm_t('Category') }}</th>
            <th>{{ sm_t('Amount') }}</th>
            <th>{{ sm_t('Date') }}</th>
        </tr>
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
        <tr>
            <td colspan="5" class="text-center">{{ sm_t('No expenses for the selected period') }}</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="grandtotal-row">
            <td colspan="3" class="text-right">{{ sm_t('Total') }}</td>
            <td>{{ sm_money($expenses->sum('amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
