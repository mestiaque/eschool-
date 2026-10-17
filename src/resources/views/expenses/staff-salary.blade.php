@extends('me::master')

@section('title', sm_t('Staff Salary Payment'))

@push('buttons')
<button class="btn btn-encodex-create btn-sm" data-bs-toggle="modal" data-bs-target="#salaryModal">
    <i class="fas fa-plus"></i> Pay Salary
</button>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ sm_t('Type') }}</label>
                    <select name="staff_type" class="form-select form-select-sm">
                        <option value="">{{ sm_t('All') }}</option>
                        <option value="teacher" {{ request('staff_type') === 'teacher' ? 'selected' : '' }}>{{ sm_t('Teachers') }}</option>
                        <option value="staff" {{ request('staff_type') === 'staff' ? 'selected' : '' }}>{{ sm_t('Staff') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ sm_t('Month') }}</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">{{ sm_t('All months') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ sm_t('Year') }}</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ request('year') }}" placeholder="{{ date('Y') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-encodex-search btn-sm"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('expenses.staff-salary') }}" class="btn btn-encodex-clear btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted small mb-2">Each payment is also posted to Expenses (salary category set in Settings → General), so reports count it once.</p>
            <div class="table-responsive">
                <table class="table table-encodex table-sm table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>{{ sm_t('Paid to') }}</th>
                            <th>{{ sm_t('Type') }}</th>
                            <th>{{ sm_t('Month') }}</th>
                            <th class="text-end">{{ sm_t('Basic') }}</th>
                            <th class="text-end">{{ sm_t('Allowance') }}</th>
                            <th class="text-end">{{ sm_t('Deduction') }}</th>
                            <th class="text-end">{{ sm_t('Net') }}</th>
                            <th>{{ sm_t('Payment date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->payee_name }}</td>
                            <td><span class="badge bg-{{ $bc = $payment->staff_type === 'teacher' ? 'info' : 'secondary' }}-subtle text-{{ $bc }}">{{ ucfirst($payment->staff_type) }}</span></td>
                            <td>{{ $payment->month_name }} {{ $payment->year }}</td>
                            <td class="text-end">{{ sm_money($payment->basic_salary, 2) }}</td>
                            <td class="text-end">{{ sm_money($payment->allowance, 2) }}</td>
                            <td class="text-end">{{ sm_money($payment->deduction, 2) }}</td>
                            <td class="text-end fw-semibold">{{ sm_money($payment->net_salary, 2) }}</td>
                            <td>{{ sm_date($payment->payment_date) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">No payments found</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2">{{ $payments->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Salary Modal -->
<div class="modal fade" id="salaryModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('expenses.staff-salary.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ sm_t('Pay Salary') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Paid to') }} <span class="text-danger">*</span></label>
                        <select name="payee" id="payeeSelect" class="form-select form-select-sm" required>
                            <option value="">{{ sm_t('Select person') }}</option>
                            <optgroup label="Teachers">
                                @foreach($teachers as $t)
                                <option value="teacher:{{ $t->id }}" data-salary="{{ $t->salary }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Staff">
                                @foreach($staff as $s)
                                <option value="staff:{{ $s->id }}" data-salary="{{ $s->salary }}">{{ $s->first_name }} {{ $s->last_name }} — {{ $s->designation }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label class="form-label">{{ sm_t('Month') }} <span class="text-danger">*</span></label>
                            <select name="month" class="form-select form-select-sm" required>
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">{{ sm_t('Year') }} <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control form-control-sm" value="{{ date('Y') }}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Basic Salary') }} <span class="text-danger">*</span></label>
                        <input type="number" name="basic_salary" id="basicSalary" class="form-control form-control-sm" min="0" step="0.01" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label class="form-label">{{ sm_t('Allowance') }}</label>
                            <input type="number" name="allowance" class="form-control form-control-sm" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label">{{ sm_t('Deduction') }}</label>
                            <input type="number" name="deduction" class="form-control form-control-sm" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Payment Date') }}</label>
                        <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-encodex-cancel btn-sm" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-encodex-payment btn-sm">{{ sm_t('Pay') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Pre-fill the basic salary from the person's profile.
document.getElementById('payeeSelect')?.addEventListener('change', function () {
    const salary = this.selectedOptions[0]?.dataset.salary;
    if (salary && Number(salary) > 0) document.getElementById('basicSalary').value = salary;
});
</script>
@endpush
@endsection
