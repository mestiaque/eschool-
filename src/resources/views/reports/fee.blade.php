@extends('me::master')
@section('title', sm_t('Fee Report'))

@push('buttons')
<a href="{{ route('reports.fees.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-encodex-print"><i class="fas fa-print"></i> Print</a>
@endpush

@section('content')
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-white bg-success"><div class="card-body py-2"><small>Total Collected</small><h5 class="mb-0">{{ sm_money($collections->sum('paid_amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger"><div class="card-body py-2"><small>Total Due</small><h5 class="mb-0">{{ sm_money($collections->sum('due_amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary"><div class="card-body py-2"><small>Total Invoiced</small><h5 class="mb-0">{{ sm_money($collections->sum('amount'), 2) }}</h5></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info"><div class="card-body py-2"><small>Total Fine</small><h5 class="mb-0">{{ sm_money($collections->sum('fine'), 2) }}</h5></div></div>
    </div>
</div>

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <select name="class_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)<option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ sm_t('Paid') }}</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ sm_t('Partial') }}</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ sm_t('Unpaid') }}</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}"></div>
            <div class="col-md-2"><input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Generate') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>{{ sm_t('Invoice') }}</th><th>{{ sm_t('Student') }}</th><th>{{ sm_t('Class') }}</th><th>{{ sm_t('Fee Type') }}</th><th>{{ sm_t('Amount') }}</th><th>{{ sm_t('Fine') }}</th><th>{{ sm_t('Paid') }}</th><th>{{ sm_t('Due') }}</th><th>{{ sm_t('Status') }}</th></tr>
            </thead>
            <tbody>
                @forelse($collections as $i => $c)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><small>{{ $c->invoice_no }}</small></td>
                    <td>{{ $c->student?->full_name }}</td>
                    <td>{{ $c->student?->classe?->name }}</td>
                    <td>{{ $c->feeType?->name }}</td>
                    <td>{{ sm_money($c->amount, 2) }}</td>
                    <td>{{ sm_money($c->fine, 2) }}</td>
                    <td>{{ sm_money($c->paid_amount, 2) }}</td>
                    <td>{{ sm_money($c->due_amount, 2) }}</td>
                    <td><span class="badge bg-{{ $bc = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger'][$c->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($c->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">No records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
