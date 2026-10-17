@extends('me::master')
@section('title', sm_t('New Fee Invoice'))
@push('buttons')
    <a href="{{ route('fees.collection') }}" class="btn btn-sm btn-encodex-list"><i class="fas fa-arrow-left"></i> Back</a>
@endpush

@section('content')
@if($errors->any())<div class="alert alert-danger py-2 small"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card shadow" style="max-width:860px">
    <div class="card-header py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i>Create Fee Invoice</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('fees.collection.store') }}" id="feeForm">
            @csrf
            <div class="row g-2">

                {{-- Student --}}
                <div class="col-md-6 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Student') }} <span class="text-danger">*</span></label>
                    @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'placeholder' => 'Select Student'])
                </div>

                {{-- Fee Type --}}
                <div class="col-md-3 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Fee Type') }} <span class="text-danger">*</span></label>
                    <select name="fee_type_id" id="feeTypeSelect" class="form-control form-control-sm" required>
                        <option value="">{{ sm_t('Select') }}</option>
                        @foreach($feeTypes as $ft)
                        <option value="{{ $ft->id }}" data-amount="{{ $ft->amount }}" {{ old('fee_type_id') == $ft->id ? 'selected':'' }}>{{ $ft->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Academic Year --}}
                <div class="col-md-3 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Academic Year') }}</label>
                    <select name="academic_year_id" class="form-control form-control-sm">
                        <option value="">{{ sm_t('Select') }}</option>
                        @foreach($academicYears as $yr)
                        <option value="{{ $yr->id }}" {{ ($yr->is_current ?? false) ? 'selected':'' }}>{{ $yr->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Amount --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Amount') }} <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ sm_currency() }}</span>
                        <input type="number" name="amount" id="amountField" class="form-control form-control-sm" step="0.01" min="0" value="{{ old('amount',0) }}" required oninput="recalc()">
                    </div>
                </div>

                {{-- Fine --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Fine') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ sm_currency() }}</span>
                        <input type="number" name="fine" id="fineField" class="form-control form-control-sm" step="0.01" min="0" value="{{ old('fine',0) }}" oninput="recalc()">
                    </div>
                </div>

                {{-- Discount --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Discount') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ sm_currency() }}</span>
                        <input type="number" name="discount" id="discountField" class="form-control form-control-sm" step="0.01" min="0" value="{{ old('discount') }}" placeholder="Auto: student's discount" oninput="recalc()">
                    </div>
                </div>

                {{-- Paid Amount --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Paid Now') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ sm_currency() }}</span>
                        <input type="number" name="paid_amount" id="paidField" class="form-control form-control-sm" step="0.01" min="0" value="{{ old('paid_amount',0) }}" oninput="recalc()">
                    </div>
                </div>

                {{-- Due (readonly display) --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Due Amount') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ sm_currency() }}</span>
                        <input type="text" id="dueDisplay" class="form-control form-control-sm bg-light fw-bold" readonly value="0.00">
                    </div>
                </div>

                {{-- Month --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Month') }}</label>
                    <select name="month" class="form-control form-control-sm">
                        <option value="">{{ sm_t('N/A') }}</option>
                        @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ old('month') == $m ? 'selected':'' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-md-2 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Year') }}</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ old('year', date('Y')) }}" min="2000" max="2099">
                </div>

                {{-- Due Date --}}
                <div class="col-md-3 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Due Date') }}</label>
                    <input type="date" name="due_date" class="form-control form-control-sm" value="{{ old('due_date') }}">
                </div>

                {{-- Payment Method (if paying now) --}}
                <div class="col-md-3 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Payment Method') }}</label>
                    <select name="payment_method" class="form-control form-control-sm">
                        <option value="">{{ sm_t('None (Invoice Only)') }}</option>
                        <option value="cash">{{ sm_t('Cash') }}</option>
                        <option value="bank">{{ sm_t('Bank Transfer') }}</option>
                        <option value="mobile">{{ sm_t('Mobile Banking') }}</option>
                        <option value="cheque">{{ sm_t('Cheque') }}</option>
                    </select>
                </div>

                {{-- Payment Date --}}
                <div class="col-md-3 mb-1">
                    <label class="form-label form-label-sm">{{ sm_t('Payment Date') }}</label>
                    <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>

                {{-- Note --}}
                <div class="col-12 mb-2">
                    <label class="form-label form-label-sm">{{ sm_t('Note') }}</label>
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional note" value="{{ old('note') }}">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-encodex-save"><i class="fas fa-file-invoice me-2"></i>Create Invoice</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto-fill amount when fee type is selected
document.getElementById('feeTypeSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const amt = parseFloat(opt.dataset.amount || 0);
    if (amt > 0) {
        document.getElementById('amountField').value = amt.toFixed(2);
        recalc();
    }
});

function recalc() {
    const amount   = parseFloat(document.getElementById('amountField').value)   || 0;
    const fine     = parseFloat(document.getElementById('fineField').value)     || 0;
    const discount = parseFloat(document.getElementById('discountField').value) || 0;
    const paid     = parseFloat(document.getElementById('paidField').value)     || 0;
    const due      = Math.max(0, amount + fine - discount - paid);
    document.getElementById('dueDisplay').value = due.toFixed(2);
}
recalc();
</script>
@endpush
@endsection
