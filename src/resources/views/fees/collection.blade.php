@extends('me::master')
@section('title', sm_t('Fee Collection'))
@push('buttons')
    <a href="{{ route('fees.collection.create') }}" class="btn btn-sm btn-encodex-create">
        <i class="fas fa-plus"></i> New Invoice
    </a>
<form method="POST" action="{{ route('fees.reminders') }}" class="d-inline"
      onsubmit="return confirm('Send a fee-due reminder (SMS / e-mail) to the guardians of every student with unpaid fees{{ request('class_id') || request('month') ? ' in this filter' : '' }}?')">
    @csrf
    <input type="hidden" name="class_id" value="{{ request('class_id') }}">
    <input type="hidden" name="month" value="{{ request('month') }}">
    <button class="btn btn-encodex-edit btn-sm"><i class="fas fa-bell"></i> Send due reminders</button>
</form>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-3">
                <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Months') }}</option>
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">{{ sm_t('All Status') }}</option>
                    <option value="unpaid"  {{ request('status')=='unpaid'  ? 'selected':'' }}>{{ sm_t('Unpaid') }}</option>
                    <option value="partial" {{ request('status')=='partial' ? 'selected':'' }}>{{ sm_t('Partial') }}</option>
                    <option value="paid"    {{ request('status')=='paid'    ? 'selected':'' }}>{{ sm_t('Paid') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Student name / Invoice no" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-search me-1"><i class="fas fa-search"></i></button>
                <a href="{{ route('fees.collection') }}" class="btn btn-sm btn-encodex-cancel">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>{{ sm_t('Invoice No') }}</th>
                    <th>{{ sm_t('Student') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Fee Type') }}</th>
                    <th>{{ sm_t('Month') }}</th>
                    <th class="text-end">{{ sm_t('Amount') }}</th>
                    <th class="text-end">{{ sm_t('Paid') }}</th>
                    <th class="text-end">{{ sm_t('Due') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collections as $col)
                <tr>
                    <td class="small text-muted fw-semibold">{{ $col->invoice_no }}</td>
                    <td>{{ $col->student?->first_name }} {{ $col->student?->last_name }}</td>
                    <td class="small">{{ $col->class?->name ?? $col->student?->classe?->name ?? '—' }}</td>
                    <td class="small">{{ $col->feeType?->name ?? '—' }}</td>
                    <td class="small">{{ $col->monthLabel('M') ?? '—' }}</td>
                    <td class="text-end">{{ sm_money($col->amount, 2) }}</td>
                    <td class="text-end text-success fw-semibold">{{ sm_money($col->paid_amount, 2) }}</td>
                    <td class="text-end text-danger fw-semibold">{{ sm_money($col->due_amount, 2) }}</td>
                    <td>
                        @php $colors = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger']; @endphp
                        <span class="badge bg-{{ $bc = $colors[$col->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($col->status) }}</span>
                    </td>
                    <td class="text-center">
                        @if($col->status !== 'paid')
                        <button class="btn btn-sm btn-encodex-payment"
                            onclick="openPayModal({{ $col->id }}, {{ $col->due_amount }})">
                            <i class="fas fa-money-bill-wave"></i> Pay
                        </button>
                        @else
                        <span class="text-success small"><i class="fas fa-check-circle"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No fee records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-2">{{ $collections->links('pagination::bootstrap-5') }}</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Collect Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-1 small mb-2">
                        Due: <strong id="dueDisplay">{{ sm_currency() }}0</strong>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Amount') }} <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">{{ sm_currency() }}</span>
                            <input type="number" name="paid_amount" id="payAmountInput" class="form-control form-control-sm" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Payment Date') }}</label>
                        <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">{{ sm_t('Method') }}</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="cash">{{ sm_t('Cash') }}</option>
                            <option value="bank">{{ sm_t('Bank Transfer') }}</option>
                            <option value="mobile">{{ sm_t('Mobile Banking') }}</option>
                            <option value="cheque">{{ sm_t('Cheque') }}</option>
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label form-label-sm">{{ sm_t('Transaction ID') }}</label>
                        <input type="text" name="transaction_id" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-payment w-100"><i class="fas fa-check me-1"></i>Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPayModal(id, due) {
    document.getElementById('paymentForm').action = '/fees/collection/' + id + '/pay';
    document.getElementById('dueDisplay').textContent = '{{ sm_currency() }}' + parseFloat(due).toFixed(2);
    document.getElementById('payAmountInput').value = parseFloat(due).toFixed(2);
    document.getElementById('payAmountInput').max = parseFloat(due).toFixed(2);
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
</script>
@endpush
@endsection
