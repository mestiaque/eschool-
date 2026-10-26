@extends('me::printMaster2')
@section('title', sm_t('Fee Report'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Fee Report',
    'subtitle' => collect([
        request('class_id') ? 'Class: ' . optional($classes->firstWhere('id', request('class_id')))->name : null,
        request('status') ? 'Status: ' . ucfirst(request('status')) : null,
        (request('from_date') || request('to_date')) ? 'Date: ' . (request('from_date') ?: '...') . ' to ' . (request('to_date') ?: '...') : null,
    ])->filter()->implode(' | ') ?: null,
])

<p class="text-right" style="font-weight:bold;">
    Total Collected: {{ sm_money($collections->sum('paid_amount'), 2) }}
    &nbsp; | &nbsp;
    Total Due: {{ sm_money($collections->sum('due_amount'), 2) }}
    &nbsp; | &nbsp;
    Total Invoiced: {{ sm_money($collections->sum('amount'), 2) }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ sm_t('Invoice') }}</th>
            <th>{{ sm_t('Student') }}</th>
            <th>{{ sm_t('Class') }}</th>
            <th>{{ sm_t('Fee Type') }}</th>
            <th>{{ sm_t('Amount') }}</th>
            <th>{{ sm_t('Fine') }}</th>
            <th>{{ sm_t('Paid') }}</th>
            <th>{{ sm_t('Due') }}</th>
            <th>{{ sm_t('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($collections as $i => $c)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $c->invoice_no }}</td>
            <td>{{ $c->student?->full_name }}</td>
            <td>{{ $c->student?->classe?->name }}</td>
            <td>{{ $c->feeType?->name }}</td>
            <td>{{ sm_money($c->amount, 2) }}</td>
            <td>{{ sm_money($c->fine, 2) }}</td>
            <td>{{ sm_money($c->paid_amount, 2) }}</td>
            <td>{{ sm_money($c->due_amount, 2) }}</td>
            <td>{{ sm_t(ucfirst($c->status)) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center">{{ sm_t('No records') }}</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="grandtotal-row">
            <td colspan="5" class="text-right">{{ sm_t('Total') }}</td>
            <td>{{ sm_money($collections->sum('amount'), 2) }}</td>
            <td>{{ sm_money($collections->sum('fine'), 2) }}</td>
            <td>{{ sm_money($collections->sum('paid_amount'), 2) }}</td>
            <td>{{ sm_money($collections->sum('due_amount'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

@endsection
