@extends('me::master')
@section('title', sm_t('Monthly Fee Collection'))

@section('content')

<!-- Generate Monthly Fee Form -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ sm_t('Generate Monthly Fee') }}</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('fees.generate-monthly') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Class') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Academic Year *</label>
                <select name="academic_year_id" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Year') }}</option>
                    @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fee Type *</label>
                <select name="fee_type_id" class="form-control form-control-sm" required>
                    <option value="">{{ sm_t('Select Fee Type') }}</option>
                    @foreach($feeTypes as $ft)
                    <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month *</label>
                <select name="month" class="form-control form-control-sm" required>
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ sm_t('Due Date') }}</label>
                <input type="date" name="due_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-encodex-save w-100">{{ sm_t('Generate') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <select name="class_id" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Classes') }}</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Months') }}</option>
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
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
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Month') }}</th>
                    <th>{{ sm_t('Amount') }}</th>
                    <th>{{ sm_t('Fine') }}</th>
                    <th>{{ sm_t('Paid') }}</th>
                    <th>{{ sm_t('Due') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($collections as $col)
                <tr>
                    <td>{{ $col->invoice_no }}</td>
                    <td>{{ $col->student?->full_name }}</td>
                    <td>{{ $col->student?->classe?->name }}</td>
                    <td>{{ $col->monthLabel('F') ?? '-' }}</td>
                    <td>{{ number_format($col->amount, 2) }}</td>
                    <td>{{ number_format($col->fine, 2) }}</td>
                    <td>{{ number_format($col->paid_amount, 2) }}</td>
                    <td>{{ number_format($col->due_amount, 2) }}</td>
                    <td>
                        @php $colors = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger']; @endphp
                        <span class="badge bg-{{ $bc = $colors[$col->status] ?? 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($col->status) }}</span>
                    </td>
                    <td>
                        @if($col->status !== 'paid')
                        <button class="btn btn-sm btn-encodex-payment" data-bs-toggle="modal" data-bs-target="#payModal{{ $col->id }}">Pay</button>
                        @else
                        <span class="text-success small"><i class="fas fa-check"></i> Paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-3">No records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $collections->links('pagination::bootstrap-5') }}</div>
</div>

@foreach($collections as $col)
@if($col->status !== 'paid')
<!-- Pay Modal -->
<div class="modal fade" id="payModal{{ $col->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm glass-card">
        <form method="POST" action="{{ route('fees.collection.pay', $col->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h6 class="modal-title">{{ sm_t('Pay Fee') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Due: <strong>{{ number_format($col->due_amount, 2) }}</strong></p>
                    <input type="number" name="paid_amount" class="form-control form-control-sm mb-2" placeholder="Amount" step="0.01" required>
                    <input type="date" name="payment_date" class="form-control form-control-sm mb-2" value="{{ date('Y-m-d') }}">
                    <select name="payment_method" class="form-control form-control-sm">
                        <option value="cash">{{ sm_t('Cash') }}</option>
                        <option value="bank">{{ sm_t('Bank') }}</option>
                        <option value="mobile">{{ sm_t('Mobile Banking') }}</option>
                    </select>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-payment w-100">{{ sm_t('Confirm Payment') }}</button></div>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach
@endsection
