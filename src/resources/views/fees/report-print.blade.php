@extends('me::printMaster2')
@section('title', sm_t('Fee Collection Report'))

@section('contents')

@include('school-management::partials.print-header', [
    'title' => 'Fee Collection Report',
    'subtitle' => (request('from_date') || request('to_date')) ? ('Date: ' . (request('from_date') ?: '...') . ' to ' . (request('to_date') ?: '...')) : null,
])

<p class="text-right" style="font-weight:bold;">
    Total Collected: {{ sm_money($total, 2) }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>{{ sm_t('Invoice No') }}</th>
            <th>{{ sm_t('Student') }}</th>
            <th>{{ sm_t('Fee Type') }}</th>
            <th>{{ sm_t('Amount') }}</th>
            <th>{{ sm_t('Paid') }}</th>
            <th>{{ sm_t('Due') }}</th>
            <th>{{ sm_t('Pay Date') }}</th>
            <th>{{ sm_t('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($collections as $i => $c)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $c->invoice_no }}</td>
            <td>{{ $c->student?->full_name }}</td>
            <td>{{ $c->feeType?->name }}</td>
            <td>{{ sm_money($c->amount, 2) }}</td>
            <td>{{ sm_money($c->paid_amount, 2) }}</td>
            <td>{{ sm_money($c->due_amount, 2) }}</td>
            <td>{{ sm_date($c->payment_date) ?? '-' }}</td>
            <td>{{ ucfirst($c->status) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">No records for the selected period</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="grandtotal-row">
            <td colspan="4" class="text-right">Total</td>
            <td>{{ sm_money($collections->sum('amount'), 2) }}</td>
            <td>{{ sm_money($total, 2) }}</td>
            <td>{{ sm_money($collections->sum('due_amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@endsection
