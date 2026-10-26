@extends('me::master')
@section('title', sm_t('Fee Discounts / Waivers'))
@push('buttons')
    <button class="btn btn-sm btn-encodex-create" data-bs-toggle="modal" data-bs-target="#discountModal">
        <i class="fas fa-plus"></i> {{ sm_t('Add Discount') }}
    </button>
@endpush

@section('content')

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover table-sm table-encodex mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ sm_t('Student') }}</th>
                    <th>{{ sm_t('Class') }}</th>
                    <th>{{ sm_t('Fee Type') }}</th>
                    <th>{{ sm_t('Discount Type') }}</th>
                    <th class="text-end">{{ sm_t('Amount') }}</th>
                    <th>{{ sm_t('Status') }}</th>
                    <th>{{ sm_t('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($discounts as $i => $d)
                <tr>
                    <td class="text-muted small">{{ $i+1 }}</td>
                    <td class="fw-semibold">{{ $d->student?->first_name }} {{ $d->student?->last_name }}</td>
                    <td class="small">{{ $d->student?->classe?->name ?? '—' }}</td>
                    <td>{{ $d->feeType?->name ?? '<span class="text-muted">All Fees</span>' }}</td>
                    <td><span class="badge bg-info-subtle text-info">{{ sm_t(ucfirst($d->discount_type ?? 'fixed')) }}</span></td>
                    <td class="text-end fw-semibold text-success">
                        @if($d->discount_type === 'percent')
                            {{ $d->discount_amount }}%
                        @else
                            {{ sm_money($d->discount_amount, 2) }}
                        @endif
                    </td>
                    <td><span class="badge bg-{{ $bc = $d->is_active ? 'success' : 'secondary' }}-subtle text-{{ $bc }}">{{ sm_t($d->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td class="text-center">
                        <form method="POST" action="{{ route('fees.discounts.destroy', $d->id) }}" class="d-inline" onsubmit="return confirm('Remove this discount?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-encodex-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">{{ sm_t('No discounts applied.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Discount Modal --}}
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog glass-card">
        <form method="POST" action="{{ route('fees.discounts.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title"><i class="fas fa-tag me-2"></i>{{ sm_t('Apply Discount / Waiver') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Student') }} <span class="text-danger">*</span></label>
                            @include('school-management::partials.student-select', ['name' => 'student_id', 'required' => true, 'placeholder' => 'Select Student'])
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Fee Type') }}</label>
                            <select name="fee_type_id" class="form-control form-control-sm">
                                <option value="">{{ sm_t('All Fees') }}</option>
                                @foreach($feeTypes as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Discount Type') }} <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-control form-control-sm" required>
                                <option value="fixed">Fixed ({{ sm_currency() }})</option>
                                <option value="percent">Percent (%)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="discount_amount" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label form-label-sm">{{ sm_t('Status') }}</label>
                            <select name="is_active" class="form-control form-control-sm">
                                <option value="1">{{ sm_t('Active') }}</option>
                                <option value="0">{{ sm_t('Inactive') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-encodex-cancel" data-bs-dismiss="modal">{{ sm_t('Cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-encodex-save"><i class="fas fa-save me-1"></i>{{ sm_t('Apply') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
