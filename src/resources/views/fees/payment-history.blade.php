@extends('me::master')

@section('title', sm_t('Payment History'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="glass-search-form mb-3 row g-2">
                        <div class="col-md-3">
                            <input type="text" name="invoice_no" class="form-control form-control-sm" placeholder="Invoice No" value="{{ request('invoice_no') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="from_date" class="form-control form-control-sm" placeholder="From Date" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="to_date" class="form-control form-control-sm" placeholder="To Date" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-encodex-search btn-sm w-100">{{ sm_t('Filter') }}</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-encodex table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>{{ sm_t('ID') }}</th>
                                    <th>{{ sm_t('Invoice No') }}</th>
                                    <th>{{ sm_t('Student') }}</th>
                                    <th>{{ sm_t('Amount') }}</th>
                                    <th>{{ sm_t('Payment Date') }}</th>
                                    <th>{{ sm_t('Method') }}</th>
                                    <th>{{ sm_t('Transaction ID') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>{{ $payment->feeCollection?->invoice_no ?? 'N/A' }}</td>
                                    <td>{{ $payment->feeCollection?->student?->full_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ sm_date($payment->date) }}</td>
                                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                    <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No payments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $payments->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
