@extends('me::master')
@section('title', sm_t('Fee Collection Report'))

@push('buttons')
<a href="{{ route('fees.report.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print"><i class="fas fa-print"></i> Print</a>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card text-white bg-success"><div class="card-body py-2"><small>Total Collected</small><h5 class="mb-0">{{ sm_money($collections->sum('paid_amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger"><div class="card-body py-2"><small>Total Due</small><h5 class="mb-0">{{ sm_money($collections->sum('due_amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary"><div class="card-body py-2"><small>Total Invoiced</small><h5 class="mb-0">{{ sm_money($collections->sum('amount'), 2) }}</h5></div></div>
    </div>
</div>

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date', date('Y-m-01')) }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Generate') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>{{ sm_t('Invoice No') }}</th><th>{{ sm_t('Student') }}</th><th>{{ sm_t('Fee Type') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Paid') }}</th><th>{{ sm_t('Due') }}</th><th>{{ sm_t('Pay Date') }}</th><th>{{ sm_t('Status') }}</th></tr>
            </thead>
            <tbody>
                @forelse($collections as $i => $c)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><small>{{ $c->invoice_no }}</small></td>
                    <td>{{ $c->student?->full_name }}</td>
                    <td>{{ $c->feeType?->name }}</td>
                    <td>{{ sm_money($c->amount, 2) }}</td>
                    <td>{{ sm_money($c->paid_amount, 2) }}</td>
                    <td>{{ sm_money($c->due_amount, 2) }}</td>
                    <td>{{ sm_date($c->payment_date) ?? '-' }}</td>
                    <td><span class="badge bg-{{ $bc = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$c->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($c->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No records for the selected period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
