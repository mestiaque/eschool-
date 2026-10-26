@extends('me::master')
@section('title', sm_t('Teacher Salary'))

@push('buttons')
<button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#salaryModal"><i class="fas fa-plus"></i> {{ sm_t('Process Salary') }}</button>
@endpush

@section('content')

<div class="card">
    <div class="card-body py-2 border-bottom">
        <form method="GET" class="glass-search-form row g-2 align-items-end">
            <div class="col-md-2">
                <select name="month" class="form-control form-control-sm">
                    <option value="">{{ sm_t('All Months') }}</option>
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="year" class="form-control form-control-sm" placeholder="{{ sm_t('Year') }}" value="{{ request('year', date('Y')) }}">
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-sm btn-encodex-search w-100">{{ sm_t('Filter') }}</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-encodex table-sm table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Teacher') }}</th>
                    <th>{{ sm_t('Month/Year') }}</th>
                    <th>{{ sm_t('Basic') }}</th>
                    <th>{{ sm_t('Allowance') }}</th>
                    <th>{{ sm_t('Deduction') }}</th>
                    <th>{{ sm_t('Net Salary') }}</th>
                    <th>{{ sm_t('Payment Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $i => $sal)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $sal->teacher?->full_name }}</td>
                    <td>{{ date('F', mktime(0,0,0,(int)$sal->month,1)) }} {{ $sal->year }}</td>
                    <td>{{ sm_money($sal->basic_salary, 2) }}</td>
                    <td>{{ sm_money($sal->allowance, 2) }}</td>
                    <td>{{ sm_money($sal->deduction, 2) }}</td>
                    <td><strong>{{ sm_money($sal->net_salary, 2) }}</strong></td>
                    <td>{{ sm_date($sal->payment_date) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">{{ sm_t('No salary records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">{{ $salaries->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="salaryModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('teachers.salary.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">{{ sm_t('Process Salary') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">{{ sm_t('Teacher') }} *</label>
                        <select name="teacher_id" class="form-control form-control-sm" required>
                            <option value="">{{ sm_t('Select Teacher') }}</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" data-salary="{{ $t->salary }}">{{ $t->full_name }} ({{ sm_money($t->salary ?? 0, 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col">
                            <label class="form-label">{{ sm_t('Month') }} *</label>
                            <select name="month" class="form-control form-control-sm" required>
                                @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">{{ sm_t('Year') }} *</label>
                            <input type="text" name="year" class="form-control form-control-sm" value="{{ date('Y') }}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label">{{ sm_t('Basic Salary') }}</label><input type="number" name="basic_salary" id="basicSal" class="form-control form-control-sm" step="0.01" required></div>
                        <div class="col"><label class="form-label">{{ sm_t('Allowance') }}</label><input type="number" name="allowance" class="form-control form-control-sm" value="0" step="0.01"></div>
                        <div class="col"><label class="form-label">{{ sm_t('Deduction') }}</label><input type="number" name="deduction" class="form-control form-control-sm" value="0" step="0.01"></div>
                    </div>
                    <div class="mb-2"><label class="form-label">{{ sm_t('Payment Date') }}</label><input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                    <div class="mb-2"><label class="form-label">{{ sm_t('Note') }}</label><textarea name="note" class="form-control form-control-sm" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-sm btn-encodex-save">{{ sm_t('Process') }}</button></div>
            </div>
        </form>
    </div>
</div>
<script>
document.querySelector('[name="teacher_id"]')?.addEventListener('change', function() {
    const salary = this.options[this.selectedIndex].dataset.salary || 0;
    document.getElementById('basicSal').value = salary;
});
</script>
@endsection
