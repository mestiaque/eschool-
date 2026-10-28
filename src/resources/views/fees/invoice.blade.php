@extends('me::master')
@section('title', sm_t('Invoices'))

@push('buttons')
<a href="{{ route('fees.collection.create') }}" class="btn btn-sm btn-encodex-create">
    <i class="fas fa-plus"></i> {{ sm_t('Create Invoice') }}
</a>
@endpush

@section('content')
@include('school-management::partials.receipt-link')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ sm_t('Student name or invoice no...') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>{{ sm_t('Unpaid') }}</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>{{ sm_t('Partial') }}</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ sm_t('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control form-control-sm" placeholder="{{ sm_t('From') }}" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control form-control-sm" placeholder="{{ sm_t('To') }}" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ sm_t('Invoice No') }}</th>
                    <th>{{ sm_t('Student') }}</th>
                    <th>{{ sm_t('Fee Type') }}</th>
                    <th>{{ sm_t('Amount') }}</th>
                    <th>{{ sm_t('Fine') }}</th>
                    <th>{{ sm_t('Discount') }}</th>
                    <th>{{ sm_t('Paid') }}</th>
                    <th>{{ sm_t('Due') }}</th>
                    <th>{{ sm_t('Due Date') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collections as $col)
                <tr>
                    <td><small>{{ $col->invoice_no }}</small></td>
                    <td>{{ $col->student?->full_name }}</td>
                    <td>{{ $col->feeType?->name }}</td>
                    <td>{{ number_format($col->amount, 2) }}</td>
                    <td>{{ number_format($col->fine, 2) }}</td>
                    <td>{{ number_format($col->discount, 2) }}</td>
                    <td>{{ number_format($col->paid_amount, 2) }}</td>
                    <td>{{ number_format($col->due_amount, 2) }}</td>
                    <td>{{ sm_date($col->due_date) }}</td>
                    <td>
                        @php $colors = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger']; @endphp
                        <span class="badge bg-{{ $bc = $colors[$col->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t(ucfirst($col->status)) }}</span>
                    </td>
                    <td>
                        @if($col->status !== 'paid')
                        <button class="btn btn-sm btn-encodex-payment" data-bs-toggle="modal" data-bs-target="#payModal{{ $col->id }}">{{ sm_t('Pay') }}</button>
                        @endif
                        <a href="{{ route('fees.collection.print', $col->id) }}" target="_blank" class="btn btn-sm btn-encodex-print" title="{{ sm_t('Print Invoice') }}"><i class="fas fa-print"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted py-3">{{ sm_t('No invoices found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $collections->links('pagination::bootstrap-5') }}</div>
</div>

@foreach($collections as $col)
@if($col->status !== 'paid')
<div class="modal fade" id="payModal{{ $col->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <form method="POST" action="{{ route('fees.collection.pay', $col->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h6 class="modal-title">{{ sm_t('Receive Payment') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small mb-1">{{ sm_t('Student') }}: <strong>{{ $col->student?->full_name }}</strong></p>
                    <p class="small mb-2">{{ sm_t('Due') }}: <strong>{{ sm_money($col->due_amount, 2) }}</strong></p>
                    <input type="number" name="paid_amount" class="form-control form-control-sm mb-2" placeholder="{{ sm_t('Amount to pay') }}" step="0.01" max="{{ $col->due_amount }}" required>
                    <input type="date" name="payment_date" class="form-control form-control-sm mb-2" value="{{ date('Y-m-d') }}">
                    <select name="payment_method" class="form-control form-control-sm mb-2">
                        <option value="cash">{{ sm_t('Cash') }}</option>
                        <option value="bank">{{ sm_t('Bank Transfer') }}</option>
                        <option value="mobile">{{ sm_t('Mobile Banking') }}</option>
                    </select>
                    <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="{{ sm_t('Transaction ID (optional)') }}">
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-payment w-100">{{ sm_t('Confirm') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
@endsection
